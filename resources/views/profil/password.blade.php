@extends('layouts.guest')

@section('title', 'Ganti Kata Sandi')

@php
    $user = auth()->user();
    $appVersion = 'v1.0';
@endphp

@section('content')
<div class="flex min-h-dvh flex-col items-center justify-center px-4 py-10 sm:px-6">

    {{-- Brand — logo di tengah, jarak rapat ke card --}}
    <div class="ea-enter mb-6 flex flex-col items-center text-center" style="animation-delay:.04s">
        <img src="{{ asset('images/logo-earsipku.png') }}" alt="E-ARSIPKU" class="h-12 w-auto">
        <p class="mt-2.5 text-sm text-slate-500">Puskesmas Driyorejo · Kabupaten Gresik</p>
    </div>

    {{-- Kartu ganti kata sandi --}}
    <div class="ea-enter w-full max-w-[460px]" style="animation-delay:.08s">
        <div class="auth-card p-6 sm:p-8">

            {{-- Header kartu --}}
            <div class="mb-6">
                <h1 class="text-[22px] font-bold tracking-tight text-foreground">Ganti Kata Sandi</h1>
                <p class="mt-1.5 text-sm text-slate-500">Perbarui kata sandi akun untuk menjaga keamanan arsip dokumen.</p>
            </div>

            {{-- Notice: paksaan ganti saat login pertama --}}
            @if ($wajib)
                <div role="alert" aria-live="polite"
                     class="mb-5 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    @svg('heroicon-o-shield-exclamation', 'mt-0.5 h-5 w-5 shrink-0 text-amber-500')
                    <span>Demi keamanan, Anda wajib mengganti kata sandi sementara sebelum melanjutkan.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('profil.password.update') }}" class="space-y-5"
                  x-data="{ showCurrent: false, showNew: false, loading: false }" @submit="loading = true">
                @csrf
                @method('PATCH')

                {{-- Kata sandi saat ini --}}
                <div class="auth-field @error('current_password') is-error @enderror">
                    <label for="current_password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata Sandi Saat Ini</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center">
                            <span class="auth-icon">@svg('heroicon-o-lock-closed', 'h-5 w-5')</span>
                        </span>
                        <input id="current_password" name="current_password" :type="showCurrent ? 'text' : 'password'"
                            required autofocus autocomplete="current-password"
                            @error('current_password') aria-invalid="true" @enderror
                            class="auth-input pr-12 @error('current_password') is-error @enderror" placeholder="••••••••">
                        <button type="button" @click="showCurrent = !showCurrent"
                            :aria-label="showCurrent ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-slate-600 focus:outline-none focus-visible:text-accent">
                            @svg('heroicon-o-eye', 'h-5 w-5', ['x-show' => '!showCurrent'])
                            @svg('heroicon-o-eye-slash', 'h-5 w-5', ['x-show' => 'showCurrent', 'x-cloak' => true])
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kata sandi baru --}}
                <div class="auth-field @error('password') is-error @enderror">
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center">
                            <span class="auth-icon">@svg('heroicon-o-key', 'h-5 w-5')</span>
                        </span>
                        <input id="password" name="password" :type="showNew ? 'text' : 'password'"
                            required minlength="6" autocomplete="new-password"
                            @error('password') aria-invalid="true" @enderror
                            class="auth-input pr-12 @error('password') is-error @enderror" placeholder="Minimal 6 karakter">
                        <button type="button" @click="showNew = !showNew"
                            :aria-label="showNew ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-slate-600 focus:outline-none focus-visible:text-accent">
                            @svg('heroicon-o-eye', 'h-5 w-5', ['x-show' => '!showNew'])
                            @svg('heroicon-o-eye-slash', 'h-5 w-5', ['x-show' => 'showNew', 'x-cloak' => true])
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi kata sandi baru --}}
                <div class="auth-field">
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center">
                            <span class="auth-icon">@svg('heroicon-o-key', 'h-5 w-5')</span>
                        </span>
                        <input id="password_confirmation" name="password_confirmation" :type="showNew ? 'text' : 'password'"
                            required minlength="6" autocomplete="new-password"
                            class="auth-input pr-4" placeholder="Ulangi kata sandi baru">
                    </div>
                </div>

                {{-- Tombol --}}
                <button type="submit" class="auth-btn" :class="loading && 'pointer-events-none opacity-90'">
                    <span x-show="!loading" class="flex items-center gap-2">
                        Simpan Kata Sandi
                        @svg('heroicon-o-check', 'h-5 w-5')
                    </span>
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Memproses…
                    </span>
                </button>
            </form>

            {{-- Tips kata sandi --}}
            <ul class="mt-6 space-y-1.5 rounded-xl bg-accent-soft px-4 py-3 text-xs text-slate-600">
                <li class="flex items-center gap-2">
                    @svg('heroicon-o-check-circle', 'h-4 w-4 shrink-0 text-accent')
                    Gunakan minimal 6 karakter
                </li>
                <li class="flex items-center gap-2">
                    @svg('heroicon-o-check-circle', 'h-4 w-4 shrink-0 text-accent')
                    Hindari kata sandi yang mudah ditebak
                </li>
            </ul>

            {{-- Aksi sekunder --}}
            <div class="mt-6 flex items-center justify-center border-t border-slate-100 pt-5 text-sm">
                @if ($wajib)
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 text-slate-500 transition hover:text-foreground">
                            @svg('heroicon-o-arrow-left-end-on-rectangle', 'h-4 w-4')
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route($user->homeRoute()) }}"
                       class="inline-flex items-center gap-1.5 text-slate-500 transition hover:text-foreground">
                        @svg('heroicon-o-arrow-left', 'h-4 w-4')
                        Kembali
                    </a>
                @endif
            </div>
        </div>

        {{-- Footer kartu --}}
        <p class="mt-4 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} E-ARSIPKU · {{ $appVersion }} · Hanya untuk lingkungan internal puskesmas
        </p>
    </div>
</div>
@endsection
