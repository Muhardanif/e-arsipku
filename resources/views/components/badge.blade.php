@props([
    'status' => null,
])

@php
    $styles = [
        // Status dokumen
        'draf'         => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'berlaku'      => 'bg-accent-soft text-accent ring-accent/20',
        'kadaluarsa'   => 'bg-red-50 text-red-700 ring-red-600/20',
        'dicabut'      => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        // Status peminjaman
        'dipinjam'     => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'dikembalikan' => 'bg-accent-soft text-accent ring-accent/20',
    ];

    $dots = [
        'draf'         => 'bg-amber-500',
        'berlaku'      => 'bg-accent',
        'kadaluarsa'   => 'bg-red-500',
        'dicabut'      => 'bg-slate-400',
        'dipinjam'     => 'bg-amber-500',
        'dikembalikan' => 'bg-accent',
    ];

    $labels = [
        'draf'         => 'Draf',
        'berlaku'      => 'Berlaku',
        'kadaluarsa'   => 'Kadaluarsa',
        'dicabut'      => 'Dicabut',
        'dipinjam'     => 'Dipinjam',
        'dikembalikan' => 'Dikembalikan',
    ];

    $class = $styles[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
    $dot   = $dots[$status] ?? 'bg-slate-400';
    $label = $labels[$status] ?? ucfirst((string) $status);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset $class"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</span>
