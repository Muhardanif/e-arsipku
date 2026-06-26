@props([
    'items' => [],
])

<ol class="flex flex-wrap items-center gap-1.5">
    <li>
        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Dashboard</a>
    </li>
    @foreach ($items as $item)
        <li class="flex items-center gap-1.5">
            @svg('heroicon-o-chevron-right', 'h-4 w-4 text-slate-300')
            @if (! empty($item['url']) && ! $loop->last)
                <a href="{{ $item['url'] }}" class="text-slate-400 hover:text-slate-600">{{ $item['label'] }}</a>
            @else
                <span class="font-medium text-slate-600">{{ $item['label'] }}</span>
            @endif
        </li>
    @endforeach
</ol>
