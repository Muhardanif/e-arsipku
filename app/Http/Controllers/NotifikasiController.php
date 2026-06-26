<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiDibaca;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Menandai notifikasi (kedaluwarsa / review) sudah dibaca per pengguna.
 * Dilindungi middleware role:admin,petugas pada definisi route.
 */
class NotifikasiController extends Controller
{
    /** Tandai satu notifikasi sudah dibaca. */
    public function baca(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dokumen_id' => ['required', 'integer', 'exists:dokumen,id'],
            'jenis' => ['required', 'in:kedaluwarsa,review'],
            'tanggal_acuan' => ['required', 'date'],
            'tingkat' => ['required', 'in:info,warning,kritis,danger'],
        ]);

        $this->tandai(
            $request->user()->id,
            (int) $data['dokumen_id'],
            $data['jenis'],
            $data['tanggal_acuan'],
            $data['tingkat'],
        );

        return back();
    }

    /** Tandai semua notifikasi aktif user sudah dibaca. */
    public function bacaSemua(Request $request, NotifikasiService $notifikasi): RedirectResponse
    {
        $user = $request->user();

        foreach ($notifikasi->ambil($user) as $n) {
            $this->tandai($user->id, $n['dokumen_id'], $n['jenis'], $n['acuan']->toDateString(), $n['tingkat']);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function tandai(int $userId, int $dokumenId, string $jenis, string $acuan, string $tingkat): void
    {
        NotifikasiDibaca::updateOrCreate(
            ['user_id' => $userId, 'dokumen_id' => $dokumenId, 'jenis' => $jenis, 'tanggal_acuan' => $acuan],
            ['tingkat' => $tingkat, 'dibaca_pada' => now()],
        );
    }
}
