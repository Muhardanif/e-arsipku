@extends('layouts.app')

@section('title', 'Laporan Peminjaman')
@section('header', 'Laporan — Peminjaman Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Laporan', 'url' => route('laporan.index')],
        ['label' => 'Peminjaman'],
    ]" />
@endsection

@section('content')
@php
    $tabs = ['semua' => 'Semua', 'dipinjam' => 'Sedang Dipinjam', 'terlambat' => 'Terlambat', 'dikembalikan' => 'Dikembalikan'];
@endphp

<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <nav class="inline-flex flex-wrap rounded-xl border border-border-default bg-white p-1 text-sm shadow-xs">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('laporan.peminjaman', ['status' => $key]) }}"
                   class="rounded-lg px-3.5 py-1.5 font-semibold transition {{ $status === $key ? 'bg-gradient-to-br from-accent to-indigo text-white shadow-accent' : 'text-slate-600 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="flex flex-wrap items-center gap-2">
            <x-button variant="outline" size="sm" :href="route('laporan.peminjaman', array_merge(request()->query(), ['excel' => 'unduh']))">
                <x-slot:icon>@svg('heroicon-o-table-cells', 'h-5 w-5')</x-slot>
                Export Excel
            </x-button>
            <x-pdf-viewer
                judul="Laporan Peminjaman Dokumen"
                :stream="route('laporan.peminjaman', array_merge(request()->query(), ['pdf' => 'stream']))"
                :unduh="route('laporan.peminjaman', array_merge(request()->query(), ['pdf' => 'unduh']))" />
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Dokumen</th>
                        <th class="px-5 py-3">Peminjam</th>
                        <th class="px-5 py-3">Tujuan</th>
                        <th class="px-5 py-3">Pinjam</th>
                        <th class="px-5 py-3">Kembali</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($peminjaman as $i => $p)
                        @php $terlambat = $p->isTerlambat(); @endphp
                        <tr class="transition hover:bg-slate-50/60 {{ $terlambat ? 'bg-red-50/60' : '' }}">
                            <td class="px-5 py-3.5 tabular-nums text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('dokumen.show', $p->dokumen_id) }}" class="font-medium text-foreground hover:text-accent">{{ $p->dokumen?->judul ?? '—' }}</a>
                                <p class="text-xs text-slate-500">{{ $p->dokumen?->nomor_dokumen }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">{{ $p->peminjam_nama ?? $p->peminjam?->nama ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $p->tujuan }}</td>
                            <td class="px-5 py-3.5 tabular-nums text-slate-600">{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3.5 tabular-nums text-slate-600">
                                {{ $p->tanggal_kembali_aktual?->translatedFormat('d M Y') ?? $p->tanggal_kembali_rencana?->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($terlambat)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Terlambat</span>
                                @else
                                    <x-badge :status="$p->status" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-arrow-path-rounded-square" title="Tidak ada data peminjaman"
                                    desc="Belum ada peminjaman pada kategori ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
