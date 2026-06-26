@extends('layouts.app')

@section('title', 'Klaster')
@section('header', 'Master Data — Klaster')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Klaster']]" />
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            Total <span class="font-medium text-foreground">{{ $klaster->total() }}</span> klaster.
        </p>
        <x-button variant="primary" size="sm" :href="route('master.klaster.create')" data-modal data-modal-title="Tambah Klaster">
            <x-slot:icon>@svg('heroicon-o-plus', 'h-5 w-5')</x-slot>
            Tambah Klaster
        </x-button>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama Klaster</th>
                        <th class="px-5 py-3 text-center">Dokumen</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($klaster as $k)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-md bg-accent-soft px-2 py-1 text-xs font-bold tracking-wide text-accent ring-1 ring-inset ring-accent/15">{{ $k->kode }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-foreground">{{ $k->nama }}</td>
                            <td class="px-5 py-3.5 text-center tabular-nums text-slate-600">{{ $k->dokumen_count }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('master.klaster.edit', $k) }}" data-modal data-modal-title="Ubah Klaster" title="Ubah"
                                       class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-accent">
                                        @svg('heroicon-o-pencil', 'h-5 w-5')
                                    </a>
                                    <form method="POST" action="{{ route('master.klaster.destroy', $k) }}" class="inline"
                                          data-confirm="Hapus klaster {{ $k->kode }}? Pastikan tidak ada dokumen yang menggunakannya."
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
                            <td colspan="4" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-squares-2x2" title="Belum ada klaster"
                                    desc="Tambahkan klaster pertama Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($klaster->hasPages())
        <div>{{ $klaster->links() }}</div>
    @endif
</div>
@endsection
