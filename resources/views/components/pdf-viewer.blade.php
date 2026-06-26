@props([
    'stream',                 // URL PDF inline (sumber iframe)
    'unduh',                  // URL unduh PDF
    'judul' => 'Pratinjau PDF',
    'label' => 'Lihat PDF',
])

@php $frameId = 'pdfframe-'.Str::random(6); @endphp

<div x-data="{ open: false, loading: true }" class="contents">
    {{-- Tombol pemicu --}}
    <x-button variant="primary" size="sm" type="button" x-on:click="open = true; loading = true" {{ $attributes }}>
        <x-slot:icon>@svg('heroicon-o-printer', 'h-5 w-5')</x-slot>
        {{ $label }}
    </x-button>

    {{-- Modal pratinjau --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
         x-trap.noscroll="open" @keydown.escape.window="open = false">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false" x-transition.opacity></div>

        <div class="relative flex h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-pop"
             x-transition.scale.origin.center>
            {{-- Header modal --}}
            <div class="flex items-center justify-between gap-3 border-b border-border-default px-4 py-3 sm:px-5">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-accent-soft text-accent">
                        @svg('heroicon-o-document-text', 'h-4 w-4')
                    </span>
                    <span class="truncate">{{ $judul }}</span>
                </h3>
                <div class="flex items-center gap-2">
                    <x-button variant="outline" size="sm"
                        x-on:click="document.getElementById('{{ $frameId }}').contentWindow.focus(); document.getElementById('{{ $frameId }}').contentWindow.print();">
                        <x-slot:icon>@svg('heroicon-o-printer', 'h-5 w-5')</x-slot>
                        <span class="hidden sm:inline">Cetak</span>
                    </x-button>
                    <x-button variant="primary" size="sm" :href="$unduh">
                        <x-slot:icon>@svg('heroicon-o-arrow-down-tray', 'h-5 w-5')</x-slot>
                        <span class="hidden sm:inline">Unduh</span>
                    </x-button>
                    <button type="button" @click="open = false" aria-label="Tutup"
                        class="ml-1 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        @svg('heroicon-o-x-mark', 'h-5 w-5')
                    </button>
                </div>
            </div>

            {{-- Body: iframe PDF --}}
            <div class="relative flex-1 bg-slate-100">
                <div x-show="loading" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-slate-50">
                    <svg class="h-8 w-8 animate-spin text-accent" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"></path>
                    </svg>
                    <p class="text-sm text-slate-500">Memuat dokumen…</p>
                </div>
                {{-- src diisi hanya saat modal dibuka agar tidak memuat di latar --}}
                <iframe id="{{ $frameId }}" :src="open ? '{{ $stream }}' : ''" @load="if (open) loading = false"
                        title="{{ $judul }}" class="h-full w-full border-0"></iframe>
            </div>
        </div>
    </div>
</div>
