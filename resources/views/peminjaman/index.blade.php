@extends('layouts.app')

@section('title', 'Peminjaman')
@section('header', 'Peminjaman Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Peminjaman']]" />
@endsection

@section('content')
@php
    $tabs = ['dipinjam' => 'Sedang Dipinjam', 'terlambat' => 'Terlambat', 'dikembalikan' => 'Dikembalikan'];
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <nav class="inline-flex rounded-xl border border-border-default bg-white p-1 text-sm shadow-xs">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('peminjaman.index', ['status' => $key]) }}"
                   class="rounded-lg px-3.5 py-1.5 font-semibold transition {{ $status === $key ? 'bg-accent text-white shadow-accent' : 'text-slate-600 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <x-button variant="primary" size="sm" :href="route('peminjaman.create')" data-modal data-modal-title="Catat Peminjaman">
            <x-slot:icon>@svg('heroicon-o-plus', 'h-5 w-5')</x-slot>
            Catat Peminjaman
        </x-button>
    </div>

    {{-- Tabel --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Dokumen</th>
                        <th class="px-5 py-3">Peminjam</th>
                        <th class="px-5 py-3">Tujuan</th>
                        <th class="px-5 py-3">Pinjam</th>
                        <th class="px-5 py-3">Rencana Kembali</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($peminjaman as $p)
                        @php $terlambat = $p->isTerlambat(); @endphp
                        <tr class="transition hover:bg-slate-50/60 {{ $terlambat ? 'bg-red-50/70' : '' }}">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('dokumen.show', $p->dokumen_id) }}" class="font-medium text-foreground hover:text-accent">{{ $p->dokumen?->judul ?? '—' }}</a>
                                <p class="text-xs text-slate-500">{{ $p->dokumen?->nomor_dokumen }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">{{ $p->peminjam_nama ?? $p->peminjam?->nama ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">
                                <div class="max-w-[16rem] truncate" title="{{ $p->tujuan }}">{{ $p->tujuan }}</div>
                            </td>
                            <td class="px-5 py-3.5 tabular-nums text-slate-600">{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3.5 tabular-nums {{ $terlambat ? 'font-medium text-red-600' : 'text-slate-600' }}">
                                {{ $p->tanggal_kembali_rencana?->translatedFormat('d M Y') }}
                                @if ($terlambat)
                                    <span class="ml-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Terlambat</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($p->status === 'dikembalikan')
                                    <x-badge status="dikembalikan" />
                                    <p class="mt-0.5 text-[11px] text-slate-500">{{ $p->tanggal_kembali_aktual?->translatedFormat('d M Y') }}</p>
                                @else
                                    <x-badge status="dipinjam" />
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if ($p->status === 'dipinjam')
                                    <form method="POST" action="{{ route('peminjaman.kembalikan', $p) }}" class="inline"
                                          data-confirm="Tandai dokumen {{ $p->dokumen?->nomor_dokumen }} sudah dikembalikan hari ini?"
                                          data-confirm-btn="Ya, Kembalikan" data-confirm-icon="question">
                                        @csrf @method('PATCH')
                                        <x-button variant="success" size="sm" type="submit">
                                            <x-slot:icon>@svg('heroicon-o-check-circle', 'h-4 w-4')</x-slot>
                                            Kembalikan
                                        </x-button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-trophy" title="Tidak ada data peminjaman"
                                    :desc="'Pada kategori '.($tabs[$status] ?? $status).'.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($peminjaman->hasPages())
        <div>{{ $peminjaman->links() }}</div>
    @endif
</div>
@endsection
