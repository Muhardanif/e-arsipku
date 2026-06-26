<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;

abstract class Controller
{
    /**
     * Validasi rentang tanggal filter (between).
     * Mengembalikan pesan error bila tanggal akhir lebih kecil dari tanggal awal,
     * atau null bila rentang valid / salah satu kosong.
     */
    protected function validasiRentangTanggal(?string $dari, ?string $sampai): ?string
    {
        if (! $dari || ! $sampai) {
            return null;
        }

        try {
            $awal = Carbon::parse($dari)->startOfDay();
            $akhir = Carbon::parse($sampai)->startOfDay();
        } catch (\Exception $e) {
            return 'Format tanggal filter tidak valid.';
        }

        if ($akhir->lt($awal)) {
            return 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.';
        }

        return null;
    }
}
