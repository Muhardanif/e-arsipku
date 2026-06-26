<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penanda baca notifikasi per pengguna. Lihat migration notifikasi_dibaca.
 */
class NotifikasiDibaca extends Model
{
    protected $table = 'notifikasi_dibaca';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'dokumen_id',
        'jenis',
        'tanggal_acuan',
        'tingkat',
        'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_acuan' => 'date',
            'dibaca_pada' => 'datetime',
        ];
    }
}
