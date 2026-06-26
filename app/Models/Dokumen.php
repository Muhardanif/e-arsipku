<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Dokumen extends Model
{
    use SoftDeletes;

    /** Ambang peringatan "akan jatuh tempo review" (hari sebelum jatuh tempo). */
    public const REVIEW_AMBANG_HARI = 90;

    protected $table = 'dokumen';

    protected $fillable = [
        'nomor_dokumen',
        'nomor_urut',
        'tahun_nomor',
        'judul',
        'kategori_id',
        'klaster_id',
        'deskripsi',
        'tanggal_dokumen',
        'tanggal_berlaku',
        'tanggal_berakhir',
        'pengesah',
        'status',
        'versi_terkini',
        'tanggal_review_terakhir',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_dokumen' => 'date',
            'tanggal_berlaku' => 'date',
            'tanggal_berakhir' => 'date',
            'tanggal_review_terakhir' => 'date',
            'versi_terkini' => 'integer',
            'nomor_urut' => 'integer',
            'tahun_nomor' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriDokumen::class, 'kategori_id');
    }

    public function klaster(): BelongsTo
    {
        return $this->belongsTo(Klaster::class, 'klaster_id');
    }

    public function versi(): HasMany
    {
        return $this->hasMany(DokumenVersi::class, 'dokumen_id');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'dokumen_id');
    }

    public function review(): HasMany
    {
        return $this->hasMany(DokumenReview::class, 'dokumen_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Versi terbaru dokumen (nomor_versi tertinggi).
     */
    public function versiTerkini(): HasMany
    {
        return $this->versi()->orderByDesc('nomor_versi');
    }

    /**
     * Dokumen masih berupa draf: nomor sudah dipesan tetapi berkas final
     * belum diunggah (menunggu tanda tangan atasan).
     */
    public function isDraf(): bool
    {
        return $this->status === 'draf';
    }

    /**
     * Kode revisi terkini gaya puskesmas (00, 01, …). NULL bila belum ada berkas.
     */
    public function kodeRevisiTerkini(): ?string
    {
        return $this->versi_terkini >= 1
            ? DokumenVersi::kodeRevisiDari($this->versi_terkini)
            : null;
    }

    // ── Review Berkala ───────────────────────────────────────────────────

    /**
     * Titik mulai siklus review: tanggal review/revisi terakhir, atau bila
     * belum pernah, tanggal dokumen.
     */
    public function anchorReview(): ?Carbon
    {
        return $this->tanggal_review_terakhir ?? $this->tanggal_dokumen;
    }

    /**
     * Tanggal dokumen ini harus ditinjau ulang. NULL bila kategori tidak
     * mewajibkan review berkala atau data tanggal belum lengkap.
     */
    public function jatuhTempoReview(): ?Carbon
    {
        $periode = (int) ($this->kategori?->periode_review_tahun ?? 0);
        $anchor = $this->anchorReview();

        if ($periode <= 0 || ! $anchor) {
            return null;
        }

        return $anchor->copy()->addYears($periode);
    }

    /**
     * Sisa hari menuju jatuh tempo review (negatif = sudah lewat).
     */
    public function sisaHariReview(): ?int
    {
        $jatuhTempo = $this->jatuhTempoReview();

        return $jatuhTempo
            ? (int) now()->startOfDay()->diffInDays($jatuhTempo, false)
            : null;
    }

    /**
     * Status review: null (tidak perlu) | 'aman' | 'segera' | 'lewat'.
     * Dokumen draf/dicabut diabaikan (tidak perlu review).
     */
    public function statusReview(): ?string
    {
        if (in_array($this->status, ['draf', 'dicabut'], true)) {
            return null;
        }

        $sisa = $this->sisaHariReview();

        if ($sisa === null) {
            return null;
        }

        return match (true) {
            $sisa < 0 => 'lewat',
            $sisa <= self::REVIEW_AMBANG_HARI => 'segera',
            default => 'aman',
        };
    }

    /**
     * Query: dokumen yang butuh ditinjau (segera atau sudah lewat) dalam
     * rentang $dalamHari ke depan. Perhitungan jatuh tempo di sisi SQL.
     */
    public function scopeButuhTinjauan(Builder $query, int $dalamHari = self::REVIEW_AMBANG_HARI): Builder
    {
        return $query
            ->join('kategori_dokumen', 'kategori_dokumen.id', '=', 'dokumen.kategori_id')
            ->whereNotNull('kategori_dokumen.periode_review_tahun')
            ->where('kategori_dokumen.periode_review_tahun', '>', 0)
            ->whereNotIn('dokumen.status', ['draf', 'dicabut'])
            ->whereRaw(
                'DATE_ADD(COALESCE(dokumen.tanggal_review_terakhir, dokumen.tanggal_dokumen), '
                .'INTERVAL kategori_dokumen.periode_review_tahun YEAR) <= ?',
                [now()->startOfDay()->addDays($dalamHari)->toDateString()]
            )
            ->select('dokumen.*');
    }
}
