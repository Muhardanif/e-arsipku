@extends('layouts.portal')

@section('title', 'Portal Pencarian Dokumen')

@section('content')
@php $adaFilter = ($filters['q'] ?? '') !== '' || ($filters['kategori_id'] ?? '') !== '' || ($filters['status'] ?? '') !== ''; @endphp

<div class="space-y-8">

    {{-- ── Hero pencarian ──────────────────────────────────────────── --}}
    <section class="pt-2 text-center">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-accent">Portal Arsip Dokumen</p>
        <h1 class="mx-auto mt-2.5 max-w-2xl text-balance text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
            Cari dokumen puskesmas dengan cepat
        </h1>
        <p class="mx-auto mt-2.5 max-w-xl text-pretty text-sm leading-relaxed text-slate-500">
            Telusuri SOP, Surat Keputusan, surat masuk/keluar, dan dokumen akreditasi.
            Cari berdasarkan nomor, judul, hingga isi dokumen, lalu buka berkasnya.
        </p>

        <form method="GET" action="{{ route('portal.index') }}" class="mx-auto mt-6 max-w-2xl">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    @svg('heroicon-o-magnifying-glass', 'h-5 w-5')
                </span>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Cari nomor, judul, atau isi dokumen…" autofocus
                       aria-label="Cari dokumen"
                       class="h-14 w-full rounded-2xl border border-border-strong bg-white pl-12 pr-32 text-base text-foreground shadow-card transition placeholder:text-slate-400 focus:border-accent focus:ring-2 focus:ring-accent/25 focus:outline-none">
                <button type="submit" aria-label="Cari"
                        class="absolute inset-y-0 right-2 my-2 inline-flex items-center gap-1.5 rounded-xl px-4 text-sm font-semibold text-white"
                        style="background-image: linear-gradient(135deg, var(--color-accent-hover) 0%, var(--color-accent) 100%); box-shadow: var(--shadow-accent);">
                    @svg('heroicon-o-magnifying-glass', 'h-4 w-4')
                    <span class="hidden sm:inline">Cari</span>
                </button>
            </div>

            {{-- Filter lanjutan --}}
            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                <label for="filter-kategori" class="sr-only">Saring berdasarkan kategori</label>
                <select id="filter-kategori" name="kategori_id" aria-label="Saring berdasarkan kategori" class="input max-w-[12rem] text-base sm:text-sm">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" @selected(($filters['kategori_id'] ?? '') == $k->id)>{{ $k->kode }} — {{ $k->nama }}</option>
                    @endforeach
                </select>
                <label for="filter-status" class="sr-only">Saring berdasarkan status</label>
                <select id="filter-status" name="status" aria-label="Saring berdasarkan status" class="input max-w-[10rem] text-base sm:text-sm">
                    <option value="">Semua status</option>
                    @foreach (['berlaku' => 'Berlaku', 'kadaluarsa' => 'Kadaluarsa', 'dicabut' => 'Dicabut'] as $v => $l)
                        <option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                @if ($adaFilter)
                    <x-button variant="outline" size="sm" :href="route('portal.index')">
                        <x-slot:icon>@svg('heroicon-o-x-mark', 'h-4 w-4 text-slate-500')</x-slot>
                        Reset
                    </x-button>
                @endif
            </div>
        </form>
    </section>

    {{-- ── Hasil ───────────────────────────────────────────────────── --}}
    <section>
        <h2 class="sr-only">Hasil pencarian dokumen</h2>
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-slate-500">
                @if (($filters['q'] ?? '') !== '')
                    Menampilkan <span class="font-semibold tabular-nums text-foreground">{{ $dokumen->total() }}</span> hasil untuk
                    “<span class="font-medium text-foreground">{{ $filters['q'] }}</span>”
                @elseif ($adaFilter)
                    Menampilkan <span class="font-semibold tabular-nums text-foreground">{{ $dokumen->total() }}</span> hasil tersaring
                @else
                    Menampilkan <span class="font-semibold tabular-nums text-foreground">{{ $dokumen->total() }}</span> dokumen terbaru
                @endif
            </p>
        </div>

        @if ($dokumen->count())
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($dokumen as $dok)
                    @php
                        // Dokumen tidak valid (kadaluarsa/dicabut) diredupkan + diberi
                        // border penanda agar dokumen "berlaku" tampil paling menonjol.
                        $takValid = in_array($dok->status, ['kadaluarsa', 'dicabut'], true);
                        $borderTakValid = match ($dok->status) {
                            'kadaluarsa' => 'border-red-200',
                            'dicabut'    => 'border-amber-200',
                            default      => '',
                        };
                    @endphp
                    <a href="{{ route('portal.show', $dok) }}"
                       class="card group flex flex-col p-5 transition duration-150 hover:-translate-y-0.5 hover:border-accent/30 hover:shadow-[var(--shadow-card-hover)] focus:outline-none focus-visible:-translate-y-0.5 focus-visible:border-accent/40 focus-visible:shadow-[var(--shadow-card-hover)] focus-visible:ring-2 focus-visible:ring-accent/30 motion-reduce:hover:translate-y-0 motion-reduce:focus-visible:translate-y-0 {{ $borderTakValid }}">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-accent-soft px-2.5 py-1 text-xs font-semibold text-accent">
                                @svg('heroicon-o-folder', 'h-4 w-4')
                                {{ $dok->kategori?->kode }}
                            </span>
                            <x-badge :status="$dok->status" />
                        </div>

                        <div class="flex flex-1 flex-col {{ $takValid ? 'opacity-70' : '' }}">
                            <h3 class="mt-3 line-clamp-2 text-[15px] font-semibold leading-snug text-foreground transition group-hover:text-accent-active">
                                {{ $dok->judul }}
                            </h3>
                            <p class="mt-1 font-mono text-xs text-slate-500">{{ $dok->nomor_dokumen ?: '—' }}</p>

                            @if ($dok->deskripsi)
                                <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $dok->deskripsi }}</p>
                            @endif

                            <div class="mt-auto flex items-center gap-3 pt-4 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1">
                                    @svg('heroicon-o-calendar', 'h-4 w-4 text-slate-400')
                                    <span>Terbit:</span>
                                    <span class="tabular-nums text-slate-500">{{ $dok->tanggal_dokumen?->translatedFormat('d M Y') ?: '—' }}</span>
                                </span>
                                <span class="ml-auto inline-flex items-center gap-1 font-semibold text-accent-active">
                                    Buka @svg('heroicon-o-arrow-right', 'h-4 w-4 transition group-hover:translate-x-0.5')
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($dokumen->hasPages())
                <div class="mt-6">{{ $dokumen->links() }}</div>
            @endif
        @else
            <div class="card px-6 py-16">
                <x-empty-state size="lg" icon="heroicon-o-document-magnifying-glass"
                    title="Dokumen tidak ditemukan"
                    desc="Coba periksa ejaan, atau cari dengan nomor dokumen.">
                    @if ($adaFilter)
                        <x-button variant="outline" size="sm" :href="route('portal.index')">
                            <x-slot:icon>@svg('heroicon-o-arrow-path', 'h-4 w-4 text-slate-500')</x-slot>
                            Reset pencarian
                        </x-button>
                    @endif
                </x-empty-state>
            </div>
        @endif
    </section>
</div>
@endsection
