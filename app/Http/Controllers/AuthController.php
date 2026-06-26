<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRoute());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        $credentials['aktif'] = true;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Nama pengguna atau kata sandi salah, atau akun dinonaktifkan.']);
        }

        $request->session()->regenerate();

        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aksi' => 'login',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->intended(route(Auth::user()->homeRoute()));
    }

    public function logout(Request $request): RedirectResponse
    {
        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aksi' => 'logout',
            'ip_address' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
