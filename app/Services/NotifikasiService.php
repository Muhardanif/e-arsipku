<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\NotifikasiDibaca;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Notifikasi in-app dihitung REAL-TIME dari tabel dokumen (tanpa scheduler).
 *
 * Dua kategori:
 *   (a) kedaluwarsa — dokumen berlaku yang masa berlakunya ≤ ambang / sudah lewat.
 *   (b) review      — dokumen yang jatuh tempo tinjauan berkala ≤ ambang / sudah lewat.
 *
 * Ambang tingkat (berdasarkan sisa hari ke jatuh tempo):
 *   H-30 → info · H-14 → warning · H-7 → kritis · H-0/lewat → danger.
 *
 * Hanya untuk role Admin & Petugas (Staf mendapat koleksi kosong).
 */
class NotifikasiService
{
    /** Notifikasi mulai muncul ketika jatuh tempo ≤ ambang hari ini (atau sudah lewat). */
    public const AMBANG_HARI = 30;

    /** Batas aman jumlah baris yang diperiksa per kategori (LAN, data moderat). */
    private const BATAS_QUERY = 100;

    /** Bobot tingkat untuk perbandingan eskalasi (besar = lebih mendesak). */
    private const BOBOT = ['info' => 1, 'warning' => 2, 'kritis' => 3, 'danger' => 4];

    /** Cache hasil per-user dalam satu request (composer memanggil 2x). */
    private array $cache = [];

    /** Masa simpan cache kandidat notifikasi lintas-request (menit). */
    private const CACHE_TTL_MENIT = 60;

    /**
     * Koleksi notifikasi aktif (belum dibaca) untuk user, paling mendesak di atas.
     *
     * @return Collection<int, array>
     */
    public function ambil(User $user, ?int $limit = null): Collection
    {
        $items = $this->kandidat($user);

        return $limit ? $items->take($limit)->values() : $items;
    }

    /** Jumlah notifikasi belum dibaca (untuk badge counter). */
    public function hitungBelumDibaca(User $user): int
    {
        return $this->kandidat($user)->count();
    }

    /** Tingkat ambang dari sisa hari (negatif = sudah lewat). */
    public function tingkat(int $sisa): string
    {
        return match (true) {
            $sisa < 0 => 'danger',
            $sisa <= 7 => 'kritis',
            $sisa <= 14 => 'warning',
            default => 'info',
        };
    }

    /**
     * Kunci cache kandidat notifikasi per pengguna.
     */
    public static function cacheKey(int $userId): string
    {
        return "notif_expiry_user_{$userId}";
    }

    /**
     * Invalidasi cache kandidat notifikasi untuk seluruh penerima (admin & petugas).
     * Dipanggil setiap kali data dokumen berubah (tambah/ubah/hapus/revisi/review)
     * agar perhitungan expiry/review tidak basi.
     */
    public function lupakanCache(): void
    {
        User::query()
            ->whereIn('role', ['admin', 'petugas'])
            ->pluck('id')
            ->each(fn ($id) => Cache::forget(self::cacheKey((int) $id)));

        // Bersihkan juga cache per-request agar perhitungan berikutnya segar.
        $this->cache = [];
    }

    /**
     * @return Collection<int, array>
     */
    private function kandidat(User $user): Collection
    {
        if (isset($this->cache[$user->id])) {
            return $this->cache[$user->id];
        }

        // RBAC: hanya Admin & Petugas yang menerima notifikasi.
        if (! ($user->isAdmin() || $user->isPetugas())) {
            return $this->cache[$user->id] = collect();
        }

        // Kandidat dokumen (expiry/review) di-cache lintas-request. Status baca
        // diterapkan tiap request — bukan dari cache — agar badge langsung turun
        // begitu notifikasi ditandai dibaca, tanpa menunggu cache kedaluwarsa.
        $semua = $this->kandidatMentah($user);

        // Status baca user — satu query, di-index berdasarkan identitas notifikasi.
        $dibaca = NotifikasiDibaca::where('user_id', $user->id)
            ->get(['dokumen_id', 'jenis', 'tanggal_acuan', 'tingkat'])
            ->keyBy(fn (NotifikasiDibaca $r) => $this->kunci($r->dokumen_id, $r->jenis, $r->tanggal_acuan->toDateString()));

        $aktif = $semua->reject(function (array $n) use ($dibaca) {
            $baca = $dibaca->get($this->kunci($n['dokumen_id'], $n['jenis'], $n['acuan']->toDateString()));

            // Disembunyikan hanya jika sudah dibaca pada tingkat yang sama / lebih mendesak;
            // bila urgensi naik (mis. warning → kritis) notifikasi muncul kembali.
            return $baca && self::BOBOT[$baca->tingkat] >= self::BOBOT[$n['tingkat']];
        });

        return $this->cache[$user->id] = $aktif->sortBy('sisa')->values();
    }

    /**
     * Kandidat notifikasi mentah — dokumen akan/sudah kedaluwarsa (H-30/H-14/H-7)
     * dan dokumen yang jatuh tempo tinjauan berkala — SEBELUM penyaringan status
     * baca per pengguna. Inilah bagian mahal (query + DATE_ADD) yang di-cache
     * selama 60 menit dengan kunci "notif_expiry_user_{id}".
     *
     * @return Collection<int, array>
     */
    private function kandidatMentah(User $user): Collection
    {
        return Cache::remember(
            self::cacheKey($user->id),
            now()->addMinutes(self::CACHE_TTL_MENIT),
            function () {
                $batas = now()->startOfDay()->addDays(self::AMBANG_HARI)->toDateString();

                // (a) Dokumen akan / sudah kedaluwarsa
                $kedaluwarsa = Dokumen::query()
                    ->where('status', 'berlaku')
                    ->whereNotNull('tanggal_berakhir')
                    ->whereDate('tanggal_berakhir', '<=', $batas)
                    ->orderBy('tanggal_berakhir')
                    ->limit(self::BATAS_QUERY)
                    ->get(['id', 'nomor_dokumen', 'judul', 'tanggal_berakhir'])
                    ->map(fn (Dokumen $d) => $this->bentuk($d, 'kedaluwarsa', $d->tanggal_berakhir));

                // (b) Dokumen perlu review (jatuh tempo tinjauan ≤ ambang atau lewat)
                $review = Dokumen::butuhTinjauan(self::AMBANG_HARI)
                    ->with('kategori')
                    ->limit(self::BATAS_QUERY)
                    ->get()
                    ->map(fn (Dokumen $d) => $this->bentuk($d, 'review', $d->jatuhTempoReview()))
                    ->filter();

                return $kedaluwarsa->concat($review)->values();
            }
        );
    }

    private function bentuk(Dokumen $dokumen, string $jenis, ?Carbon $acuan): ?array
    {
        if (! $acuan) {
            return null;
        }

        $acuan = $acuan->copy()->startOfDay();
        $sisa = (int) now()->startOfDay()->diffInDays($acuan, false);

        return [
            'dokumen_id' => $dokumen->id,
            'nomor' => $dokumen->nomor_dokumen,
            'judul' => $dokumen->judul,
            'jenis' => $jenis,
            'acuan' => $acuan,
            'sisa' => $sisa,
            'tingkat' => $this->tingkat($sisa),
        ];
    }

    private function kunci(int $dokumenId, string $jenis, string $acuan): string
    {
        return $dokumenId.'|'.$jenis.'|'.$acuan;
    }
}
