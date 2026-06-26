@extends('layouts.app')

@section('title', 'Laporan Daftar Dokumen')
@section('header', 'Laporan — Daftar Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Laporan', 'url' => route('laporan.index')],
        ['label' => 'Daftar Dokumen'],
    ]" />
@endsection

@section('content')
<div class="space-y-5">
    {{-- Filter --}}
    <form method="GET" action="{{ route('laporan.dokumen') }}" class="card grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label class="label">Kategori</label>
            <select name="kategori_id" class="input">
                <option value="">Semua</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k->id }}" @selected(($filters['kategori_id'] ?? '') == $k->id)>{{ $k->kode }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Status</label>
            <select name="status" class="input">
                <option value="">Semua</option>
                @foreach (['draf' => 'Draf', 'berlaku' => 'Berlaku', 'kadaluarsa' => 'Kadaluarsa', 'dicabut' => 'Dicabut'] as $v => $l)
                    <option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $filters['dari'] ?? '' }}" class="input">
        </div>
        <div>
            <label class="label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="input">
        </div>
        <div class="flex items-end gap-2">
            <x-button variant="primary" size="sm" type="submit">Terapkan</x-button>
            <x-button variant="outline" size="sm" :href="route('laporan.dokumen')">Reset</x-button>
        </div>
    </form>

    {{-- Hasil --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Ditemukan <span class="font-medium text-foreground">{{ $dokumen->count() }}</span> dokumen.</p>
        <div class="flex flex-wrap items-center gap-2">
            <x-button variant="outline" size="sm" :href="route('laporan.dokumen', array_merge(request()->query(), ['excel' => 'unduh']))">
                <x-slot:icon>@svg('heroicon-o-table-cells', 'h-5 w-5')</x-slot>
                Export Excel
            </x-button>
            <x-pdf-viewer
                judul="Laporan Daftar Dokumen"
                :stream="route('laporan.dokumen', array_merge(request()->query(), ['pdf' => 'stream']))"
                :unduh="route('laporan.dokumen', array_merge(request()->query(), ['pdf' => 'unduh']))" />
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
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Pengesah</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($dokumen as $i => $dok)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 tabular-nums text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('dokumen.show', $dok) }}" class="font-medium text-foreground hover:text-accent">{{ $dok->judul }}</a>
                                <p class="text-xs text-slate-500">{{ $dok->nomor_dokumen }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $dok->kategori?->kode }}</td>
                            <td class="px-5 py-3.5 tabular-nums text-slate-600">{{ $dok->tanggal_dokumen?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $dok->pengesah ?: '—' }}</td>
                            <td class="px-5 py-3.5"><x-badge :status="$dok->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-document-text" title="Tidak ada dokumen sesuai filter"
                                    desc="Ubah kombinasi kategori, status, atau periode tanggal." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
