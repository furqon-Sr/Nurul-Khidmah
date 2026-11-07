{{-- Empty state ringkas untuk panel analitik tanpa data --}}
<div class="flex h-48 flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 text-center dark:border-gray-700">
    <span class="mb-3 inline-flex size-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400" aria-hidden="true">
        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14l4-4 3 3 5-6"/></svg>
    </span>
    <p class="max-w-xs px-4 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $text }}</p>
</div>
