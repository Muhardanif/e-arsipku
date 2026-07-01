<?php

namespace App\Console\Commands;

use App\Models\DokumenVersi;
use App\Support\EkstraksiTeks;
use Illuminate\Console\Command;

/**
 * Mengindeks isi teks seluruh berkas dokumen untuk pencarian penuh.
 * Berguna untuk mengindeks berkas lama, atau memproses ulang berkas scan
 * (status 'perlu_ocr') setelah Tesseract/Ghostscript dipasang di server.
 */
class IndeksTeksDokumen extends Command
{
    protected $signature = 'dokumen:indeks-teks
                            {--ulang : Indeks ulang semua berkas, termasuk yang sudah terindeks}
                            {--perlu-ocr : Hanya proses ulang berkas yang sebelumnya ditandai perlu OCR}';

    protected $description = 'Ekstrak isi teks (lapisan teks PDF + OCR) berkas dokumen untuk pencarian penuh.';

    public function handle(): int
    {
        $query = DokumenVersi::query();

        if ($this->option('perlu-ocr')) {
            $query->where('metode_ekstraksi', 'perlu_ocr');
        } elseif (! $this->option('ulang')) {
            $query->whereNull('teks_diindeks_pada');
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada berkas yang perlu diindeks.');

            return self::SUCCESS;
        }

        $this->info("Mengindeks {$total} berkas dokumen…");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ringkasan = [];

        $query->with('dokumen')->orderBy('id')->chunkById(20, function ($items) use ($bar, &$ringkasan) {
            foreach ($items as $versi) {
                EkstraksiTeks::indeks($versi);
                $metode = $versi->refresh()->metode_ekstraksi ?? 'gagal';
                $ringkasan[$metode] = ($ringkasan[$metode] ?? 0) + 1;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $label = [
            'teks' => 'Lapisan teks PDF',
            'ocr' => 'OCR (scan)',
            'perlu_ocr' => 'Perlu OCR (alat belum siap)',
            'tidak_didukung' => 'Format tidak didukung',
            'gagal' => 'Gagal',
        ];

        $this->table(['Metode', 'Jumlah'], collect($ringkasan)
            ->map(fn ($jml, $metode) => [$label[$metode] ?? $metode, $jml])
            ->values()
            ->all());

        if (($ringkasan['perlu_ocr'] ?? 0) > 0) {
            $this->warn('Sebagian berkas scan belum ter-OCR. Pastikan Tesseract (bahasa "ind") '
                .'dan Ghostscript terpasang, lalu jalankan: php artisan dokumen:indeks-teks --perlu-ocr');
        }

        return self::SUCCESS;
    }
}
