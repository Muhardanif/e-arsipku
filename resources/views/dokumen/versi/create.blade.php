@extends('layouts.app')

@section('title', 'Upload Revisi')
@section('header', 'Upload Revisi Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dokumen', 'url' => route('dokumen.index')],
        ['label' => $dokumen->nomor_dokumen, 'url' => route('dokumen.show', $dokumen)],
        ['label' => 'Upload Revisi'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('dokumen.versi.store', $dokumen) }}" enctype="multipart/form-data"
          x-data="{ fileName: '' }" class="mx-auto max-w-3xl space-y-6">
        @csrf

        {{-- Ringkasan dokumen --}}
        <div class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-foreground">{{ $dokumen->judul }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $dokumen->nomor_dokumen }} · {{ $dokumen->kategori?->nama }}</p>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-500">Revisi terkini:</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-500/15">Revisi {{ $dokumen->kodeRevisiTerkini() }}</span>
                    @svg('heroicon-o-arrow-right', 'h-4 w-4 text-slate-300')
                    <span class="inline-flex items-center rounded-full bg-gradient-to-br from-accent to-indigo px-2.5 py-1 text-xs font-semibold text-white shadow-accent">Revisi {{ $revisiBerikutnya }} (baru)</span>
                </div>
            </div>
        </div>

        {{-- Form revisi --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-foreground">Detail Revisi</h3>
            <p class="mb-5 mt-0.5 text-xs text-slate-500">
                Revisi lama tetap tersimpan dan dapat diunduh. Nomor revisi diberikan otomatis.
            </p>

            <div class="space-y-5">
                <div>
                    <label for="tanggal_revisi" class="block text-sm font-medium text-slate-700">
                        Tanggal Revisi <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal_revisi" name="tanggal_revisi" max="{{ date('Y-m-d') }}"
                        value="{{ old('tanggal_revisi', date('Y-m-d')) }}"
                        class="input mt-1.5 {{ $errors->has('tanggal_revisi') ? '!border-red-300 focus:!border-red-400 focus:!ring-red-200' : '' }}">
                    @error('tanggal_revisi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="catatan_revisi" class="block text-sm font-medium text-slate-700">
                        Catatan Revisi <span class="text-red-500">*</span>
                    </label>
                    <textarea id="catatan_revisi" name="catatan_revisi" rows="3"
                        class="input mt-1.5 {{ $errors->has('catatan_revisi') ? '!border-red-300 focus:!border-red-400 focus:!ring-red-200' : '' }}"
                        placeholder="Jelaskan perubahan pada versi ini, mis. pembaruan prosedur bagian 3.">{{ old('catatan_revisi') }}</textarea>
                    @error('catatan_revisi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">
                        Berkas Revisi <span class="text-red-500">*</span>
                    </label>
                    <label for="file"
                        class="mt-1.5 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-6 py-8 text-center transition hover:border-accent hover:bg-accent-soft/40 {{ $errors->has('file') ? 'border-red-300' : 'border-slate-300' }}">
                        @svg('heroicon-o-arrow-up-tray', 'h-8 w-8 text-slate-400')
                        <span class="text-sm text-slate-600"><span class="font-medium text-accent">Klik untuk memilih berkas</span> atau seret ke sini</span>
                        <span class="text-xs text-slate-400" x-text="fileName || 'PDF, JPG, PNG · maks 10 MB'"></span>
                        <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                            @change="fileName = $event.target.files[0]?.name ?? ''">
                    </label>
                    @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-button variant="outline" :href="route('dokumen.show', $dokumen)">Batal</x-button>
            <x-button variant="primary" type="submit">
                <x-slot:icon>@svg('heroicon-o-arrow-up-tray', 'h-5 w-5')</x-slot>
                Unggah Revisi
            </x-button>
        </div>
    </form>
@endsection
