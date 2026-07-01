<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfilController extends Controller
{
    use CatatAktivitas;

    /**
     * Form ganti kata sandi sendiri. Halaman ini juga menjadi tujuan
     * paksaan bila pengguna ditandai harus_ganti_password.
     */
    public function editPassword(): View
    {
        return view('profil.password', [
            'wajib' => (bool) auth()->user()->harus_ganti_password,
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => $request->validated('password'),
            'harus_ganti_password' => false,
        ]);

        $this->catatLog('ganti_password', $user, ['username' => $user->username]);

        return redirect()->route($user->homeRoute())
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
