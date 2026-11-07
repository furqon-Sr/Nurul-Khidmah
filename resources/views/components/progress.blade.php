@props(['value' => 0, 'total' => null, 'label' => 'Selesai'])
@php
    $pct = max(0, min(100, (float) $value));
    $tone = $pct >= 70 ? 'bg-primary-500' : ($pct >= 40 ? 'bg-secondary-500' : 'bg-error-500');
@endphp
{{-- Progress (style guide): bilah + persen tebal + keterangan "n/total Selesai" --}}
<div {{ $attributes->class(['flex items-center gap-3 text-xs']) }}>
    <div class="progress-track max-w-24 flex-1"><div class="progress-fill {{ $tone }}" style="width: {{ $pct }}%"></div></div>
    <span class="font-bold text-gray-900 dark:text-gray-100">{{ rtrim(rtrim(number_format($pct, 1), '0'), '.') }}%</span>
    @if (! is_null($total))
        <span class="text-gray-500 dark:text-gray-400">{{ $slot->isEmpty() ? round($pct / 100 * $total).'/'.$total.' '.$label : $slot }}</span>
    @endif
</div>
