<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenVersi extends Model
{
    protected $table = 'dokumen_versi';

    protected $fillable = [
        'dokumen_id',
        'nomor_versi',
        'catatan_revisi',
        'tanggal_revisi',
        'file_path',
        'ukuran_file',
        'diunggah_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nomor_versi' => 'integer',
            'tanggal_revisi' => 'date',
            'ukuran_file' => 'integer',
        ];
    }

    /**
     * Kode revisi gaya puskesmas: 2 digit mulai dari 00.
     * Versi internal 1-based → revisi 0-based (nomor_versi 1 = Revisi 00).
     */
    public static function kodeRevisiDari(int $nomorVersi): string
    {
        return str_pad((string) max(0, $nomorVersi - 1), 2, '0', STR_PAD_LEFT);
    }

    public function kodeRevisi(): string
    {
        return static::kodeRevisiDari($this->nomor_versi);
    }

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }
}
