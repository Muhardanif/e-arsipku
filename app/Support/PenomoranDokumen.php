<?php

namespace App\Support;

use App\Models\Dokumen;
use App\Models\Klaster;
use App\Models\KategoriDokumen;

/**
 * Pembuat nomor dokumen dinamis berbasis template per kategori.
 *
 * Token yang didukung pada template `format_nomor`:
 *   {NO}      → nomor urut, di-zero-pad sesuai `digit_nomor` (mis. 001)
 *   {TAHUN}   → tahun 4 digit (mis. 2025)
 *   {TAHUN2}  → tahun 2 digit (mis. 25)
 *   {KLASTER} → kode klaster (mis. K5) — kategori yang memuat token ini
 *               akan meminta pemilihan klaster saat input dokumen
 *
 * Nomor urut di-reset per tahun, dan untuk kategori berklaster dihitung
 * terpisah per klaster (kategori_id + klaster_id + tahun).
 */
class PenomoranDokumen
{
    /**
     * Apakah template memakai token klaster.
     */
    public static function pakaiKlaster(?string $template): bool
    {
        return $template !== null && str_contains($template, '{KLASTER}');
    }

    /**
     * Susun string nomor dari template + nilai.
     */
    public static function render(string $template, int $urut, int $tahun, int $digit, ?string $kodeKlaster = null): string
    {
        return strtr($template, [
            '{NO}' => str_pad((string) $urut, max($digit, 1), '0', STR_PAD_LEFT),
            '{TAHUN2}' => substr((string) $tahun, -2),
            '{TAHUN}' => (string) $tahun,
            '{KLASTER}' => (string) $kodeKlaster,
        ]);
    }

    /**
     * Nomor urut berikutnya untuk kategori + tahun (+ klaster bila ada), untuk pratinjau.
     * Bila ada celah bekas draf yang dibatalkan, celah terkecil itulah yang dipakai
     * lebih dulu sehingga nomor tidak terbuang.
     */
    public static function nextUrut(KategoriDokumen $kategori, int $tahun, ?Klaster $klaster = null): int
    {
        return self::cariUrut($kategori->id, $tahun, $klaster?->id);
    }

    /**
     * Hasilkan nomor final + urutnya secara aman dari duplikat.
     * Panggil di dalam transaksi yang sudah mengunci baris kategori.
     *
     * Nomor urut diambil dari celah terkecil yang kosong (bekas draf yang
     * dibatalkan) lebih dulu, baru max+1 bila tidak ada celah.
     *
     * @return array{0:string,1:int} [nomor_dokumen, nomor_urut]
     */
    public static function generate(KategoriDokumen $kategori, int $tahun, ?Klaster $klaster = null): array
    {
        $urut = self::cariUrut($kategori->id, $tahun, $klaster?->id);

        // Jaga-jaga: bila string nomor hasil render ternyata sudah dipakai
        // (mis. dokumen ber-soft-delete dengan nomor sama), maju ke urut bebas.
        do {
            $nomor = self::render($kategori->format_nomor, $urut, $tahun, (int) $kategori->digit_nomor, $klaster?->kode);
            $bentrok = Dokumen::withTrashed()->where('nomor_dokumen', $nomor)->exists();
            if ($bentrok) {
                $urut++;
            }
        } while ($bentrok);

        return [$nomor, $urut];
    }

    /**
     * Tentukan nomor urut yang akan dipakai pada lingkup kategori + tahun (+ klaster):
     * celah terkecil yang kosong (bekas draf yang dibatalkan), atau max+1 bila tidak ada celah.
     *
     * Hanya baris yang masih ada (termasuk soft delete) yang dianggap "memakai" nomor.
     * Draf yang dibatalkan dihapus permanen sehingga nomornya kembali menjadi celah.
     */
    private static function cariUrut(int $kategoriId, int $tahun, ?int $klasterId): int
    {
        $terpakai = Dokumen::withTrashed()
            ->where('kategori_id', $kategoriId)
            ->where('tahun_nomor', $tahun)
            ->where('klaster_id', $klasterId)
            ->whereNotNull('nomor_urut')
            ->pluck('nomor_urut')
            ->all();

        if (empty($terpakai)) {
            return 1;
        }

        $set = array_flip($terpakai);
        $max = max($terpakai);

        for ($i = 1; $i < $max; $i++) {
            if (! isset($set[$i])) {
                return $i; // celah terkecil bekas draf yang dibatalkan
            }
        }

        return $max + 1;
    }
}
