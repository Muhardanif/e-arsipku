@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('header', 'Tambah Pengguna')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Pengguna', 'url' => route('pengguna.index')],
        ['label' => 'Tambah'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('pengguna.store') }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-foreground">Data Pengguna</h2>
            <p class="mb-5 mt-0.5 text-xs text-slate-500">Tanda <span class="text-red-500">*</span> wajib diisi.</p>
            @include('pengguna._form', ['pengguna' => null])
        </div>
        <div class="flex items-center justify-end gap-3">
            <x-button variant="outline" :href="route('pengguna.index')">Batal</x-button>
            <x-button variant="primary" type="submit">
                <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
                Simpan Pengguna
            </x-button>
        </div>
    </form>
@endsection
