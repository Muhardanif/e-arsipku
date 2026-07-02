<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>E-ARSIPKU | Puskesmas Driyorejo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="h-full bg-surface font-sans text-foreground antialiased">
@php
    $user = auth()->user();
    $roleLabel = ['admin' => 'Admin Arsip', 'petugas' => 'Petugas Arsip', 'staf' => 'Staf'][$user->role] ?? $user->role;
    // Data notifikasi disuntik oleh View Composer (AppServiceProvider) untuk role
    // admin & petugas; fallback aman bila composer tidak terpasang.
    $notifCount = $notifCount ?? 0;
    $notifItems = $notifItems ?? collect();
@endphp

<div
    x-data="{ sidebarOpen: false, collapsed: localStorage.getItem('ea-sidebar-collapsed') === '1' }"
    x-init="$watch('collapsed', v => {
        localStorage.setItem('ea-sidebar-collapsed', v ? '1' : '0');
        window.dispatchEvent(new CustomEvent('sidebar-collapse', { detail: v }));
    });
    // Kunci scroll body saat drawer mobile terbuka (hanya < lg, tempat sidebar
    // berperilaku sebagai overlay). Di desktop sidebar bukan drawer → biarkan.
    $watch('sidebarOpen', open => {
        const isMobile = window.matchMedia('(max-width: 1023px)').matches;
        document.body.classList.toggle('overflow-hidden', open && isMobile);
    });
    // Tutup drawer & lepaskan kunci bila viewport melar ke desktop saat terbuka.
    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 1024px)').matches && sidebarOpen) sidebarOpen = false;
    })"
    class="min-h-full">
    {{-- Overlay (mobile) --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         x-transition.opacity
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" aria-hidden="true"></div>

    {{-- ── Sidebar mengambang (floating) ────────────────────────── --}}
    {{-- Modern healthcare workspace: panel teal melayang, sudut membulat, shadow lembut.
         Collapse (desktop) dikendalikan class .is-collapsed via CSS agar mobile tetap penuh. --}}
    <aside
        class="app-sidebar fixed inset-y-3 left-3 z-40 flex flex-col overflow-hidden rounded-[22px] bg-primary ring-1 ring-white/[0.05] shadow-[0_18px_50px_-20px_rgba(2,32,28,0.55)] lg:translate-x-0"
        :class="{ 'is-collapsed': collapsed, 'translate-x-0': sidebarOpen, '-translate-x-[260px]': !sidebarOpen }">
        {{-- Brand — logo puskesmas + identitas institusi (E-ARSIPKU sebagai tag sistem) --}}
        <div class="sidebar-brand flex items-center gap-3 px-4 pt-6 pb-5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-md ring-1 ring-white/20">
                <img src="{{ asset('images/logo-puskesmas.png') }}" alt="Logo Puskesmas Driyorejo" class="h-9 w-9 object-contain">
            </span>
            <div class="sidebar-brand-text leading-tight">
                <p class="text-[15px] font-bold tracking-tight text-white">E-ARSIPKU</p>
                <p class="text-[11px] font-medium text-white/80">Puskesmas Driyorejo</p>
                <p class="text-[10px] text-white/60">Kabupaten Gresik</p>
            </div>
        </div>

        {{-- Menu — dikelompokkan, spasi lega, tanpa garis separator panjang --}}
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4 pt-1">
            <p class="sidebar-section px-3 pt-1 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/55">Operasional</p>

            <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                <x-slot:icon>@svg('heroicon-o-home', 'h-5 w-5')</x-slot:icon>
                Dashboard
            </x-nav-link>

            <x-nav-link href="{{ url('/dokumen') }}" :active="request()->is('dokumen*')">
                <x-slot:icon>@svg('heroicon-o-document-text', 'h-5 w-5')</x-slot:icon>
                Dokumen
            </x-nav-link>

            @if ($user->isAdmin() || $user->isPetugas())
                <x-nav-link href="{{ url('/peminjaman') }}" :active="request()->is('peminjaman*')">
                    <x-slot:icon>@svg('heroicon-o-arrow-path-rounded-square', 'h-5 w-5')</x-slot:icon>
                    Peminjaman
                </x-nav-link>

                <x-nav-link href="{{ url('/laporan') }}" :active="request()->is('laporan*')">
                    <x-slot:icon>@svg('heroicon-o-chart-bar', 'h-5 w-5')</x-slot:icon>
                    Laporan
                </x-nav-link>
            @endif

            @if ($user->isAdmin())
                <p class="sidebar-section px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/55">Manajemen</p>

                <x-nav-link href="{{ url('/master/kategori') }}" :active="request()->is('master/kategori*')">
                    <x-slot:icon>@svg('heroicon-o-tag', 'h-5 w-5')</x-slot:icon>
                    Kategori
                </x-nav-link>

                <x-nav-link href="{{ url('/master/klaster') }}" :active="request()->is('master/klaster*')">
                    <x-slot:icon>@svg('heroicon-o-squares-2x2', 'h-5 w-5')</x-slot:icon>
                    Klaster
                </x-nav-link>

                <x-nav-link href="{{ url('/pengguna') }}" :active="request()->is('pengguna*')">
                    <x-slot:icon>@svg('heroicon-o-users', 'h-5 w-5')</x-slot:icon>
                    Pengguna
                </x-nav-link>

                <x-nav-link href="{{ url('/audit-akses') }}" :active="request()->is('audit-akses*')">
                    <x-slot:icon>@svg('heroicon-o-finger-print', 'h-5 w-5')</x-slot:icon>
                    Audit Akses
                </x-nav-link>

                <x-nav-link href="{{ url('/log-aktivitas') }}" :active="request()->is('log-aktivitas*')">
                    <x-slot:icon>@svg('heroicon-o-clock', 'h-5 w-5')</x-slot:icon>
                    Log Aktivitas
                </x-nav-link>
            @endif
        </nav>

        {{-- Footer sidebar — penegas institusi resmi.
             Logo di luar .sidebar-brand-text agar tetap tampil & terpusat saat collapse. --}}
        <div class="sidebar-foot border-t border-white/15 px-5 py-4">
            <div class="sidebar-foot-inner flex items-center gap-2.5">
                <img src="{{ asset('images/logo-pemkab-gresik.png') }}" alt="Pemerintah Kabupaten Gresik"
                     class="h-7 w-7 shrink-0 object-contain opacity-95">
                <div class="sidebar-brand-text leading-tight">
                    <p class="text-[10px] font-medium uppercase tracking-wider text-white/55">Pemerintah</p>
                    <p class="text-[11px] font-semibold text-white/85">Kabupaten Gresik</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Konten ──────────────────────────────────────────────── --}}
    <div class="app-content flex min-h-full flex-col" :class="{ 'is-collapsed': collapsed }">
        {{-- ── Application header / navbar — satu keluarga kontrol ───────
             Sticky putih + shadow-sm (tanpa garis keras). Hamburger, bell,
             dan user menu memakai bahasa komponen yang sama: container
             ber-border halus, radius konsisten, hover abu lembut. --}}
        {{-- Pembungkus sticky ber-latar abu: menutup konten yang lewat di celah
             atas, sementara header putih mengambang (rounded + shadow) dengan
             jarak dari tepi — pola "floating panels". --}}
        <div class="sticky top-0 z-30 bg-surface px-4 pt-4 pb-3 sm:px-6">
        <header class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-sm sm:px-5">
            {{-- Toggle sidebar — mobile (buka drawer) --}}
            <button type="button" @click="sidebarOpen = true"
                class="w-10 h-10 flex shrink-0 items-center justify-center rounded-lg border border-border-default text-slate-500 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40 lg:hidden"
                aria-label="Buka menu">
                @svg('heroicon-o-bars-3', 'h-6 w-6')
            </button>

            {{-- Toggle sidebar — desktop (lipat / buka) --}}
            <button type="button" @click="collapsed = !collapsed"
                class="w-10 h-10 hidden shrink-0 items-center justify-center rounded-lg border border-border-default text-slate-500 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40 lg:flex"
                :aria-pressed="collapsed" aria-label="Lipat menu">
                <span class="transition-transform duration-200 motion-reduce:transition-none" :class="collapsed && 'rotate-180'">
                    @svg('heroicon-o-chevron-double-left', 'h-5 w-5')
                </span>
            </button>

            {{-- Info halaman --}}
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-[17px] font-semibold leading-tight text-foreground">@yield('header', 'Dashboard')</h1>
                <p class="truncate text-sm text-muted">@yield('subtitle', 'Sistem Pencatatan Arsip Dokumen Puskesmas')</p>
            </div>

            {{-- ── Aksi kanan ───────────────────────────────────────── --}}
            <div class="flex shrink-0 items-center gap-3">
                {{-- Notifikasi — bell + dropdown real-time (admin & petugas) --}}
                <x-notif-bell :count="$notifCount" :items="$notifItems" />

                {{-- Divider vertikal --}}
                <div class="w-px h-6 bg-border-default"></div>

                {{-- Menu pengguna --}}
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" :aria-expanded="open"
                        class="flex items-center gap-2 rounded-lg border border-border-default hover:bg-slate-100 transition pl-1 pr-3 py-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40">
                        <span class="w-9 h-9 rounded-full bg-accent text-white flex items-center justify-center text-xs font-medium">
                            {{ strtoupper(substr($user->nama, 0, 2)) }}
                        </span>
                        <span class="hidden text-left leading-tight sm:block">
                            <span class="block font-medium text-sm text-foreground">{{ $user->nama }}</span>
                            <span class="block text-xs text-slate-500">{{ $roleLabel }}</span>
                        </span>
                        <span class="hidden transition-transform duration-200 sm:block" :class="open && 'rotate-180'">
                            @svg('heroicon-o-chevron-down', 'h-4 w-4 text-slate-400')
                        </span>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                        x-transition.origin.top.right
                        class="absolute right-0 mt-2.5 w-60 origin-top-right rounded-lg border border-border-default bg-white py-1.5 shadow-pop">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
                            <span class="w-9 h-9 rounded-full bg-accent text-white flex items-center justify-center text-xs font-medium">
                                {{ strtoupper(substr($user->nama, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $user->nama }}</p>
                                <p class="truncate text-xs text-slate-500">{{ '@'.$user->username }} · {{ $roleLabel }}</p>
                            </div>
                        </div>
                        <a href="{{ route('profil.password.edit') }}"
                            class="mt-1 flex w-full items-center gap-2.5 rounded-md px-4 py-2.5 text-sm text-slate-700 transition-colors duration-150 hover:bg-slate-50">
                            @svg('heroicon-o-key', 'h-5 w-5 text-slate-400')
                            Ganti Kata Sandi
                        </a>
                        <form method="POST" action="{{ route('logout') }}"
                              data-confirm="Keluar dari sesi E-ARSIPKU?" data-confirm-btn="Ya, Keluar" data-confirm-icon="question">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2.5 rounded-md px-4 py-2.5 text-sm text-slate-700 transition-colors duration-150 hover:bg-slate-50">
                                @svg('heroicon-o-arrow-left-end-on-rectangle', 'h-5 w-5 text-slate-400')
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        </div>

        {{-- Page body --}}
        <main class="flex-1 px-4 pb-8 pt-2 sm:px-6">
            @hasSection('breadcrumb')
                <nav aria-label="Breadcrumb" class="mb-4 text-sm text-slate-500">
                    @yield('breadcrumb')
                </nav>
            @endif

            @php $flashType = collect(['success', 'error', 'warning', 'info'])->first(fn ($t) => session()->has($t)); @endphp
            @if ($flashType)
                <div id="flash-data" data-type="{{ $flashType }}" data-message="{{ session($flashType) }}" hidden></div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- ── Modal global (gaya Bootstrap, diisi via AJAX) ───────────────── --}}
<div id="ea-modal" class="ea-modal hidden fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-labelledby="ea-modal-title">
    <div class="ea-modal__backdrop absolute inset-0 bg-slate-900/50"></div>
    <div data-modal-backdrop class="absolute inset-0 flex items-start justify-center overflow-y-auto p-4 sm:p-6">
        <div class="ea-modal__dialog relative my-6 w-full max-w-2xl rounded-2xl bg-white shadow-pop sm:my-10">
            {{-- Header sticky: tetap tampil saat dialog di-scroll oleh backdrop.
                 Scroll dipindah ke level dialog agar dropdown (Choices) tidak
                 terpotong oleh kontainer ber-overflow di dalam form. --}}
            <div class="sticky top-0 z-10 flex items-center justify-between gap-4 rounded-t-2xl border-b border-slate-100 bg-white px-6 py-4">
                <h3 id="ea-modal-title" data-modal-title class="text-base font-semibold text-foreground">Formulir</h3>
                <button type="button" data-modal-close aria-label="Tutup"
                    class="-mr-1.5 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    @svg('heroicon-o-x-mark', 'h-5 w-5')
                </button>
            </div>
            <div data-modal-body></div>
        </div>
    </div>
</div>
</body>
</html>
