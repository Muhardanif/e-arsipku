@extends('layouts.app')

@section('title', 'Tambah Klaster')
@section('header', 'Tambah Klaster')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Klaster', 'url' => route('master.klaster.index')],
        ['label' => 'Tambah'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('master.klaster.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-foreground">Data Klaster</h2>
            <p class="mb-5 mt-0.5 text-xs text-slate-500">Tanda <span class="text-red-500">*</span> wajib diisi.</p>
            @include('master.klaster._form', ['klaster' => null])
        </div>
        <div class="flex items-center justify-end gap-3">
            <x-button variant="outline" :href="route('master.klaster.index')">Batal</x-button>
            <x-button variant="primary" type="submit">
                <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
                Simpan Klaster
            </x-button>
        </div>
    </form>
@endsection
