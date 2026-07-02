<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Menyarankan metadata dokumen dari isi teksnya menggunakan model Claude.
 *
 * Bekerja pada teks yang SUDAH diekstrak aplikasi (lapisan teks PDF / OCR via
 * {@see \App\Support\EkstraksiTeks}), jadi tidak mengirim berkas mentah ke API —
 * lebih hemat token dan tidak bergantung pada dukungan vision.
 *
 * Hasilnya hanya SARAN untuk mengisi form; petugas tetap meninjau & menyimpan.
 */
class EkstraksiMetadataAI
{
    /** Field yang mungkin dikembalikan model (selain kategori). */
    private const FIELD = [
        'nomor_dokumen', 'judul', 'deskripsi',
        'tanggal_dokumen', 'tanggal_berlaku', 'tanggal_berakhir', 'pengesah',
    ];

    /**
     * Apakah fitur aktif dan siap dipakai (sakelar on + kunci API terisi).
     */
    public static function tersedia(): bool
    {
        return (bool) config('arsip.ai.aktif') && filled(config('arsip.ai.api_key'));
    }

    /**
     * Minta model menyarankan metadata dari $teks.
     *
     * @param  Collection<int, \App\Models\KategoriDokumen>  $kategori  Daftar kategori untuk pencocokan.
     * @return array<string, string|null>  Saran metadata (nilai ternormalisasi; kunci `kategori_id` bila cocok).
     *
     * @throws \RuntimeException  Bila fitur tidak tersedia atau API gagal.
     */
    public function sarankan(string $teks, Collection $kategori): array
    {
        if (! self::tersedia()) {
            throw new \RuntimeException('Fitur saran metadata AI belum diaktifkan.');
        }

        $teks = trim(Str::limit($teks, (int) config('arsip.ai.maks_teks', 12000), ''));

        if ($teks === '') {
            throw new \RuntimeException('Isi teks berkas kosong — tidak ada yang bisa dibaca AI.');
        }

        $mentah = $this->panggilApi($teks, $kategori);

        return $this->normalkan($mentah, $kategori);
    }

    /**
     * Panggil Messages API Anthropic dan kembalikan JSON hasil parse.
     *
     * @return array<string, mixed>
     */
    private function panggilApi(string $teks, Collection $kategori): array
    {
        $daftarKategori = $kategori
            ->map(fn ($k) => "- {$k->kode}: {$k->nama}")
            ->implode("\n");

        $instruksi = <<<TXT
        Anda asisten pengelola arsip dokumen puskesmas di Indonesia. Dari ISI DOKUMEN
        di bawah, ekstrak metadata berikut dan balas HANYA sebagai satu objek JSON
        (tanpa penjelasan, tanpa blok kode markdown):

        {
          "nomor_dokumen": "nomor resmi yang tertera pada dokumen, atau null",
          "judul": "judul/perihal dokumen yang ringkas, atau null",
          "deskripsi": "ringkasan isi 1-2 kalimat, atau null",
          "tanggal_dokumen": "tanggal dokumen format YYYY-MM-DD, atau null",
          "tanggal_berlaku": "tanggal mulai berlaku YYYY-MM-DD, atau null",
          "tanggal_berakhir": "tanggal berakhir/kadaluarsa YYYY-MM-DD, atau null",
          "pengesah": "nama/jabatan penanda tangan atau pengesah, atau null",
          "kategori_kode": "kode kategori paling sesuai dari daftar, atau null"
        }

        Aturan:
        - Gunakan null bila informasi tidak ditemukan; JANGAN mengarang.
        - Semua tanggal harus format YYYY-MM-DD.
        - "kategori_kode" harus SALAH SATU kode dari daftar berikut (atau null):
        {$daftarKategori}

        ISI DOKUMEN:
        \"\"\"
        {$teks}
        \"\"\"
        TXT;

        $response = Http::withHeaders([
            'x-api-key' => (string) config('arsip.ai.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])
            ->timeout((int) config('arsip.ai.timeout', 60))
            ->baseUrl((string) config('arsip.ai.base_url'))
            ->post('/v1/messages', [
                'model' => (string) config('arsip.ai.model', 'claude-opus-4-8'),
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $instruksi],
                ],
            ]);

        if ($response->failed()) {
            Log::warning('Saran metadata AI gagal: HTTP '.$response->status().' '.$response->body());

            throw new \RuntimeException(match ($response->status()) {
                401, 403 => 'Kunci API Anthropic tidak valid atau tidak berizin.',
                429 => 'Kuota API sedang penuh. Coba lagi beberapa saat.',
                default => 'Layanan AI sedang tidak dapat dihubungi.',
            });
        }

        $teksJawaban = data_get($response->json(), 'content.0.text');

        if (! is_string($teksJawaban) || $teksJawaban === '') {
            throw new \RuntimeException('AI tidak mengembalikan jawaban yang dapat dibaca.');
        }

        return $this->parseJson($teksJawaban);
    }

    /**
     * Ambil objek JSON dari teks jawaban model (toleran terhadap teks pembungkus).
     *
     * @return array<string, mixed>
     */
    private function parseJson(string $teks): array
    {
        $awal = strpos($teks, '{');
        $akhir = strrpos($teks, '}');

        if ($awal === false || $akhir === false || $akhir <= $awal) {
            throw new \RuntimeException('Format jawaban AI tidak dikenali.');
        }

        $json = substr($teks, $awal, $akhir - $awal + 1);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new \RuntimeException('Jawaban AI bukan JSON yang valid.');
        }

        return $data;
    }

    /**
     * Bersihkan & validasi nilai mentah: tanggal harus valid, string dirapikan,
     * kategori_kode dipetakan ke kategori_id yang ada.
     *
     * @param  array<string, mixed>  $mentah
     * @param  Collection<int, \App\Models\KategoriDokumen>  $kategori
     * @return array<string, string|null>
     */
    private function normalkan(array $mentah, Collection $kategori): array
    {
        $saran = [];

        foreach (self::FIELD as $field) {
            $nilai = $mentah[$field] ?? null;
            $nilai = is_string($nilai) ? trim($nilai) : null;

            if ($nilai === '' || $nilai === null) {
                $saran[$field] = null;

                continue;
            }

            $saran[$field] = str_starts_with($field, 'tanggal_')
                ? $this->tanggalValid($nilai)
                : $nilai;
        }

        // Petakan kode kategori (case-insensitive) ke id yang benar-benar ada.
        $kode = isset($mentah['kategori_kode']) && is_string($mentah['kategori_kode'])
            ? trim($mentah['kategori_kode'])
            : '';

        $cocok = $kode !== ''
            ? $kategori->first(fn ($k) => strcasecmp((string) $k->kode, $kode) === 0)
            : null;

        $saran['kategori_id'] = $cocok?->id ? (string) $cocok->id : null;

        return $saran;
    }

    /**
     * Kembalikan tanggal YYYY-MM-DD bila dapat diparse, selain itu null.
     */
    private function tanggalValid(string $nilai): ?string
    {
        try {
            return Carbon::parse($nilai)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
