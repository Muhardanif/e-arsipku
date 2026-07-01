@extends('layouts.app')

@section('title', $dokumen->nomor_dokumen)
@section('header', 'Detail Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dokumen', 'url' => route('dokumen.index')],
        ['label' => $dokumen->nomor_dokumen],
    ]" />
@endsection

@section('content')
@php
    $bisaKelola = auth()->user()->isAdmin() || auth()->user()->isPetugas();
    $fmtSize = function ($bytes) {
        if (! $bytes) return '—';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $i), 1).' '.$units[$i];
    };
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-foreground">{{ $dokumen->judul }}</h2>
                <x-badge :status="$dokumen->status" />
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $dokumen->nomor_dokumen }} · {{ $dokumen->kategori?->nama }}</p>
        </div>
        @if ($bisaKelola)
            <div class="flex items-center gap-2">
                @if ($dokumen->isDraf())
                    <x-button variant="primary" size="sm" :href="route('dokumen.terbitkan.form', $dokumen)" data-modal data-modal-title="Terbitkan Dokumen">
                        <x-slot:icon>@svg('heroicon-o-paper-airplane', 'h-5 w-5')</x-slot>
                        Terbitkan
                    </x-button>
                @else
                    <x-button variant="primary" size="sm" :href="route('dokumen.versi.create', $dokumen)" data-modal data-modal-title="Upload Revisi Baru">
                        <x-slot:icon>@svg('heroicon-o-arrow-up-tray', 'h-5 w-5')</x-slot>
                        Upload Revisi
                    </x-button>
                @endif
                @if (! $dokumen->isDraf() && $dokumen->kategori?->perluReview())
                    <x-button variant="outline" size="sm" :href="route('dokumen.review.create', $dokumen)" data-modal data-modal-title="Tandai Sudah Direview">
                        <x-slot:icon>@svg('heroicon-o-check-circle', 'h-5 w-5 text-slate-500')</x-slot>
                        Tandai Direview
                    </x-button>
                @endif
                <x-button variant="outline" size="sm" :href="route('dokumen.edit', $dokumen)" data-modal data-modal-title="Ubah Dokumen">
                    <x-slot:icon>@svg('heroicon-o-pencil', 'h-5 w-5 text-slate-500')</x-slot>
                    Ubah
                </x-button>
                <form method="POST" action="{{ route('dokumen.destroy', $dokumen) }}" class="inline"
                      data-confirm="{{ $dokumen->isDraf() ? 'Batalkan draf ' . $dokumen->nomor_dokumen . '? Draf dihapus permanen dan nomornya dapat dipakai ulang.' : 'Hapus dokumen ' . $dokumen->nomor_dokumen . '? Dokumen akan dipindahkan ke arsip terhapus.' }}"
                      data-confirm-btn="{{ $dokumen->isDraf() ? 'Ya, Batalkan' : 'Ya, Hapus' }}" data-confirm-danger>
                    @csrf
                    @method('DELETE')
                    <x-button variant="danger" size="sm" type="submit">
                        <x-slot:icon>@svg('heroicon-o-trash', 'h-5 w-5')</x-slot>
                        {{ $dokumen->isDraf() ? 'Batalkan Draf' : 'Hapus' }}
                    </x-button>
                </form>
            </div>
        @endif
    </div>

    @if ($dokumen->isDraf())
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5">
            @svg('heroicon-o-clock', 'h-5 w-5 shrink-0 text-amber-600')
            <div class="text-sm text-amber-800">
                <p class="font-semibold">Dokumen ini masih draf.</p>
                <p class="mt-0.5 text-amber-700">Nomor <span class="font-medium">{{ $dokumen->nomor_dokumen }}</span> sudah dipesan. Setelah ditandatangani, gunakan tombol <span class="font-semibold">Terbitkan</span> untuk mengunggah berkas final.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Info dokumen --}}
        <div class="lg:col-span-2 space-y-6">
            <section class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-foreground">Informasi Dokumen</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Nomor Dokumen</dt><dd class="mt-0.5 text-sm font-medium text-foreground">{{ $dokumen->nomor_dokumen }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Kategori</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->kategori?->nama }}</dd></div>
                    @if ($dokumen->klaster)
                        <div><dt class="text-xs text-slate-500">Klaster</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->klaster->kode }} — {{ $dokumen->klaster->nama }}</dd></div>
                    @endif
                    <div><dt class="text-xs text-slate-500">Tanggal Dokumen</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->tanggal_dokumen?->translatedFormat('d F Y') }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Pengesah</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->pengesah ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Tanggal Berlaku</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->tanggal_berlaku?->translatedFormat('d F Y') ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Tanggal Berakhir</dt><dd class="mt-0.5 text-sm text-foreground">{{ $dokumen->tanggal_berakhir?->translatedFormat('d F Y') ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Deskripsi</dt><dd class="mt-0.5 whitespace-pre-line text-sm text-foreground">{{ $dokumen->deskripsi ?: '—' }}</dd></div>
                </dl>
            </section>

            {{-- Riwayat versi --}}
            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-foreground">Riwayat Revisi <span class="text-slate-400">({{ $dokumen->versi->count() }})</span></h3>
                </div>
                <ul class="divide-y divide-slate-50">
                    @forelse ($dokumen->versi as $v)
                        <li class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="flex items-start gap-3">
                                @php $terkini = $v->nomor_versi === $dokumen->versi_terkini; @endphp
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $terkini ? 'bg-gradient-to-br from-accent to-indigo text-white shadow-accent' : 'bg-slate-100 text-slate-600' }}">{{ $v->kodeRevisi() }}</span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-foreground">Revisi {{ $v->kodeRevisi() }}</p>
                                        @if ($terkini)
                                            <span class="inline-flex items-center rounded-full bg-accent-soft px-2 py-0.5 text-[11px] font-semibold text-accent ring-1 ring-inset ring-accent/20">Revisi Terkini</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs font-medium text-slate-600">
                                        @svg('heroicon-o-calendar-days', 'mr-1 inline h-3.5 w-3.5 -translate-y-px text-slate-400')Tanggal revisi: {{ $v->tanggal_revisi?->translatedFormat('d M Y') ?? '—' }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $v->catatan_revisi ?: '—' }}</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">
                                        {{ $fmtSize($v->ukuran_file) }} · {{ $v->pengunggah?->nama ?? '—' }} · diunggah {{ $v->created_at?->translatedFormat('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            @php
                                $ext = strtolower(pathinfo($v->file_path, PATHINFO_EXTENSION));
                                $isGambar = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
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
                                @if ($bisaKelola && $dokumen->versi->count() > 1)
                                    <form method="POST" action="{{ route('dokumen.versi.destroy', $v) }}" class="inline"
                                          data-confirm="Hapus Revisi {{ $v->kodeRevisi() }}? Berkas revisi ini akan dihapus permanen dan tidak dapat dikembalikan."
                                          data-confirm-btn="Ya, Hapus" data-confirm-danger>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus revisi"
                                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">
                                            @svg('heroicon-o-trash', 'h-4 w-4')
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-10 text-center">
                            @svg('heroicon-o-document-arrow-up', 'mx-auto h-9 w-9 text-slate-300')
                            <p class="mt-3 text-sm font-medium text-slate-600">Belum ada berkas</p>
                            <p class="mt-0.5 text-xs text-slate-400">Dokumen masih draf. Unggah berkas final untuk menerbitkan.</p>
                            @if ($bisaKelola && $dokumen->isDraf())
                                <x-button variant="primary" size="sm" class="mt-4" :href="route('dokumen.terbitkan.form', $dokumen)" data-modal data-modal-title="Terbitkan Dokumen">
                                    <x-slot:icon>@svg('heroicon-o-paper-airplane', 'h-5 w-5')</x-slot>
                                    Terbitkan Dokumen
                                </x-button>
                            @endif
                        </li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- Sidebar info --}}
        <div class="space-y-6">
            <section class="card p-6">
                <h3 class="mb-3 text-sm font-semibold text-foreground">Metadata</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Revisi terkini</dt><dd class="font-medium text-foreground">{{ $dokumen->kodeRevisiTerkini() ? 'Revisi ' . $dokumen->kodeRevisiTerkini() : 'Belum ada' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Dibuat oleh</dt><dd class="text-foreground">{{ $dokumen->pembuat?->nama ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Dibuat</dt><dd class="text-foreground">{{ $dokumen->created_at?->translatedFormat('d M Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Diperbarui</dt><dd class="text-foreground">{{ $dokumen->updated_at?->translatedFormat('d M Y') }}</dd></div>
                </dl>
            </section>

            {{-- Review berkala --}}
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-foreground">Review Berkala</h3>
                    @if (! $dokumen->isDraf() && $dokumen->kategori?->perluReview())
                        <x-review-status :dokumen="$dokumen" />
                    @endif
                </div>

                @if (! $dokumen->kategori?->perluReview())
                    <p class="px-6 py-6 text-center text-sm text-slate-500">Kategori ini tidak memerlukan review berkala.</p>
                @elseif ($dokumen->isDraf())
                    <p class="px-6 py-6 text-center text-sm text-slate-500">Dokumen masih draf — review dimulai setelah diterbitkan.</p>
                @else
                    <div class="px-6 py-4">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Periode review</dt><dd class="font-medium text-foreground">{{ $dokumen->kategori->periode_review_tahun }} tahun sekali</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Dihitung dari</dt><dd class="text-foreground">{{ $dokumen->anchorReview()?->translatedFormat('d M Y') }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Jatuh tempo review</dt><dd class="font-semibold text-foreground">{{ $dokumen->jatuhTempoReview()?->translatedFormat('d M Y') ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Terakhir ditinjau</dt><dd class="text-foreground">{{ $dokumen->tanggal_review_terakhir?->translatedFormat('d M Y') ?? 'Belum pernah' }}</dd></div>
                        </dl>
                    </div>

                    @if ($dokumen->review->isNotEmpty())
                        <div class="border-t border-slate-100">
                            <p class="px-6 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Riwayat Tinjauan</p>
                            <ul class="divide-y divide-slate-50">
                                @foreach ($dokumen->review as $r)
                                    <li class="px-6 py-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-medium text-foreground">{{ $r->tanggal_review?->translatedFormat('d M Y') }}</p>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $r->hasil === 'perlu_revisi' ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-accent-soft text-accent ring-accent/20' }}">
                                                {{ $r->hasil === 'perlu_revisi' ? 'Perlu revisi' : 'Sesuai' }}
                                            </span>
                                        </div>
                                        @if ($r->catatan)
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $r->catatan }}</p>
                                        @endif
                                        <p class="mt-0.5 text-[11px] text-slate-400">{{ $r->peninjau?->nama ?? '—' }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </section>

            {{-- Riwayat peminjaman --}}
            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-foreground">Riwayat Peminjaman <span class="text-slate-400">({{ $dokumen->peminjaman->count() }})</span></h3>
                </div>
                @forelse ($dokumen->peminjaman as $p)
                    <div class="border-b border-slate-50 px-6 py-3.5 last:border-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-medium text-foreground">{{ $p->peminjam_nama ?? $p->peminjam?->nama ?? '—' }}</p>
                            <x-badge :status="$p->status" />
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $p->tujuan }}</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $p->tanggal_pinjam?->translatedFormat('d M Y') }} → {{ $p->tanggal_kembali_rencana?->translatedFormat('d M Y') }}
                        </p>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-500">Belum pernah dipinjam.</p>
                @endforelse
            </section>

            {{-- Riwayat akses berkas — siapa melihat/mengunduh (admin & petugas) --}}
            @if ($bisaKelola)
                <section class="card overflow-hidden">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-6 py-4">
                        <h3 class="text-sm font-semibold text-foreground">Riwayat Akses Berkas</h3>
                        @if ($riwayatAkses->isNotEmpty())
                            <a href="{{ route('audit-akses.index', ['q' => $dokumen->nomor_dokumen]) }}"
                               class="text-xs font-medium text-accent hover:underline">Lihat semua</a>
                        @endif
                    </div>
                    @forelse ($riwayatAkses as $akses)
                        @php $unduh = $akses->aksi === 'unduh_dokumen'; @endphp
                        <div class="flex items-center gap-3 border-b border-slate-50 px-6 py-3 last:border-0">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $unduh ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-500' }}">
                                @svg($unduh ? 'heroicon-o-arrow-down-tray' : 'heroicon-o-document-magnifying-glass', 'h-4 w-4')
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-foreground">
                                    <span class="font-medium">{{ $akses->user?->nama ?? 'Sistem' }}</span>
                                    <span class="text-slate-500">{{ $unduh ? 'mengunduh' : 'melihat' }} berkas</span>
                                </p>
                                <p class="text-[11px] text-slate-400">
                                    {{ $akses->created_at?->translatedFormat('d M Y, H:i') }}
                                    @if (! empty($akses->detail['revisi'])) · Rev {{ $akses->detail['revisi'] }} @endif
                                    @if ($akses->ip_address) · <span class="tabular-nums">{{ $akses->ip_address }}</span> @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-slate-500">Belum ada yang mengakses berkas ini.</p>
                    @endforelse
                </section>
            @endif
        </div>
    </div>

</div>
@endsection
