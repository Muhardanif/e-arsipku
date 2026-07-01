@extends('layouts.app')

@section('title', 'Audit Akses')
@section('header', 'Audit Akses Berkas')
@section('subtitle', 'Jejak siapa melihat & mengunduh dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Audit Akses']]" />
@endsection

@section('content')
<div class="space-y-5">
    {{-- Filter --}}
    <form method="GET" action="{{ route('audit-akses.index') }}" class="card flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[16rem] flex-[2]">
            <label class="label">Pencarian</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nomor/judul dokumen, atau IP…" class="input">
        </div>
        <div class="min-w-[10rem] flex-1">
            <label class="label">Jenis Akses</label>
            <select name="jenis" class="input">
                <option value="">Semua</option>
                <option value="lihat" @selected(($filters['jenis'] ?? '') === 'lihat')>Melihat</option>
                <option value="unduh" @selected(($filters['jenis'] ?? '') === 'unduh')>Mengunduh</option>
            </select>
        </div>
        <div class="min-w-[12rem] flex-1">
            <label class="label">Pengguna</label>
            <select name="user_id" class="input">
                <option value="">Semua</option>
                @foreach ($pengguna as $u)
                    <option value="{{ $u->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $u->id)>{{ $u->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[10rem] flex-1">
            <label class="label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $filters['dari'] ?? '' }}" class="input">
        </div>
        <div class="min-w-[10rem] flex-1">
            <label class="label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="input">
        </div>
        <div class="flex w-full items-end gap-2">
            <x-button variant="primary" size="sm" type="submit">Terapkan</x-button>
            <x-button variant="outline" size="sm" :href="route('audit-akses.index')">Reset</x-button>
        </div>
    </form>

    <p class="text-sm text-slate-500">
        Menampilkan <span class="font-medium text-foreground">{{ $log->total() }}</span> catatan akses berkas.
    </p>

    {{-- Tabel jejak akses --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Pengguna</th>
                        <th class="px-5 py-3">Akses</th>
                        <th class="px-5 py-3">Dokumen</th>
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($log as $item)
                        @php
                            $unduh = $item->aksi === 'unduh_dokumen';
                            $detail = $item->detail ?? [];
                        @endphp
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent text-[11px] font-semibold text-white">
                                        {{ strtoupper(substr($item->user?->nama ?? 'S', 0, 2)) }}
                                    </span>
                                    <span class="font-medium text-foreground">{{ $item->user?->nama ?? 'Sistem' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $unduh ? 'bg-indigo-50 text-indigo-600 ring-indigo-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20' }}">
                                    @svg($unduh ? 'heroicon-o-arrow-down-tray' : 'heroicon-o-document-magnifying-glass', 'h-3.5 w-3.5')
                                    {{ $unduh ? 'Mengunduh' : 'Melihat' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-foreground">{{ $detail['judul'] ?? '—' }}</p>
                                <p class="text-xs text-slate-500">
                                    <span class="font-mono">{{ $detail['nomor_dokumen'] ?? '—' }}</span>
                                    @if (! empty($detail['revisi'])) · Rev {{ $detail['revisi'] }} @endif
                                </p>
                            </td>
                            <td class="px-5 py-3.5 tabular-nums text-slate-600">{{ $item->created_at?->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-5 py-3.5 tabular-nums text-slate-500">{{ $item->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16">
                                <x-empty-state icon="heroicon-o-finger-print" title="Belum ada akses berkas tercatat"
                                    desc="Aktivitas melihat & mengunduh berkas akan muncul di sini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($log->hasPages())
        <div>{{ $log->links() }}</div>
    @endif
</div>
@endsection
