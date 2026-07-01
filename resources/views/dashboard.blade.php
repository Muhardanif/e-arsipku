@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subtitle', 'Ringkasan arsip digital Puskesmas Driyorejo')

@section('content')
    @php
        $user = auth()->user();
        $cards = [
            ['label' => 'Total Dokumen', 'value' => $stats['total_dokumen'], 'tone' => 'total', 'icon' => 'document-text'],
            ['label' => 'Dokumen Berlaku', 'value' => $stats['dokumen_berlaku'], 'tone' => 'berlaku', 'icon' => 'check-circle'],
            ['label' => 'Draf (belum terbit)', 'value' => $stats['draf'], 'tone' => 'draf', 'icon' => 'clock', 'url' => route('dokumen.index', ['status' => 'draf'])],
            ['label' => 'Perlu Review (≤90 hari)', 'value' => $stats['perlu_review'], 'tone' => 'review', 'icon' => 'arrow-path', 'url' => route('laporan.review')],
        ];
        // Tint ikon — token brand + skala semantik (tanpa hex mentah)
        $tones = [
            'total'      => ['bg' => 'bg-accent-soft', 'text' => 'text-accent'],
            'berlaku'    => ['bg' => 'bg-accent-soft', 'text' => 'text-accent'],
            'draf'       => ['bg' => 'bg-amber-50',    'text' => 'text-amber-600'],
            'kadaluarsa' => ['bg' => 'bg-red-50',      'text' => 'text-red-600'],
            'review'     => ['bg' => 'bg-violet-50',   'text' => 'text-violet-600'],
            'dipinjam'   => ['bg' => 'bg-sky-50',      'text' => 'text-sky-600'],
        ];
    @endphp

    {{-- Greeting --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-foreground">Selamat datang, {{ explode(' ', $user->nama)[0] }}</h2>
            <p class="mt-1 text-sm text-slate-500">Ringkasan arsip dokumen <span class="font-medium text-slate-600">Puskesmas Driyorejo</span> per {{ now()->translatedFormat('d F Y') }}.</p>
        </div>
        @if ($user->isAdmin() || $user->isPetugas())
            <x-button variant="primary" size="sm" :href="route('dokumen.create')" data-modal data-modal-title="Tambah Dokumen">
                <x-slot:icon>@svg('heroicon-o-plus', 'h-5 w-5')</x-slot>
                Tambah Dokumen
            </x-button>
        @endif
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as $card)
            @php $t = $tones[$card['tone']]; @endphp
            <{{ $tag = isset($card['url']) ? 'a' : 'div' }} @isset($card['url']) href="{{ $card['url'] }}" @endisset
                class="card group relative block p-5 transition-all duration-200 hover:-translate-y-0.5 hover:[box-shadow:var(--shadow-card-hover)]">
                <div class="flex items-start justify-between">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $t['bg'] }} {{ $t['text'] }} ring-1 ring-black/[0.03]">
                        @svg('heroicon-o-'.$card['icon'], 'h-6 w-6')
                    </span>
                    @isset($card['url'])
                        <span class="opacity-0 transition-opacity duration-200 group-hover:opacity-100 motion-reduce:transition-none">
                            @svg('heroicon-o-arrow-up-right', 'h-4 w-4 text-slate-400')
                        </span>
                    @endisset
                </div>
                <p class="mt-3 text-3xl font-bold tabular-nums tracking-tight text-foreground">{{ number_format($card['value'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-sm font-medium text-muted">{{ $card['label'] }}</p>
            </{{ $tag }}>
        @endforeach
    </div>

    {{-- Dokumen perlu ditinjau --}}
    @if ($dokumenPerluReview->isNotEmpty())
        <section class="card mt-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        @svg('heroicon-o-arrow-path', 'h-4 w-4')
                    </span>
                    Dokumen Perlu Ditinjau
                </h2>
                <a href="{{ route('laporan.review') }}" class="text-sm font-semibold text-accent transition hover:text-accent-hover">Lihat semua →</a>
            </div>
            @foreach ($dokumenPerluReview as $dok)
                <a href="{{ route('dokumen.show', $dok) }}" class="flex items-center justify-between gap-4 border-b border-slate-50 px-5 py-3.5 transition last:border-0 hover:bg-slate-50/70">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">{{ $dok->judul }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $dok->nomor_dokumen }} · jatuh tempo {{ $dok->jatuhTempoReview()?->translatedFormat('d M Y') }}</p>
                    </div>
                    <x-review-status :dokumen="$dok" />
                </a>
            @endforeach
        </section>
    @endif

    {{-- Grafik --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="card p-5 lg:col-span-2">
            <h2 class="text-sm font-semibold text-foreground">Dokumen per Kategori</h2>
            <p class="text-xs text-slate-500">Jumlah dokumen di tiap kategori</p>
            <div data-chart="kategori" data-config='@json(['labels' => $chartKategori['labels'], 'data' => $chartKategori['data']])' class="mt-2 min-h-[260px]"></div>
        </section>

        <section class="card p-5">
            <h2 class="text-sm font-semibold text-foreground">Komposisi Status Dokumen</h2>
            <p class="text-xs text-slate-500">Seluruh dokumen</p>
            <div data-chart="status" data-config='@json($chartStatus)' class="mt-2 min-h-[260px]"></div>
        </section>
    </div>

    {{-- Dokumen terbaru --}}
    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-accent-soft text-accent">
                    @svg('heroicon-o-book-open', 'h-4 w-4')
                </span>
                Dokumen Terbaru
            </h2>
            <a href="{{ url('/dokumen') }}" class="text-sm font-semibold text-accent transition hover:text-accent-hover">Lihat semua →</a>
        </div>
        @forelse ($dokumenTerbaru as $dok)
            <a href="{{ route('dokumen.show', $dok) }}" class="flex items-center justify-between gap-4 border-b border-slate-50 px-5 py-3.5 transition hover:bg-slate-50/70 last:border-0">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">{{ $dok->judul }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $dok->nomor_dokumen }} · {{ $dok->kategori?->nama }}</p>
                </div>
                <x-badge :status="$dok->status" />
            </a>
        @empty
            <div class="px-5 py-12">
                <x-empty-state icon="heroicon-o-book-open" title="Belum ada dokumen tercatat"
                    desc="Dokumen baru akan tampil di sini." />
            </div>
        @endforelse
    </section>
@endsection
