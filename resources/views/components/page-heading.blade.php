@props(['title', 'description' => null])
{{-- Page heading minimalis (menggantikan banner besar): judul, deskripsi, aksi di kanan --}}
<div {{ $attributes->class('mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
