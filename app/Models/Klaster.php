<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Klaster extends Model
{
    protected $table = 'klaster';

    protected $fillable = [
        'kode',
        'nama',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'klaster_id');
    }
}
