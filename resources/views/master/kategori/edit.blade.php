@extends('layouts.app')

@section('title', 'Ubah Kategori')
@section('header', 'Ubah Kategori')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Kategori Dokumen', 'url' => route('master.kategori.index')],
        ['label' => $kategori->kode],
        ['label' => 'Ubah'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('master.kategori.update', $kategori) }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @method('PUT')
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-foreground">Data Kategori</h2>
            <p class="mb-5 mt-0.5 text-xs text-slate-500">Tanda <span class="text-red-500">*</span> wajib diisi.</p>
            @include('master.kategori._form', ['kategori' => $kategori])
        </div>
        <div class="flex items-center justify-end gap-3">
            <x-button variant="outline" :href="route('master.kategori.index')">Batal</x-button>
            <x-button variant="primary" type="submit">
                <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
                Simpan Perubahan
            </x-button>
        </div>
    </form>
@endsection
