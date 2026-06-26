@extends('layouts.guest')

@section('title', 'Masuk')

@php $appVersion = 'v1.0'; @endphp

@section('content')
<div class="grid min-h-dvh lg:h-dvh lg:grid-cols-2 lg:overflow-hidden">

    {{-- ══ Panel brand (kiri, desktop) ═══════════════════════════════ --}}
    <div class="brand-gradient relative hidden overflow-hidden lg:flex lg:flex-col lg:p-10 xl:p-14">

        {{-- Lapisan dekoratif (z-0) --}}
        <div class="pointer-events-none absolute inset-0">
            {{-- Pola blueprint halus --}}
            <div class="absolute inset-0 opacity-[0.06]"
                 style="background-image:linear-gradient(rgb(255_255_255)_1px,transparent_1px),linear-gradient(90deg,rgb(255_255_255)_1px,transparent_1px);background-size:38px_38px"></div>
            {{-- Orb glow lembut --}}
            <div class="ea-orb ea-orb--1 h-80 w-80 bg-white/15" style="top:-6rem;right:-5rem"></div>
            <div class="ea-orb ea-orb--2 h-72 w-72 bg-white/10" style="bottom:-5rem;left:-6rem"></div>
            {{-- Watermark arsip (abstrak, sangat subtle — bukan focal point) --}}
            <div class="absolute -right-20 -bottom-6 text-white/[0.025]">
                @svg('heroicon-o-folder-open', 'h-[24rem] w-[24rem]')
            </div>
        </div>

        {{-- Brand + heading: dikelompokkan & dipusatkan vertikal --}}
        <div class="relative z-10 flex flex-1 flex-col justify-center">
            <div class="max-w-lg">
                <span class="auth-logo-box ea-enter" style="animation-delay:.05s">
                    <img src="{{ asset('images/logo-earsipku.png') }}" alt="E-ARSIPKU" class="h-12 w-auto">
                </span>

                <span class="ea-enter mt-9 inline-flex items-center gap-1.5 rounded-full bg-white/[0.12] px-2.5 py-1 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-white/90 ring-1 ring-white/20" style="animation-delay:.12s">
                    @svg('heroicon-o-shield-check', 'h-3.5 w-3.5')
                    Sistem Arsip Digital
                </span>

                <h1 class="ea-enter mt-3.5 max-w-md text-[2.05rem] font-bold leading-[1.18] tracking-tight text-white xl:text-[2.5rem]" style="animation-delay:.18s">
                    Kelola arsip dokumen dengan rapi &amp; aman.
                </h1>
                <p class="ea-enter mt-4 max-w-sm text-[15px] leading-relaxed text-white/80" style="animation-delay:.24s">
                    Satu pusat arsip digital untuk seluruh dokumen administrasi puskesmas.
                </p>

                <ul class="mt-6 grid max-w-sm gap-3">
                    @foreach ([
                        ['heroicon-o-document-duplicate', 'Riwayat revisi & versi dokumen otomatis'],
                        ['heroicon-o-arrow-path-rounded-square', 'Pelacakan peminjaman dokumen fisik'],
                        ['heroicon-o-chart-bar', 'Laporan & ekspor PDF siap cetak'],
                    ] as $i => $fitur)
                        <li class="ea-enter flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.04] px-3.5 py-2.5 text-[13.5px] text-slate-100"
                            style="animation-delay:{{ 0.3 + $i * 0.08 }}s">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white ring-1 ring-white/20">
                                @svg($fitur[0], 'h-4 w-4')
                            </span>
                            {{ $fitur[1] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Footer: logo pemerintah + versi --}}
        <div class="ea-enter relative z-10 mt-8 border-t border-white/10 pt-5" style="animation-delay:.6s">
            <p class="text-[11px] font-medium uppercase tracking-wider text-white/45">Diselenggarakan oleh</p>
            <div class="mt-3 flex items-center gap-4">
                <img src="{{ asset('images/logo-pemkab-gresik.png') }}" alt="Pemerintah Kabupaten Gresik" class="h-12 w-auto drop-shadow">
                <img src="{{ asset('images/logo-puskesmas.png') }}" alt="Puskesmas Driyorejo" class="h-12 w-auto drop-shadow">
                <div class="border-l border-white/15 pl-4 leading-tight">
                    <p class="text-sm font-semibold text-white">Puskesmas Driyorejo</p>
                    <p class="text-xs text-white/75">Pemerintah Kabupaten Gresik</p>
                </div>
            </div>
            <div class="mt-6 flex items-center gap-2 text-xs text-white/65">
                <span>&copy; {{ date('Y') }} E-ARSIPKU</span>
                <span class="h-1 w-1 rounded-full bg-white/25"></span>
                <span>{{ $appVersion }}</span>
                <span class="h-1 w-1 rounded-full bg-white/25"></span>
                <span>Lingkungan internal</span>
            </div>
        </div>
    </div>

    {{-- ══ Form (kanan) ═════════════════════════════════════════════ --}}
    <div class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="ea-enter w-full max-w-[420px] lg:-translate-y-10" style="animation-delay:.1s">
            <div class="auth-card p-6 sm:p-8">

                {{-- Brand mobile (di dalam kartu) --}}
                <div class="mb-7 lg:hidden">
                    <img src="{{ asset('images/logo-earsipku.png') }}" alt="E-ARSIPKU" class="mx-auto h-11 w-auto">
                    <div class="brand-gradient mt-4 flex items-center justify-center gap-3 rounded-xl px-4 py-2.5">
                        <img src="{{ asset('images/logo-pemkab-gresik.png') }}" alt="Pemerintah Kabupaten Gresik" class="h-9 w-auto">
                        <img src="{{ asset('images/logo-puskesmas.png') }}" alt="Puskesmas Driyorejo" class="h-9 w-auto">
                        <div class="border-l border-white/15 pl-3 text-left leading-tight">
                            <p class="text-[11px] font-semibold text-white">Puskesmas Driyorejo</p>
                            <p class="text-[10px] text-white/75">Pemkab Gresik</p>
                        </div>
                    </div>
                </div>

                {{-- Heading --}}
                <div class="mb-6">
                    <h2 class="text-[22px] font-bold tracking-tight text-foreground">Masuk ke akun Anda</h2>
                    <p class="mt-1.5 text-sm text-slate-500">Masukkan kredensial untuk melanjutkan.</p>
                </div>

                {{-- Alert error --}}
                @error('username')
                    <div role="alert" aria-live="assertive"
                         class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        @svg('heroicon-o-exclamation-circle', 'mt-0.5 h-5 w-5 shrink-0')
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <form method="POST" action="{{ route('login') }}" class="space-y-5"
                      x-data="{ show: false, loading: false }" @submit="loading = true">
                    @csrf

                    {{-- Nama pengguna --}}
                    <div class="auth-field @error('username') is-error @enderror">
                        <label for="username" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Pengguna</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center">
                                <span class="auth-icon">@svg('heroicon-o-user', 'h-5 w-5')</span>
                            </span>
                            <input id="username" name="username" type="text" value="{{ old('username') }}"
                                required autofocus autocomplete="username"
                                @error('username') aria-invalid="true" @enderror
                                class="auth-input @error('username') is-error @enderror" placeholder="mis. admin">
                        </div>
                    </div>

                    {{-- Kata sandi --}}
                    <div class="auth-field @error('username') is-error @enderror">
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata Sandi</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center">
                                <span class="auth-icon">@svg('heroicon-o-lock-closed', 'h-5 w-5')</span>
                            </span>
                            <input id="password" name="password" :type="show ? 'text' : 'password'"
                                required autocomplete="current-password"
                                @error('username') aria-invalid="true" @enderror
                                class="auth-input pr-12 @error('username') is-error @enderror" placeholder="••••••••">
                            <button type="button" @click="show = !show"
                                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-slate-600 focus:outline-none focus-visible:text-accent">
                                @svg('heroicon-o-eye', 'h-5 w-5', ['x-show' => '!show'])
                                @svg('heroicon-o-eye-slash', 'h-5 w-5', ['x-show' => 'show', 'x-cloak' => true])
                            </button>
                        </div>
                    </div>

                    {{-- Ingat saya --}}
                    <label class="flex cursor-pointer items-center gap-2.5 select-none">
                        <input id="remember" name="remember" type="checkbox" class="auth-check-input peer sr-only">
                        <span class="auth-check-box">
                            @svg('heroicon-o-check', 'h-3.5 w-3.5 stroke-[3]')
                        </span>
                        <span class="text-sm text-slate-600">Ingat saya di perangkat ini</span>
                    </label>

                    {{-- Tombol --}}
                    <button type="submit" class="auth-btn" :class="loading && 'pointer-events-none opacity-90'">
                        <span x-show="!loading" class="flex items-center gap-2">
                            Masuk
                            @svg('heroicon-o-arrow-right', 'h-5 w-5')
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

                {{-- Info keamanan --}}
                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 border-t border-slate-100 pt-5 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        @svg('heroicon-o-shield-check', 'h-4 w-4 text-accent')
                        Sistem internal Puskesmas
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        @svg('heroicon-o-lock-closed', 'h-4 w-4 text-accent')
                        Data dokumen tersimpan aman
                    </span>
                </div>
            </div>

            {{-- Footer kartu --}}
            <p class="mt-4 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} E-ARSIPKU · {{ $appVersion }} · Hanya untuk lingkungan internal puskesmas
            </p>
        </div>
    </div>
</div>
@endsection
