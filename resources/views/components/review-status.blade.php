@props([
    'dokumen',
    'compact' => false,   // true → badge ringkas (untuk tabel/kartu)
])

@php
    $dokumen->loadMissing('kategori');
    $status = $dokumen->statusReview();
    $jatuhTempo = $dokumen->jatuhTempoReview();
    $sisa = $dokumen->sisaHariReview();

    $map = [
        'aman'   => ['cls' => 'bg-accent-soft text-accent ring-accent/20', 'dot' => 'bg-accent', 'label' => 'Review aman'],
        'segera' => ['cls' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'dot' => 'bg-amber-500', 'label' => 'Segera ditinjau'],
        'lewat'  => ['cls' => 'bg-red-50 text-red-700 ring-red-600/20',       'dot' => 'bg-red-500',   'label' => 'Lewat tinjauan'],
    ];
@endphp

@if ($status === null)
    @unless ($compact)
        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 ring-1 ring-inset ring-slate-500/15">
            <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
            Tanpa review berkala
        </span>
    @endunless
@else
    @php $m = $map[$status]; @endphp
    <span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$m['cls']}"]) }}
          @if ($jatuhTempo) title="Jatuh tempo review: {{ $jatuhTempo->translatedFormat('d F Y') }}" @endif>
        <span class="h-1.5 w-1.5 rounded-full {{ $m['dot'] }}"></span>
        @if ($compact)
            {{ $status === 'lewat' ? 'Lewat '.abs($sisa).' hr' : ($status === 'segera' ? $sisa.' hr lagi' : 'Aman') }}
        @else
            {{ $m['label'] }}
            @if ($status === 'lewat')
                · lewat {{ abs($sisa) }} hari
            @elseif ($status === 'segera')
                · {{ $sisa }} hari lagi
            @endif
        @endif
    </span>
@endif
