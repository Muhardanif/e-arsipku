@extends('layouts.app')

@section('title', 'Dokumen')
@section('header', 'Manajemen Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dokumen']]" />
@endsection

@section('content')
@php $bisaKelola = auth()->user()->bolehMenu('dokumen-kelola'); @endphp

<div class="space-y-5">

    {{-- Header actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            Menampilkan <span class="font-medium tabular-nums text-foreground">{{ $dokumen->total() }}</span> dokumen.
        </p>
        @if ($bisaKelola)
            <x-button variant="primary" size="sm" :href="route('dokumen.create')" data-modal data-modal-title="Tambah Dokumen">
                <x-slot:icon>@svg('heroicon-o-plus', 'h-5 w-5')</x-slot>
                Tambah Dokumen
            </x-button>
        @endif
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('dokumen.index') }}"
          class="card grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6">
        <div class="lg:col-span-2">
            <label class="label">Pencarian</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nomor, judul, atau isi dokumen…" class="input">
        </div>
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
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-6">
            <x-button variant="primary" size="sm" type="submit">
                <x-slot:icon>@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</x-slot>
                Terapkan
            </x-button>
            <x-button variant="outline" size="sm" :href="route('dokumen.index')">Reset</x-button>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Nomor & Judul</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($dokumen as $dok)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 align-top">
                                <a href="{{ route('dokumen.show', $dok) }}" class="font-medium text-foreground hover:text-accent">{{ $dok->judul }}</a>
                                <p class="text-xs text-slate-500">{{ $dok->nomor_dokumen }}</p>
                            </td>
                            <td class="px-5 py-3.5 align-top text-slate-600">
                                <span title="{{ $dok->kategori?->nama }}">{{ $dok->kategori?->kode }}</span>
                            </td>
                            <td class="px-5 py-3.5 align-top tabular-nums text-slate-600">{{ $dok->tanggal_dokumen?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3.5 align-top">
                                <x-badge :status="$dok->status" />
                                @if (in_array($dok->statusReview(), ['segera', 'lewat'], true))
                                    <div class="mt-1"><x-review-status :dokumen="$dok" compact /></div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 align-top">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('dokumen.show', $dok) }}" title="Lihat"
                                       class="rounded-md p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                        @svg('heroicon-o-document-magnifying-glass', 'h-5 w-5')
                                    </a>
                                    @if ($bisaKelola)
                                        <a href="{{ route('dokumen.edit', $dok) }}" data-modal data-modal-title="Ubah Dokumen" title="Ubah"
                                           class="rounded-md p-2 text-slate-400 transition hover:bg-slate-100 hover:text-accent">
                                            @svg('heroicon-o-pencil', 'h-5 w-5')
                                        </a>
                                        <form method="POST" action="{{ route('dokumen.destroy', $dok) }}" class="inline"
                                              data-confirm="Hapus dokumen {{ $dok->nomor_dokumen }}? Dokumen akan dipindahkan ke arsip terhapus dan dapat dipulihkan administrator."
                                              data-confirm-btn="Ya, Hapus" data-confirm-danger>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                class="rounded-md p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                                @svg('heroicon-o-trash', 'h-5 w-5')
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-document" title="Tidak ada dokumen ditemukan"
                                    desc="Coba ubah kata kunci atau filter pencarian." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($dokumen->hasPages())
        <div>{{ $dokumen->links() }}</div>
    @endif
</div>
@endsection
