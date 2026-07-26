<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuAksesMiddleware
{
    /**
     * Batasi akses route berdasarkan hak akses menu per role
     * (tabel menu_akses, diatur admin di Master > Hak Akses Menu).
     *
     * Pemakaian di route: ->middleware('menu:peminjaman')
     */
    public function handle(Request $request, Closure $next, string $menu): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->aktif) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi administrator.']);
        }

        if (! $user->bolehMenu($menu)) {
            abort(403, 'Anda tidak memiliki akses ke menu ini.');
        }

        return $next($request);
    }
}
