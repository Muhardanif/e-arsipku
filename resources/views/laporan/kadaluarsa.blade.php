@extends('layouts.app')

@section('title', 'Laporan Dokumen Akan Kadaluarsa')
@section('header', 'Laporan — Dokumen Akan Kadaluarsa')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Laporan', 'url' => route('laporan.index')],
        ['label' => 'Akan Kadaluarsa'],
    ]" />
@endsection

@section('content')
<div class="space-y-5">
    <form method="GET" action="{{ route('laporan.kadaluarsa') }}" class="card flex flex-wrap items-end gap-3 p-4">
        <div>
            <label class="label">Rentang Waktu</label>
            <select name="hari" class="input" onchange="this.form.submit()">
                @foreach ([7 => '7 hari ke depan', 30 => '30 hari ke depan', 60 => '60 hari ke depan', 90 => '90 hari ke depan'] as $v => $l)
                    <option value="{{ $v }}" @selected($hari == $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <p class="flex-1 text-xs text-slate-400">Termasuk dokumen yang sudah melewati tanggal berakhir.</p>
    </form>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Ditemukan <span class="font-medium text-foreground">{{ $dokumen->count() }}</span> dokumen.</p>
        <div class="flex flex-wrap items-center gap-2">
            <x-button variant="outline" size="sm" :href="route('laporan.kadaluarsa', array_merge(request()->query(), ['excel' => 'unduh']))">
                <x-slot:icon>@svg('heroicon-o-table-cells', 'h-5 w-5')</x-slot>
                Export Excel
            </x-button>
            <x-pdf-viewer
                judul="Laporan Dokumen Akan Kadaluarsa"
                :stream="route('laporan.kadaluarsa', array_merge(request()->query(), ['pdf' => 'stream']))"
                :unduh="route('laporan.kadaluarsa', array_merge(request()->query(), ['pdf' => 'unduh']))" />
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nomor & Judul</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Tanggal Berakhir</th>
                        <th class="px-5 py-3">Sisa Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($dokumen as $i => $dok)
                        @php
                            $sisa = now()->startOfDay()->diffInDays($dok->tanggal_berakhir, false);
                            $lewat = $sisa < 0;
                        @endphp
                        <tr class="transition hover:bg-slate-50/60 {{ $lewat ? 'bg-red-50/60' : '' }}">
                            <td class="px-5 py-3.5 tabular-nums text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('dokumen.show', $dok) }}" class="font-medium text-foreground hover:text-accent">{{ $dok->judul }}</a>
                                <p class="text-xs text-slate-500">{{ $dok->nomor_dokumen }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $dok->kategori?->kode }}</td>
                            <td class="px-5 py-3.5 tabular-nums {{ $lewat ? 'font-medium text-red-600' : 'text-slate-600' }}">{{ $dok->tanggal_berakhir?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3.5">
                                @if ($lewat)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Lewat {{ abs((int) $sisa) }} hari
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ (int) $sisa }} hari lagi
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-calendar-days" title="Tidak ada dokumen akan kadaluarsa"
                                    desc="Tidak ada yang jatuh tempo dalam rentang waktu ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
