<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HarusGantiPassword
{
    /**
     * Paksa pengguna yang ditandai harus_ganti_password (akun baru dibuat
     * admin atau kata sandinya direset) untuk mengganti kata sandi sebelum
     * mengakses halaman lain. Route ganti kata sandi & logout dikecualikan
     * agar tidak terjadi pengalihan tak berujung.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && $user->harus_ganti_password
            && ! $request->routeIs('profil.password.edit', 'profil.password.update', 'logout')) {
            return redirect()->route('profil.password.edit');
        }

        return $next($request);
    }
}
