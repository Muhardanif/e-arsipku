<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenReview extends Model
{
    protected $table = 'dokumen_review';

    protected $fillable = [
        'dokumen_id',
        'tanggal_review',
        'ditinjau_oleh',
        'hasil',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_review' => 'date',
        ];
    }

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }
}
