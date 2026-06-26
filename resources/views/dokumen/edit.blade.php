@extends('layouts.app')

@section('title', 'Ubah Dokumen')
@section('header', 'Ubah Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dokumen', 'url' => route('dokumen.index')],
        ['label' => $dokumen->nomor_dokumen, 'url' => route('dokumen.show', $dokumen)],
        ['label' => 'Ubah'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('dokumen.update', $dokumen) }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        <div class="card p-6">
            <h2 class="text-sm font-semibold text-foreground">Informasi Dokumen</h2>
            <p class="mb-5 mt-0.5 text-xs text-slate-500">
                Berkas tidak diubah di sini — gunakan menu <span class="font-medium">Upload Revisi</span> pada halaman detail untuk versi baru.
            </p>

            @include('dokumen._form', ['dokumen' => $dokumen])
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-button variant="outline" :href="route('dokumen.show', $dokumen)">Batal</x-button>
            <x-button variant="primary" type="submit">
                <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
                Simpan Perubahan
            </x-button>
        </div>
    </form>
@endsection
