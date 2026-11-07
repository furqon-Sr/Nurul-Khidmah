@props(['label', 'value', 'hint' => null, 'suffix' => null])
{{-- Kartu statistik netral: satu warna aksen (Primary) hanya pada ikon --}}
<div {{ $attributes->class('flex items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900') }}>
    <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ $label }}</p>
        <p class="mt-2 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">
            {{ $value }}@if ($suffix)<span class="ml-1 text-sm font-semibold text-gray-600 dark:text-gray-400">{{ $suffix }}</span>@endif
        </p>
        @if ($hint)
            <p class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $hint }}</p>
        @endif
    </div>
    @isset($icon)
        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-300" aria-hidden="true">
            <span class="size-5 [&>svg]:size-5">{{ $icon }}</span>
        </span>
    @endisset
</div>
