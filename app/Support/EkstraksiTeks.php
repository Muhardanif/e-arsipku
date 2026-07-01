<?php

namespace App\Support;

use App\Models\DokumenVersi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;
use Symfony\Component\Process\Process;
use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * Mengekstrak isi teks dari berkas dokumen agar dapat dicari berdasarkan
 * kontennya. Strategi hibrida:
 *   1. PDF berbasis teks  → ambil lapisan teks (smalot/pdfparser, murni PHP).
 *   2. PDF hasil scan      → rasterisasi (Ghostscript) lalu OCR (Tesseract).
 *   3. Gambar (jpg/png/…)  → OCR langsung.
 *
 * Semua jalur menurunkan derajat dengan aman: bila alat OCR belum terpasang,
 * berkas ditandai 'perlu_ocr' (bukan gagal) sehingga dapat diindeks ulang
 * setelah Tesseract/Ghostscript dipasang — lihat `php artisan dokumen:indeks-teks`.
 */
class EkstraksiTeks
{
    /** Ekstensi yang diperlakukan sebagai gambar (OCR langsung). */
    public const GAMBAR = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff'];

    /**
     * Indeks satu versi dokumen: ekstrak teks lalu simpan ke basis data.
     * Tidak pernah melempar — kegagalan dicatat dan ditandai 'gagal'.
     */
    public static function indeks(DokumenVersi $versi): void
    {
        try {
            $hasil = static::dariBerkas($versi->file_path);
        } catch (\Throwable $e) {
            Log::warning('Ekstraksi teks gagal untuk versi #'.$versi->id.': '.$e->getMessage());
            $hasil = ['teks' => null, 'metode' => 'gagal'];
        }

        $versi->forceFill([
            'isi_teks' => $hasil['teks'],
            'metode_ekstraksi' => $hasil['metode'],
            'teks_diindeks_pada' => now(),
        ])->save();
    }

    /**
     * Ekstrak teks dari sebuah berkas pada disk 'local'.
     *
     * @return array{teks: ?string, metode: string}
     */
    public static function dariBerkas(string $path): array
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return ['teks' => null, 'metode' => 'gagal'];
        }

        $abs = $disk->path($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $hasil = match (true) {
            $ext === 'pdf' => static::dariPdf($abs),
            in_array($ext, self::GAMBAR, true) => static::dariGambar($abs),
            default => ['teks' => null, 'metode' => 'tidak_didukung'],
        };

        if (! empty($hasil['teks'])) {
            $hasil['teks'] = static::rapikan($hasil['teks']);
        }

        return $hasil;
    }

    /**
     * PDF: coba lapisan teks dulu; bila kosong/minim → OCR.
     *
     * @return array{teks: ?string, metode: string}
     */
    protected static function dariPdf(string $abs): array
    {
        $min = (int) config('arsip.ekstraksi.min_teks', 40);
        $teks = '';

        try {
            $teks = trim((new PdfParser)->parseFile($abs)->getText());
        } catch (\Throwable $e) {
            // PDF rusak/terenkripsi/berbasis gambar — lanjut ke OCR.
            Log::info('Lapisan teks PDF tidak terbaca, mencoba OCR: '.$e->getMessage());
        }

        if (Str::length($teks) >= $min) {
            return ['teks' => $teks, 'metode' => 'teks'];
        }

        // Lapisan teks kosong/minim → kemungkinan hasil scan.
        if (static::ocrAktif()) {
            $ocr = static::ocrPdf($abs);

            if ($ocr !== null && trim($ocr) !== '') {
                return ['teks' => $ocr, 'metode' => 'ocr'];
            }
        }

        // OCR nonaktif atau gagal: simpan teks seadanya, tandai perlu OCR
        // bila benar-benar kosong agar bisa diproses ulang nanti.
        return $teks === ''
            ? ['teks' => null, 'metode' => 'perlu_ocr']
            : ['teks' => $teks, 'metode' => 'teks'];
    }

    /**
     * Gambar: OCR langsung.
     *
     * @return array{teks: ?string, metode: string}
     */
    protected static function dariGambar(string $abs): array
    {
        if (! static::ocrAktif()) {
            return ['teks' => null, 'metode' => 'perlu_ocr'];
        }

        $teks = static::ocrGambar($abs);

        return $teks !== null && trim($teks) !== ''
            ? ['teks' => $teks, 'metode' => 'ocr']
            : ['teks' => null, 'metode' => 'perlu_ocr'];
    }

    /**
     * OCR satu berkas gambar via Tesseract. NULL bila gagal (mis. tidak terpasang).
     */
    protected static function ocrGambar(string $abs): ?string
    {
        try {
            $lang = array_filter(explode('+', (string) config('arsip.ocr.lang', 'ind')));

            return (new TesseractOCR($abs))
                ->executable(config('arsip.ocr.tesseract_path', 'tesseract'))
                ->lang(...$lang)
                ->run();
        } catch (\Throwable $e) {
            Log::warning('OCR gambar gagal (Tesseract terpasang?): '.$e->getMessage());

            return null;
        }
    }

    /**
     * OCR PDF hasil scan: rasterisasi tiap halaman → OCR → gabung.
     */
    protected static function ocrPdf(string $abs): ?string
    {
        [$dir, $halaman] = static::rasterisasi($abs);

        if ($halaman === []) {
            return null;
        }

        $potongan = [];

        try {
            foreach ($halaman as $img) {
                $t = static::ocrGambar($img);
                if ($t !== null && trim($t) !== '') {
                    $potongan[] = trim($t);
                }
            }
        } finally {
            static::bersihkanDir($dir);
        }

        return $potongan === [] ? null : implode("\n\n", $potongan);
    }

    /**
     * Rasterisasi halaman PDF menjadi PNG via Ghostscript.
     * Mengembalikan [direktori_sementara, daftar_path_gambar].
     *
     * @return array{0: ?string, 1: array<int, string>}
     */
    protected static function rasterisasi(string $abs): array
    {
        $dpi = (int) config('arsip.ocr.dpi', 200);
        $maks = (int) config('arsip.ocr.maks_halaman', 15);
        $timeout = (float) config('arsip.ocr.timeout', 300);
        $gs = (string) config('arsip.ocr.ghostscript_path', 'gswin64c');

        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'tmp-ocr'.DIRECTORY_SEPARATOR.Str::uuid()->toString());

        if (! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return [null, []];
        }

        $output = $dir.DIRECTORY_SEPARATOR.'page-%03d.png';

        $proses = new Process([
            $gs, '-q', '-dNOPAUSE', '-dBATCH', '-dSAFER',
            '-sDEVICE=png16m', '-r'.$dpi,
            '-dFirstPage=1', '-dLastPage='.$maks,
            '-sOutputFile='.$output, $abs,
        ]);
        $proses->setTimeout($timeout);

        try {
            $proses->run();
        } catch (\Throwable $e) {
            Log::warning('Rasterisasi PDF gagal (Ghostscript terpasang?): '.$e->getMessage());
        }

        $files = glob($dir.DIRECTORY_SEPARATOR.'page-*.png') ?: [];
        sort($files);

        if ($files === []) {
            static::bersihkanDir($dir);

            return [null, []];
        }

        return [$dir, $files];
    }

    protected static function bersihkanDir(?string $dir): void
    {
        if (! $dir || ! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
            @unlink($f);
        }

        @rmdir($dir);
    }

    protected static function ocrAktif(): bool
    {
        return (bool) config('arsip.ocr.aktif', true);
    }

    /**
     * Normalkan spasi/baris berlebih dan batasi panjang teks tersimpan.
     */
    protected static function rapikan(string $teks): string
    {
        $teks = preg_replace('/[ \t\x{00A0}]+/u', ' ', $teks) ?? $teks;
        $teks = preg_replace('/\n{3,}/', "\n\n", $teks) ?? $teks;
        $teks = trim($teks);

        $maks = (int) config('arsip.ekstraksi.maks_simpan', 200000);

        return Str::limit($teks, $maks, '');
    }
}
