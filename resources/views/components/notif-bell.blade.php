@props([
    'count' => 0,
    'items' => null,
])

@php
    $items = $items ?? collect();

    // Gaya badge per tingkat ambang (selaras palet semantik aplikasi).
    $gayaTingkat = [
        'info'    => ['dot' => 'bg-sky-500',    'badge' => 'bg-sky-50 text-sky-700 ring-sky-600/20',       'label' => 'Mendekati'],
        'warning' => ['dot' => 'bg-amber-500',  'badge' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'label' => 'Peringatan'],
        'kritis'  => ['dot' => 'bg-orange-500', 'badge' => 'bg-orange-50 text-orange-700 ring-orange-600/20','label' => 'Mendesak'],
        'danger'  => ['dot' => 'bg-red-500',    'badge' => 'bg-red-50 text-red-700 ring-red-600/20',       'label' => 'Lewat'],
    ];
@endphp

<div x-data="{ open: false }" class="relative">
    {{-- Tombol bell (styling header tidak diubah) --}}
    <button type="button" @click="open = !open" :aria-expanded="open"
        class="relative w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 transition"
        aria-label="Notifikasi{{ $count > 0 ? ' ('.$count.' belum dibaca)' : '' }}">
        @svg('heroicon-o-bell', 'h-[22px] w-[22px]')
        @if ($count > 0)
            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center ring-2 ring-white">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
        class="absolute right-0 mt-2.5 w-[22rem] max-w-[calc(100vw-2rem)] origin-top-right rounded-lg border border-gray-200 bg-white shadow-pop">

        {{-- Header dropdown --}}
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-foreground">Notifikasi</p>
                <p class="text-xs text-muted">{{ $count > 0 ? $count.' perlu perhatian' : 'Semua terpantau aman' }}</p>
            </div>
            @if ($count > 0)
                <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                    @csrf
                    <button type="submit"
                        class="shrink-0 rounded-md px-2 py-1 text-xs font-medium text-accent transition hover:bg-accent-soft">
                        Tandai semua
                    </button>
                </form>
            @endif
        </div>

        {{-- Daftar --}}
        <div class="max-h-[24rem] overflow-y-auto py-1">
            @forelse ($items as $n)
                @php
                    $g = $gayaTingkat[$n['tingkat']];
                    $isExp = $n['jenis'] === 'kedaluwarsa';
                    $sisaTeks = $n['sisa'] < 0
                        ? 'Lewat '.abs($n['sisa']).' hari'
                        : ($n['sisa'] === 0 ? 'Jatuh tempo hari ini' : $n['sisa'].' hari lagi');
                    $jenisTeks = $isExp ? 'Masa berlaku' : 'Review berkala';
                @endphp
                <div class="group relative flex gap-3 px-4 py-3 transition-colors hover:bg-gray-50">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $isExp ? 'bg-red-50 text-red-600' : 'bg-accent-soft text-accent' }}">
                        @svg($isExp ? 'heroicon-o-calendar-days' : 'heroicon-o-arrow-path', 'h-4 w-4')
                    </span>

                    <a href="{{ route('dokumen.show', $n['dokumen_id']) }}" class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $g['badge'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $g['dot'] }}"></span>{{ $g['label'] }}
                            </span>
                            <span class="text-[11px] font-medium text-muted">{{ $sisaTeks }}</span>
                        </div>
                        <p class="mt-1 truncate text-sm font-medium text-foreground">{{ $n['judul'] }}</p>
                        <p class="truncate text-xs text-muted">{{ $jenisTeks }} · {{ $n['nomor'] }} · {{ $n['acuan']->translatedFormat('d M Y') }}</p>
                    </a>

                    {{-- Tandai sudah dibaca (per item) --}}
                    <form method="POST" action="{{ route('notifikasi.baca') }}" class="shrink-0">
                        @csrf
                        <input type="hidden" name="dokumen_id" value="{{ $n['dokumen_id'] }}">
                        <input type="hidden" name="jenis" value="{{ $n['jenis'] }}">
                        <input type="hidden" name="tanggal_acuan" value="{{ $n['acuan']->toDateString() }}">
                        <input type="hidden" name="tingkat" value="{{ $n['tingkat'] }}">
                        <button type="submit" aria-label="Tandai sudah dibaca" title="Tandai sudah dibaca"
                            class="rounded-md p-1 text-gray-300 transition hover:bg-gray-200 hover:text-gray-600">
                            @svg('heroicon-o-check', 'h-4 w-4')
                        </button>
                    </form>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-accent-soft text-accent">
                        @svg('heroicon-o-check-badge', 'h-6 w-6')
                    </span>
                    <p class="mt-3 text-sm font-medium text-foreground">Tidak ada notifikasi</p>
                    <p class="text-xs text-muted">Semua dokumen terpantau aman.</p>
                </div>
            @endforelse
        </div>

        @if ($items->isNotEmpty())
            <div class="border-t border-gray-100 px-4 py-2.5 text-center">
                <a href="{{ route('laporan.kadaluarsa') }}" class="text-xs font-medium text-accent hover:underline">
                    Lihat laporan kedaluwarsa &amp; review
                </a>
            </div>
        @endif
    </div>
</div>
