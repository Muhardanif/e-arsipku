@props([
    'status' => null,
])

{{--
    Badge status dokumen untuk Portal Pencarian.
    Konsisten: rounded-full · px-2.5 py-1 · text-xs · font-medium · dot warna.
    Skema warna disamakan dengan <x-badge> panel admin (sumber tunggal, sesuai
    CLAUDE.md): berlaku → hijau brand, kadaluarsa → merah, draf → amber,
    dicabut → abu-abu (netral).
--}}
@php
    $map = [
        'berlaku' => [
            'class' => 'bg-accent-soft text-accent',
            'dot'   => 'bg-accent',
            'label' => 'Berlaku',
        ],
        'kadaluarsa' => [
            'class' => 'bg-red-50 text-red-700',
            'dot'   => 'bg-red-500',
            'label' => 'Kadaluarsa',
        ],
        'draf' => [
            'class' => 'bg-amber-50 text-amber-700',
            'dot'   => 'bg-amber-500',
            'label' => 'Draf',
        ],
        'dicabut' => [
            'class' => 'bg-slate-100 text-slate-600',
            'dot'   => 'bg-slate-400',
            'label' => 'Dicabut',
        ],
    ];

    $s = $map[$status] ?? [
        'class' => 'bg-slate-100 text-slate-600',
        'dot'   => 'bg-slate-400',
        'label' => ucfirst((string) $status),
    ];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {$s['class']}"]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $s['dot'] }}"></span>
    {{ $slot->isNotEmpty() ? $slot : $s['label'] }}
</span>
