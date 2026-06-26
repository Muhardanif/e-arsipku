@extends('layouts.app')

@section('title', 'Laporan')
@section('header', 'Laporan')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Laporan']]" />
@endsection

@section('content')
@php
    $laporan = [
        [
            'judul' => 'Daftar Dokumen',
            'desc'  => 'Rekap seluruh dokumen dengan filter kategori, status, dan periode.',
            'url'   => route('laporan.dokumen'),
            'tone'  => 'from-accent to-indigo',
            'icon'  => 'document-text',
            'count' => $stats['total_dokumen'],
            'label' => 'total dokumen',
        ],
        [
            'judul' => 'Dokumen Akan Kadaluarsa',
            'desc'  => 'Dokumen yang masa berlakunya akan / sudah berakhir.',
            'url'   => route('laporan.kadaluarsa'),
            'tone'  => 'from-amber-400 to-orange-500',
            'icon'  => 'exclamation-triangle',
            'count' => $stats['akan_kadaluarsa'],
            'label' => 'dalam ≤30 hari',
        ],
        [
            'judul' => 'Dokumen Perlu Review',
            'desc'  => 'Dokumen yang jatuh tempo tinjauan berkala (segera atau sudah lewat).',
            'url'   => route('laporan.review'),
            'tone'  => 'from-violet-400 to-purple-500',
            'icon'  => 'arrow-path',
            'count' => $stats['perlu_review'],
            'label' => 'dalam ≤90 hari',
        ],
        [
            'judul' => 'Peminjaman Dokumen',
            'desc'  => 'Riwayat peminjaman: aktif, dikembalikan, dan terlambat.',
            'url'   => route('laporan.peminjaman'),
            'tone'  => 'from-sky-500 to-blue-600',
            'icon'  => 'trophy',
            'count' => $stats['peminjaman_aktif'],
            'label' => 'sedang dipinjam',
        ],
    ];
@endphp

<p class="mb-5 text-sm text-slate-500">Pilih jenis laporan untuk ditampilkan, dicetak, atau diekspor ke PDF.</p>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($laporan as $l)
        <a href="{{ $l['url'] }}"
           class="card group relative overflow-hidden p-6 transition-all duration-200 hover:-translate-y-0.5 hover:[box-shadow:var(--shadow-card-hover)]">
            <div class="flex items-start justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $l['tone'] }} text-white shadow-md ring-1 ring-white/30">
                    @svg('heroicon-o-'.$l['icon'], 'h-6 w-6')
                </span>
                <span class="text-right">
                    <span class="block text-2xl font-bold tabular-nums text-foreground">{{ number_format($l['count'], 0, ',', '.') }}</span>
                    <span class="block text-xs text-slate-400">{{ $l['label'] }}</span>
                </span>
            </div>
            <h3 class="mt-4 text-base font-semibold text-foreground">{{ $l['judul'] }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $l['desc'] }}</p>
            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-accent">
                Buka laporan
                @svg('heroicon-o-arrow-right', 'h-4 w-4 transition-transform group-hover:translate-x-0.5')
            </span>
        </a>
    @endforeach
</div>
@endsection
