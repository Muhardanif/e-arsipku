@extends('layouts.app')

@section('title', 'Pengguna')
@section('header', 'Manajemen Pengguna')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Pengguna']]" />
@endsection

@section('content')
@php
    $roleStyle = [
        'admin'   => 'bg-accent-soft text-accent ring-accent/20',
        'petugas' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'staf'    => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    ];
    $roleLabel = ['admin' => 'Administrator', 'petugas' => 'Petugas', 'staf' => 'Staf'];
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            Total <span class="font-medium text-foreground">{{ $pengguna->total() }}</span> pengguna terdaftar.
        </p>
        <x-button variant="primary" size="sm" :href="route('pengguna.create')" data-modal data-modal-title="Tambah Pengguna">
            <x-slot:icon>@svg('heroicon-o-plus', 'h-5 w-5')</x-slot>
            Tambah Pengguna
        </x-button>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('pengguna.index') }}" class="card grid grid-cols-1 gap-3 p-4 sm:grid-cols-4">
        <div class="sm:col-span-2">
            <label class="label">Pencarian</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama atau username…" class="input">
        </div>
        <div>
            <label class="label">Role</label>
            <select name="role" class="input">
                <option value="">Semua</option>
                @foreach ($roleLabel as $v => $l)
                    <option value="{{ $v }}" @selected(($filters['role'] ?? '') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <x-button variant="primary" size="sm" type="submit">Terapkan</x-button>
            <x-button variant="outline" size="sm" :href="route('pengguna.index')">Reset</x-button>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Pengguna</th>
                        <th class="px-5 py-3">Jabatan</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($pengguna as $u)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent to-indigo text-xs font-semibold text-white ring-2 ring-white shadow-xs">
                                        {{ strtoupper(substr($u->nama, 0, 2)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-foreground">{{ $u->nama }}</p>
                                        <p class="text-xs text-slate-500">{{ '@'.$u->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $u->jabatan ?: '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $roleStyle[$u->role] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20' }}">
                                    {{ $roleLabel[$u->role] ?? $u->role }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($u->aktif)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-soft px-2.5 py-1 text-xs font-semibold text-accent ring-1 ring-inset ring-accent/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 ring-1 ring-inset ring-slate-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Toggle aktif --}}
                                    @unless ($u->is(auth()->user()))
                                        <form action="{{ route('pengguna.toggle-aktif', $u) }}" method="POST" class="inline"
                                              data-confirm="{{ $u->aktif ? "Nonaktifkan akun {$u->nama}? Pengguna tidak akan bisa masuk." : "Aktifkan kembali akun {$u->nama}?" }}"
                                              data-confirm-btn="{{ $u->aktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}" data-confirm-icon="question">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="{{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                                @if ($u->aktif)
                                                    @svg('heroicon-o-no-symbol', 'h-5 w-5')
                                                @else
                                                    @svg('heroicon-o-check-circle', 'h-5 w-5')
                                                @endif
                                            </button>
                                        </form>
                                    @endunless
                                    {{-- Reset password --}}
                                    <a href="{{ route('pengguna.reset-password.form', $u) }}" data-modal data-modal-title="Reset Kata Sandi" title="Reset kata sandi"
                                        class="rounded-md p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600">
                                        @svg('heroicon-o-key', 'h-5 w-5')
                                    </a>
                                    {{-- Edit --}}
                                    <a href="{{ route('pengguna.edit', $u) }}" data-modal data-modal-title="Ubah Pengguna" title="Ubah"
                                       class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-accent">
                                        @svg('heroicon-o-pencil', 'h-5 w-5')
                                    </a>
                                    {{-- Hapus --}}
                                    @unless ($u->is(auth()->user()))
                                        <form method="POST" action="{{ route('pengguna.destroy', $u) }}" class="inline"
                                              data-confirm="Hapus pengguna {{ $u->nama }}? Akun akan dihapus permanen."
                                              data-confirm-btn="Ya, Hapus" data-confirm-danger>
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                class="rounded-md p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                                @svg('heroicon-o-trash', 'h-5 w-5')
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14">
                                <x-empty-state icon="heroicon-o-users" title="Tidak ada pengguna ditemukan"
                                    desc="Coba ubah kata kunci atau filter role." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($pengguna->hasPages())
        <div>{{ $pengguna->links() }}</div>
    @endif
</div>
@endsection
