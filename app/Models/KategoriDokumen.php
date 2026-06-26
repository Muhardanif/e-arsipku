<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriDokumen extends Model
{
    protected $table = 'kategori_dokumen';

    protected $fillable = [
        'kode',
        'nama',
        'keterangan',
        'format_nomor',
        'digit_nomor',
        'periode_review_tahun',
    ];

    protected function casts(): array
    {
        return [
            'digit_nomor' => 'integer',
            'periode_review_tahun' => 'integer',
        ];
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'kategori_id');
    }

    /**
     * Apakah kategori ini punya template penomoran otomatis.
     */
    public function punyaFormatNomor(): bool
    {
        return filled($this->format_nomor);
    }

    /**
     * Apakah dokumen kategori ini wajib ditinjau berkala.
     */
    public function perluReview(): bool
    {
        return (int) $this->periode_review_tahun > 0;
    }
}
