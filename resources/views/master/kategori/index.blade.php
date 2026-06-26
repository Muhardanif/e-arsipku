@extends('layouts.app')

@section('title', 'Kategori Dokumen')
@section('header', 'Master Data — Kategori')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Kategori Dokumen']]" />
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            Total <span class="font-medium text-foreground">{{ $kategori->total() }}</span> kategori dokumen.
        </p>
        <x-button variant="primary" size="sm" :href="route('master.kategori.create')" data-modal data-modal-title="Tambah Kategori">
            <x-slot:icon>@svg('heroicon-o-plus', 'h-5 w-5')</x-slot>
            Tambah Kategori
        </x-button>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama Kategori</th>
                        <th class="px-5 py-3">Format Nomor</th>
                        <th class="px-5 py-3 text-center">Review</th>
                        <th class="px-5 py-3 text-center">Dokumen</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($kategori as $k)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-md bg-accent-soft px-2 py-1 text-xs font-bold tracking-wide text-accent ring-1 ring-inset ring-accent/15">{{ $k->kode }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-foreground">{{ $k->nama }}</td>
                            <td class="px-5 py-3.5">
                                @if ($k->format_nomor)
                                    <span class="font-mono text-xs text-slate-600">{{ $k->format_nomor }}</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Manual</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($k->periode_review_tahun)
                                    <span class="inline-flex items-center rounded-md bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-600">tiap {{ $k->periode_review_tahun }} th</span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center tabular-nums text-slate-600">{{ $k->dokumen_count }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('master.kategori.edit', $k) }}" data-modal data-modal-title="Ubah Kategori" title="Ubah"
                                       class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-accent">
                                        @svg('heroicon-o-pencil', 'h-5 w-5')
                                    </a>
                                    <form method="POST" action="{{ route('master.kategori.destroy', $k) }}" class="inline"
                                          data-confirm="Hapus kategori {{ $k->kode }}? Pastikan tidak ada dokumen yang menggunakannya."
                                          data-confirm-btn="Ya, Hapus" data-confirm-danger>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="rounded-md p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                            @svg('heroicon-o-trash', 'h-5 w-5')
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-tag" title="Belum ada kategori"
                                    desc="Tambahkan kategori dokumen pertama Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($kategori->hasPages())
        <div>{{ $kategori->links() }}</div>
    @endif
</div>
@endsection
