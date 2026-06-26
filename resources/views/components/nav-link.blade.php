@props([
    'href' => '#',
    'active' => false,
])

@php
    // Item menu: tinggi 42px, radius 10px, indikator aktif = border-kiri accent (bukan pill).
    // Border transparan saat tidak aktif agar teks tidak bergeser. Hover animasi halus (CSS .sidebar-link).
    $base = 'sidebar-link group flex h-[42px] items-center gap-3 rounded-[10px] border-l-[3px] px-3 text-sm';
    $state = $active
        ? 'border-white bg-white/[0.18] font-semibold text-white'
        : 'border-transparent font-normal text-white/75 hover:bg-white/10 hover:text-white';

    // Icon container kecil — beri kesan workspace, bukan ikon telanjang.
    $iconState = $active
        ? 'bg-white/20 text-white'
        : 'bg-white/10 text-white/65 group-hover:bg-white/15 group-hover:text-white';
@endphp

<a href="{{ $href }}"
   data-sidebar-link data-label="{{ trim($slot) }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->merge(['class' => $base.' '.$state]) }}>
    @isset($icon)
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[9px] transition-colors duration-150 {{ $iconState }}">
            {{ $icon }}
        </span>
    @endisset
    <span class="sidebar-label truncate">{{ $slot }}</span>
</a>
