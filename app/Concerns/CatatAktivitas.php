<?php

namespace App\Concerns;

use App\Models\LogAktivitas;
use Illuminate\Database\Eloquent\Model;

trait CatatAktivitas
{
    /**
     * Catat aktivitas penting ke tabel log_aktivitas.
     */
    protected function catatLog(string $aksi, ?Model $record = null, array $detail = []): void
    {
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'tabel' => $record?->getTable(),
            'record_id' => $record?->getKey(),
            'detail' => $detail ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
