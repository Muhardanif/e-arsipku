@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('header', 'Log Aktivitas Sistem')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Log Aktivitas']]" />
@endsection

@section('content')
@php
    // Ikon (heroicon) + warna per kata kerja aksi
    $icons = [
        'tambah'     => 'plus',
        'ubah'       => 'pencil',
        'hapus'      => 'trash',
        'unduh'      => 'arrow-down-tray',
        'kembalikan' => 'check-circle',
        'reset'      => 'key',
        'toggle'     => 'no-symbol',
        'cetak'      => 'printer',
    ];
    $tones = [
        'tambah'     => 'bg-accent-soft text-accent',
        'ubah'       => 'bg-blue-100 text-blue-600',
        'hapus'      => 'bg-red-100 text-red-600',
        'unduh'      => 'bg-indigo-100 text-indigo-600',
        'kembalikan' => 'bg-accent-soft text-accent',
        'reset'      => 'bg-amber-100 text-amber-600',
        'toggle'     => 'bg-slate-100 text-slate-600',
        'cetak'      => 'bg-violet-100 text-violet-600',
    ];
    $verbLabel = [
        'tambah' => 'Menambah', 'ubah' => 'Mengubah', 'hapus' => 'Menghapus',
        'unduh' => 'Mengunduh', 'kembalikan' => 'Mengembalikan', 'reset' => 'Mereset',
        'toggle' => 'Mengubah status', 'cetak' => 'Mencetak',
    ];
    $objLabel = [
        'dokumen' => 'dokumen', 'peminjaman' => 'peminjaman', 'kategori' => 'kategori',
        'pengguna' => 'pengguna', 'password' => 'kata sandi', 'laporan' => 'laporan',
    ];
    $deskripsiAksi = function (string $aksi) use ($verbLabel, $objLabel) {
        [$verb, $obj] = array_pad(explode('_', $aksi, 2), 2, '');
        $v = $verbLabel[$verb] ?? ucfirst($verb);
        $o = $objLabel[$obj] ?? str_replace('_', ' ', $obj);
        return trim("$v " . ucwords($o)); // objek di-Title Case: "dokumen" → "Dokumen", "kata sandi" → "Kata Sandi"
    };
@endphp

<div class="space-y-5">
    {{-- Filter --}}
    <form method="GET" action="{{ route('log-aktivitas.index') }}" class="card flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[16rem] flex-[2]">
            <label class="label">Pencarian</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Aksi, detail, atau IP…" class="input">
        </div>
        <div class="min-w-[13rem] flex-1">
            <label class="label">Jenis Aksi</label>
            <select name="aksi" class="input">
                <option value="">Semua</option>
                @foreach ($daftarAksi as $a)
                    <option value="{{ $a }}" @selected(($filters['aksi'] ?? '') === $a)>{{ $deskripsiAksi($a) }}</option>
                @endforeach
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
            <input type="date" name="dari" value="{{ $filters['dari'] ?? '' }}" class="input" placeholder="Pilih tanggal">
        </div>
        <div class="min-w-[10rem] flex-1">
            <label class="label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="input" placeholder="Pilih tanggal">
        </div>
        <div class="flex w-full items-end gap-2">
            <x-button variant="primary" size="sm" type="submit">Terapkan</x-button>
            <x-button variant="outline" size="sm" :href="route('log-aktivitas.index')">Reset</x-button>
        </div>
    </form>

    <p class="text-sm text-slate-500">
        Menampilkan <span class="font-medium text-foreground">{{ $log->total() }}</span> catatan aktivitas.
    </p>

    {{-- Timeline --}}
    <div class="card overflow-hidden">
        @forelse ($log as $item)
            @php
                $verb = explode('_', $item->aksi)[0];
                $icon = $icons[$verb] ?? 'information-circle';
                $tone = $tones[$verb] ?? 'bg-slate-100 text-slate-600';
            @endphp
            <div class="flex items-start gap-4 border-b border-slate-50 px-5 py-4 transition last:border-0 hover:bg-slate-50/60">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tone }}">
                    @svg('heroicon-o-'.$icon, 'h-5 w-5')
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <p class="text-sm font-semibold text-foreground">{{ $deskripsiAksi($item->aksi) }}</p>
                        @if ($item->tabel)
                            <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-500">{{ $item->tabel }}#{{ $item->record_id }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">
                        oleh <span class="font-medium text-slate-700">{{ $item->user?->nama ?? 'Sistem' }}</span>
                        · {{ $item->created_at?->translatedFormat('d M Y, H:i') }}
                        @if ($item->ip_address) · <span class="tabular-nums">{{ $item->ip_address }}</span> @endif
                    </p>
                    @if (! empty($item->detail))
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach ($item->detail as $k => $v)
                                <span class="inline-flex items-center gap-1 rounded-md bg-accent-soft px-2 py-0.5 text-[11px] text-accent ring-1 ring-inset ring-accent/15">
                                    <span class="font-medium">{{ $k }}:</span> {{ is_scalar($v) ? $v : json_encode($v) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-5 py-16">
                <x-empty-state icon="heroicon-o-clock" title="Belum ada aktivitas tercatat"
                    desc="Coba ubah filter pencarian." />
            </div>
        @endforelse
    </div>

    @if ($log->hasPages())
        <div>{{ $log->links() }}</div>
    @endif
</div>
@endsection
