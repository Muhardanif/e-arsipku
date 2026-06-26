@extends('layouts.app')

@section('title', 'Catat Peminjaman')
@section('header', 'Catat Peminjaman')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Peminjaman', 'url' => route('peminjaman.index')],
        ['label' => 'Catat Baru'],
    ]" />
@endsection

@section('content')
@php
    $inputClass = 'input mt-1.5';
    $ok = '';
    $err = '!border-red-300 focus:!border-red-400 focus:!ring-red-200';
@endphp

<form method="POST" action="{{ route('peminjaman.store') }}"
      x-data="{ mode: '{{ old('peminjam_nama') ? 'manual' : 'pengguna' }}' }"
      class="mx-auto max-w-3xl space-y-6">
    @csrf

    <div class="card p-6">
        <h2 class="mb-5 text-sm font-semibold text-foreground">Data Peminjaman</h2>

        <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
            {{-- Dokumen --}}
            <div class="md:col-span-2">
                <label for="dokumen_id" class="block text-sm font-medium text-slate-700">Dokumen <span class="text-red-500">*</span></label>
                <select id="dokumen_id" name="dokumen_id" class="{{ $inputClass }} {{ $errors->has('dokumen_id') ? $err : $ok }}">
                    <option value="">— Pilih dokumen —</option>
                    @foreach ($dokumen as $d)
                        <option value="{{ $d->id }}" @selected((string) old('dokumen_id', $dokumenTerpilih) === (string) $d->id)>
                            {{ $d->nomor_dokumen }} — {{ $d->judul }}
                        </option>
                    @endforeach
                </select>
                @error('dokumen_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Peminjam: toggle --}}
            <div class="md:col-span-2">
                <span class="block text-sm font-medium text-slate-700">Peminjam <span class="text-red-500">*</span></span>
                <div class="mt-2 inline-flex rounded-lg border border-border-default bg-slate-100 p-1 text-sm">
                    <button type="button" @click="mode = 'pengguna'"
                        :class="mode === 'pengguna' ? 'bg-white text-foreground shadow-xs ring-1 ring-border-default' : 'text-slate-500'"
                        class="rounded-md px-3 py-1.5 font-semibold transition">Dari Pengguna</button>
                    <button type="button" @click="mode = 'manual'"
                        :class="mode === 'manual' ? 'bg-white text-foreground shadow-xs ring-1 ring-border-default' : 'text-slate-500'"
                        class="rounded-md px-3 py-1.5 font-semibold transition">Input Manual</button>
                </div>

                {{-- Pilih pengguna --}}
                <div x-show="mode === 'pengguna'" class="mt-2">
                    <select name="peminjam_id" :disabled="mode !== 'pengguna'"
                        class="{{ $inputClass }} {{ $errors->has('peminjam_id') ? $err : $ok }}">
                        <option value="">— Pilih pengguna —</option>
                        @foreach ($pengguna as $u)
                            <option value="{{ $u->id }}" @selected((string) old('peminjam_id') === (string) $u->id)>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                    @error('peminjam_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Input manual --}}
                <div x-show="mode === 'manual'" x-cloak class="mt-2">
                    <input type="text" name="peminjam_nama" value="{{ old('peminjam_nama') }}" :disabled="mode !== 'manual'"
                        placeholder="Nama peminjam"
                        class="{{ $inputClass }} {{ $errors->has('peminjam_nama') ? $err : $ok }}">
                    @error('peminjam_nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Tujuan --}}
            <div class="md:col-span-2">
                <label for="tujuan" class="block text-sm font-medium text-slate-700">Tujuan Peminjaman <span class="text-red-500">*</span></label>
                <input type="text" id="tujuan" name="tujuan" value="{{ old('tujuan') }}"
                    class="{{ $inputClass }} {{ $errors->has('tujuan') ? $err : $ok }}"
                    placeholder="mis. Keperluan audit internal">
                @error('tujuan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Tanggal pinjam --}}
            <div>
                <label for="tanggal_pinjam" class="block text-sm font-medium text-slate-700">Tanggal Pinjam <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}"
                    class="{{ $inputClass }} {{ $errors->has('tanggal_pinjam') ? $err : $ok }}">
                @error('tanggal_pinjam') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Rencana kembali --}}
            <div>
                <label for="tanggal_kembali_rencana" class="block text-sm font-medium text-slate-700">Rencana Kembali <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" value="{{ old('tanggal_kembali_rencana') }}"
                    class="{{ $inputClass }} {{ $errors->has('tanggal_kembali_rencana') ? $err : $ok }}">
                @error('tanggal_kembali_rencana') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Keterangan --}}
            <div class="md:col-span-2">
                <label for="keterangan" class="block text-sm font-medium text-slate-700">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="2"
                    class="{{ $inputClass }} {{ $errors->has('keterangan') ? $err : $ok }}"
                    placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                @error('keterangan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <x-button variant="outline" :href="route('peminjaman.index')">Batal</x-button>
        <x-button variant="primary" type="submit">
            <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
            Simpan Peminjaman
        </x-button>
    </div>
</form>
@endsection
