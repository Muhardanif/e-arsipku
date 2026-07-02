@extends('layouts.app')

@section('title', 'Tambah Dokumen')
@section('header', 'Tambah Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dokumen', 'url' => route('dokumen.index')],
        ['label' => 'Tambah'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('dokumen.store') }}" enctype="multipart/form-data"
          x-data="{ fileName: '' }" class="mx-auto max-w-4xl space-y-6">
        @csrf

        <div class="card p-6 sm:p-7">
            @include('dokumen._form', ['dokumen' => null])
        </div>

        <div class="card p-6 sm:p-7">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Berkas Versi Awal</p>

            <div class="mt-3 flex items-start gap-2 rounded-lg bg-amber-50 px-3.5 py-2.5 text-xs text-amber-700 ring-1 ring-inset ring-amber-600/20">
                @svg('heroicon-o-information-circle', 'h-4 w-4 shrink-0')
                <span>Belum ditandatangani? <span class="font-semibold">Kosongkan berkas</span> — dokumen tersimpan sebagai <span class="font-semibold">Draf</span> dengan nomor yang sudah dipesan. Unggah berkas final nanti lewat tombol <span class="font-semibold">Terbitkan</span>.</span>
            </div>

            <div class="mt-4 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Berkas Dokumen <span class="text-slate-400">(opsional)</span></label>
                    <label for="file"
                        class="group mt-1.5 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-8 text-center transition hover:border-accent hover:bg-accent-soft/40 {{ $errors->has('file') ? 'border-red-300' : 'border-slate-300' }}"
                        :class="fileName && '!border-accent bg-accent-soft/40'">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition group-hover:bg-accent-soft group-hover:text-accent"
                              :class="fileName && '!bg-accent-soft !text-accent'">
                            <span x-show="!fileName">@svg('heroicon-o-arrow-up-tray', 'h-6 w-6')</span>
                            <span x-show="fileName" x-cloak>@svg('heroicon-o-document-check', 'h-6 w-6')</span>
                        </span>
                        <span class="text-sm text-slate-600" x-show="!fileName">
                            <span class="font-semibold text-accent">Klik untuk memilih berkas</span> atau seret ke sini
                        </span>
                        <span class="text-sm font-medium text-foreground" x-show="fileName" x-cloak x-text="fileName"></span>
                        <span class="text-xs text-slate-400">PDF, JPG, atau PNG · maksimal 10 MB</span>
                        <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                            @change="fileName = $event.target.files[0]?.name ?? ''">
                    </label>
                    @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                @if ($aiAktif ?? false)
                    <div x-show="fileName" x-cloak
                         x-data="saranMetadata({ url: '{{ route('dokumen.saran-metadata') }}', token: '{{ csrf_token() }}' })"
                         class="rounded-xl border border-accent/20 bg-accent-soft/40 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-start gap-2 text-xs text-slate-600">
                                @svg('heroicon-o-sparkles', 'h-4 w-4 shrink-0 text-accent')
                                <span>Baca berkas dengan AI untuk mengisi <span class="font-medium">field yang masih kosong</span> secara otomatis.</span>
                            </div>
                            <button type="button" @click="jalankan()" :disabled="loading"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="!loading" class="inline-flex items-center gap-1.5">
                                    @svg('heroicon-o-sparkles', 'h-4 w-4')
                                    Isi otomatis dari berkas
                                </span>
                                <span x-show="loading" x-cloak class="inline-flex items-center gap-1.5">
                                    @svg('heroicon-o-arrow-path', 'h-4 w-4 animate-spin')
                                    Membaca dokumen…
                                </span>
                            </button>
                        </div>
                        <p x-show="pesan" x-cloak x-text="pesan"
                           :class="ok ? 'text-emerald-700' : 'text-red-600'"
                           class="mt-2.5 text-xs"></p>
                    </div>
                @endif

                <div>
                    <label for="catatan_revisi" class="block text-sm font-medium text-slate-700">Catatan Versi</label>
                    <input type="text" id="catatan_revisi" name="catatan_revisi" value="{{ old('catatan_revisi') }}"
                        class="input mt-1.5" placeholder="mis. Versi awal dokumen">
                    @error('catatan_revisi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-button variant="outline" :href="route('dokumen.index')">Batal</x-button>
            <x-button variant="primary" type="submit">
                <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
                Simpan Dokumen
            </x-button>
        </div>
    </form>
@endsection
