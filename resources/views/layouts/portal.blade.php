<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Portal Pencarian') | E-ARSIPKU</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="flex h-full min-h-full flex-col bg-surface font-sans text-foreground antialiased">
@php
    $user = auth()->user();
    $roleLabel = ['admin' => 'Administrator', 'petugas' => 'Petugas', 'staf' => 'Staf'][$user->role] ?? $user->role;
@endphp

{{-- ── Topbar bersih ───────────────────────────────────────────────── --}}
<header class="sticky top-0 z-30 border-b border-border-default/80 bg-white/85 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Brand --}}
        <a href="{{ route('portal.index') }}" class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-soft ring-1 ring-accent/10">
                <img src="{{ asset('images/logo-puskesmas.png') }}" alt="Logo Puskesmas Driyorejo" class="h-7 w-7 object-contain">
            </span>
            <div class="leading-tight">
                <p class="text-sm font-bold tracking-tight text-foreground">E-ARSIPKU</p>
                <p class="text-[11px] text-slate-500">Puskesmas Driyorejo · Kab. Gresik</p>
            </div>
        </a>

        <div class="flex-1"></div>

        {{-- Pintasan ke panel admin (hanya admin/petugas) --}}
        @if ($user->isAdmin() || $user->isPetugas())
            <a href="{{ route('dashboard') }}"
               class="hidden items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-foreground sm:inline-flex">
                @svg('heroicon-o-squares-2x2', 'h-5 w-5 text-slate-400')
                Panel Admin
            </a>
        @endif

        {{-- Menu pengguna --}}
        <div x-data="{ open: false }" class="relative">
            <button type="button" @click="open = !open" :aria-expanded="open"
                class="flex items-center gap-2.5 rounded-lg p-1.5 pr-2.5 text-left transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-accent to-indigo text-xs font-semibold text-white ring-2 ring-white shadow-xs">
                    {{ strtoupper(substr($user->nama, 0, 2)) }}
                </span>
                <span class="hidden text-sm leading-tight sm:block">
                    <span class="block font-medium text-foreground">{{ $user->nama }}</span>
                    <span class="block text-xs text-slate-500">{{ $user->jabatan ?: $roleLabel }}</span>
                </span>
                @svg('heroicon-o-chevron-down', 'h-4 w-4 text-slate-400')
            </button>

            <div x-show="open" x-cloak @click.outside="open = false"
                x-transition.origin.top.right
                class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl border border-border-default bg-white py-1.5 shadow-pop">
                <div class="border-b border-slate-100 px-4 py-2.5">
                    <p class="text-sm font-medium text-foreground">{{ $user->nama }}</p>
                    <p class="text-xs text-slate-500">{{ '@'.$user->username }} · {{ $roleLabel }}</p>
                </div>
                @if ($user->isAdmin() || $user->isPetugas())
                    <a href="{{ route('dashboard') }}"
                       class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 sm:hidden">
                        @svg('heroicon-o-squares-2x2', 'h-5 w-5 text-slate-400')
                        Panel Admin
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}"
                      data-confirm="Keluar dari sesi E-ARSIPKU?" data-confirm-btn="Ya, Keluar" data-confirm-icon="question">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50">
                        @svg('heroicon-o-arrow-left-end-on-rectangle', 'h-5 w-5 text-slate-400')
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- ── Konten ──────────────────────────────────────────────────────── --}}
<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
    @php $flashType = collect(['success', 'error', 'warning', 'info'])->first(fn ($t) => session()->has($t)); @endphp
    @if ($flashType)
        <div id="flash-data" data-type="{{ $flashType }}" data-message="{{ session($flashType) }}" hidden></div>
    @endif

    @yield('content')
</main>

{{-- ── Footer ──────────────────────────────────────────────────────── --}}
<footer class="border-t border-border-default/70 py-5">
    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 text-xs text-slate-400 sm:flex-row sm:px-6 lg:px-8">
        <p>E-ARSIPKU — Sistem Pencatatan Arsip Dokumen Puskesmas</p>
        <p>Puskesmas Driyorejo · Pemerintah Kabupaten Gresik</p>
    </div>
</footer>
</body>
</html>
