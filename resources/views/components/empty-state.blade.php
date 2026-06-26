@props([
    'icon' => 'heroicon-o-inbox',
    'title' => 'Tidak ada data',
    'desc' => null,
    'size' => 'md', // 'md' = di dalam sel tabel/kartu · 'lg' = empty state level-halaman
])

@php
    // Skala tunggal untuk seluruh empty state aplikasi — menjaga konsistensi
    // ikon, judul, dan deskripsi di setiap tabel/daftar.
    $iconSize  = $size === 'lg' ? 'h-14 w-14' : 'h-10 w-10';
    $titleSize = $size === 'lg' ? 'text-base font-semibold' : 'text-sm font-medium';
    $descSize  = $size === 'lg' ? 'text-sm' : 'text-xs';
@endphp

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    @svg($icon, "mx-auto {$iconSize} text-slate-300")
    <p class="mt-3 {{ $titleSize }} text-slate-600">{{ $title }}</p>
    @if (filled($desc))
        <p class="mt-1 {{ $descSize }} text-slate-400">{{ $desc }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
