@extends('layouts.portal')

@section('title', $dokumen->nomor_dokumen ?: $dokumen->judul)

@section('content')
@php
    $fmtSize = function ($bytes) {
        if (! $bytes) return '—';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $i), 1).' '.$units[$i];
    };
    $versiTerkini = $dokumen->versi->firstWhere('nomor_versi', $dokumen->versi_terkini) ?? $dokumen->versi->first();
@endphp

<div class="space-y-6">
    {{-- Kembali --}}
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('portal.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-accent">
        @svg('heroicon-o-arrow-left', 'h-4 w-4')
        Kembali ke pencarian
    </a>

    {{-- Header --}}
    <div class="card p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-accent-soft px-2.5 py-1 text-xs font-semibold text-accent">
                        @svg('heroicon-o-folder', 'h-4 w-4')
                        {{ $dokumen->kategori?->kode }} — {{ $dokumen->kategori?->nama }}
                    </span>
                    <x-badge :status="$dokumen->status" />
                </div>
                <h1 class="mt-3 text-xl font-bold leading-snug text-foreground sm:text-2xl">{{ $dokumen->judul }}</h1>
                <p class="mt-1 font-mono text-sm text-slate-500">{{ $dokumen->nomor_dokumen ?: '—' }}</p>
            </div>

            {{-- Aksi cepat: buka & unduh berkas terkini --}}
            @if ($versiTerkini)
                @php
                    $extT = strtolower(pathinfo($versiTerkini->file_path, PATHINFO_EXTENSION));
                    $isGambarT = in_array($extT, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                @endphp
                <div class="flex shrink-0 items-center gap-2">
                    <x-file-viewer
                        :src="route('dokumen.versi.view', $versiTerkini)"
                        :unduh="route('dokumen.versi.download', $versiTerkini)"
                        :gambar="$isGambarT"
                        judul="{{ $dokumen->nomor_dokumen }} — Rev {{ $versiTerkini->kodeRevisi() }}"
                        variant="primary">
                        @svg('heroicon-o-eye', 'h-5 w-5')
                        Buka Berkas
                    </x-file-viewer>
                    <x-button variant="outline" size="sm" :href="route('dokumen.versi.download', $versiTerkini)">
                        <x-slot:icon>@svg('heroicon-o-arrow-down-tray', 'h-5 w-5 text-slate-500')</x-slot>
                        Unduh
                    </x-button>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Informasi & berkas --}}
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Informasi Dokumen</h2>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Nomor Dokumen</dt><dd class="mt-0.5 text-sm font-medium text-foreground">{{ $dokumen->nomor_dokumen ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Kategori</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->kategori?->nama }}</dd></div>
                    @if ($dokumen->klaster)
                        <div><dt class="text-xs text-slate-500">Klaster</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->klaster->kode }} — {{ $dokumen->klaster->nama }}</dd></div>
                    @endif
                    <div><dt class="text-xs text-slate-500">Tanggal Dokumen</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->tanggal_dokumen?->translatedFormat('d F Y') ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Pengesah</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->pengesah ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Tanggal Berlaku</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->tanggal_berlaku?->translatedFormat('d F Y') ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Tanggal Berakhir</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->tanggal_berakhir?->translatedFormat('d F Y') ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Deskripsi</dt><dd class="mt-0.5 whitespace-pre-line text-sm text-foreground">{{ $dokumen->deskripsi ?: '—' }}</dd></div>
                </dl>
            </section>

            {{-- Daftar berkas / versi --}}
            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-foreground">Berkas Dokumen <span class="text-slate-400">({{ $dokumen->versi->count() }})</span></h2>
                </div>
                <ul class="divide-y divide-slate-50">
                    @forelse ($dokumen->versi as $v)
                        @php
                            $terkini = $v->nomor_versi === $dokumen->versi_terkini;
                            $ext = strtolower(pathinfo($v->file_path, PATHINFO_EXTENSION));
                            $isGambar = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-4 px-6 py-4">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $terkini ? 'bg-gradient-to-br from-accent to-indigo text-white shadow-accent' : 'bg-slate-100 text-slate-600' }}">{{ $v->kodeRevisi() }}</span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-foreground">Revisi {{ $v->kodeRevisi() }}</p>
                                        @if ($terkini)
                                            <span class="inline-flex items-center rounded-full bg-accent-soft px-2 py-0.5 text-[11px] font-semibold text-accent ring-1 ring-inset ring-accent/20">Terkini</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs font-medium text-slate-600">Tanggal revisi: {{ $v->tanggal_revisi?->translatedFormat('d M Y') ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $v->catatan_revisi ?: '—' }}</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">{{ $fmtSize($v->ukuran_file) }} · diunggah {{ $v->created_at?->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-file-viewer
                                    :src="route('dokumen.versi.view', $v)"
                                    :unduh="route('dokumen.versi.download', $v)"
                                    :gambar="$isGambar"
                                    judul="{{ $dokumen->nomor_dokumen }} — Rev {{ $v->kodeRevisi() }}" />
                                <x-button variant="outline" size="sm" :href="route('dokumen.versi.download', $v)">
                                    <x-slot:icon>@svg('heroicon-o-arrow-down-tray', 'h-4 w-4')</x-slot>
                                    Unduh
                                </x-button>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-12">
                            <x-empty-state icon="heroicon-o-document" title="Belum ada berkas" />
                        </li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- Metadata ringkas --}}
        <div class="space-y-6">
            <section class="card p-6">
                <h2 class="mb-3 text-sm font-semibold text-foreground">Ringkasan</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Status</dt><dd><x-badge :status="$dokumen->status" /></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Revisi terkini</dt><dd class="font-medium text-foreground">{{ $dokumen->kodeRevisiTerkini() ? 'Revisi '.$dokumen->kodeRevisiTerkini() : '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Jumlah berkas</dt><dd class="text-foreground">{{ $dokumen->versi->count() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Terakhir diperbarui</dt><dd class="text-foreground">{{ $dokumen->updated_at?->translatedFormat('d M Y') }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
</div>
@endsection
