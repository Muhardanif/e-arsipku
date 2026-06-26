@props([
    'variant' => 'primary',  // primary | secondary | danger | success | ghost | outline
    'size' => 'md',          // sm | md | lg
    'type' => 'button',      // type tombol (diabaikan bila ada href → render <a>)
    'href' => null,          // bila diisi → render <a>, selain itu <button>
    'loading' => false,      // tampilkan spinner + nonaktif
    'disabled' => false,     // nonaktif
])

@php
    // ══ Sistem tombol tunggal E-ARSIPKU ══════════════════════════════════
    // Catatan brand: variant `primary` & `success` memakai BRAND hijau
    // #009177 (token --color-accent), BUKAN green-600 Tailwind, agar selaras
    // dengan sidebar, halaman login, dan identitas tunggal aplikasi.
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary'   => 'bg-accent text-white shadow-accent hover:bg-accent-hover active:bg-accent-active focus-visible:ring-accent/40',
        'success'   => 'bg-accent text-white hover:bg-accent-hover active:bg-accent-active focus-visible:ring-accent/40',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus-visible:ring-slate-400/50',
        'outline'   => 'border border-border-strong bg-surface-raised text-foreground shadow-xs hover:bg-surface focus-visible:ring-accent/30',
        'ghost'     => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-slate-400/40',
        'danger'    => 'bg-red-600 text-white shadow-xs hover:bg-red-700 active:bg-red-800 focus-visible:ring-red-500/40',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $classes = $base
        .' '.($variants[$variant] ?? $variants['primary'])
        .' '.($sizes[$size] ?? $sizes['md']);

    $isLink = filled($href);
    $isDisabled = $disabled || $loading;
@endphp

@if ($isLink)
    <a href="{{ $isDisabled ? '#' : $href }}"
       @if ($isDisabled) aria-disabled="true" tabindex="-1" @endif
       {{ $attributes->merge(['class' => $classes.($isDisabled ? ' pointer-events-none opacity-50' : '')]) }}>
        @if ($loading)
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
        @elseif (isset($icon))
            {{ $icon }}
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($isDisabled)
        {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
        @elseif (isset($icon))
            {{ $icon }}
        @endif
        {{ $slot }}
    </button>
@endif
