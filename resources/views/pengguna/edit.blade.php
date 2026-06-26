@extends('layouts.app')

@section('title', 'Ubah Pengguna')
@section('header', 'Ubah Pengguna')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Pengguna', 'url' => route('pengguna.index')],
        ['label' => $pengguna->nama],
        ['label' => 'Ubah'],
    ]" />
@endsection

@section('content')
    @if ($pengguna->is(auth()->user()))
        <div class="mx-auto mb-5 flex max-w-4xl items-start gap-2.5 rounded-xl border border-accent/20 bg-accent-soft px-4 py-3 text-sm text-foreground">
            @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0 text-accent')
            <span>Anda sedang menyunting akun sendiri — role dan status aktif tidak dapat diubah dari sini.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('pengguna.update', $pengguna) }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-foreground">Data Pengguna</h2>
            <p class="mb-5 mt-0.5 text-xs text-slate-500">Kosongkan kata sandi bila tidak ingin mengubahnya.</p>
            @include('pengguna._form', ['pengguna' => $pengguna])
        </div>
        <div class="flex items-center justify-end gap-3">
            <x-button variant="outline" :href="route('pengguna.index')">Batal</x-button>
            <x-button variant="primary" type="submit">
                <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
                Simpan Perubahan
            </x-button>
        </div>
    </form>
@endsection
