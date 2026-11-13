<div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
    <div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex size-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <svg aria-hidden="true" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                {{ $results->count() }} santri
            </span>
        </div>

        <h2 class="text-base font-bold text-gray-900 dark:text-white">Rapor Santri</h2>
        <p class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
            Cari santri berdasarkan nama, NIS, atau kelas untuk pratinjau maupun unduh rapor PDF resmi.
        </p>
    </div>

    <div class="mt-4 pt-3.5 border-t border-gray-100 dark:border-gray-800 space-y-2.5">
        <div>
            <label for="rapor_santri_search_input" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">
                Cari Santri
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z"/>
                    </svg>
                </span>
                <input id="rapor_santri_search_input"
                       type="search"
                       wire:model.live.debounce.200ms="search"
                       placeholder="Ketik nama, NIS, atau NISN..."
                       autocomplete="off"
                       class="w-full h-10 pl-9 pr-14 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-sm sm:text-xs font-medium text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500">
                <div class="absolute inset-y-0 right-2.5 flex items-center gap-1">
                    <svg wire:loading wire:target="search,clearSearch" class="animate-spin size-3.5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    @if($search !== '')
                        <button type="button"
                                wire:click="clearSearch"
                                title="Bersihkan pencarian"
                                aria-label="Bersihkan pencarian"
                                class="rounded px-1.5 py-0.5 text-[11px] font-semibold text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 cursor-pointer">
                            ✕
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Daftar Hasil Pencarian Langsung --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-950/40 max-h-40 overflow-y-auto divide-y divide-gray-200/70 dark:divide-gray-800">
            @forelse($results as $item)
                @php $isSelected = $selectedItem && (int) $selectedItem['id'] === (int) $item['id']; @endphp
                <button type="button"
                        wire:key="rapor-search-item-{{ $item['id'] }}"
                        wire:click="selectSantri({{ $item['id'] }})"
                        class="w-full px-3 py-2 text-left flex items-center justify-between gap-2 transition cursor-pointer {{ $isSelected ? 'bg-primary-50 dark:bg-primary-950/50 text-primary-900 dark:text-primary-100' : 'hover:bg-gray-100/80 dark:hover:bg-gray-800/70 text-gray-800 dark:text-gray-200' }}">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold {{ $isSelected ? 'text-primary-800 dark:text-primary-200' : 'text-gray-900 dark:text-gray-100' }}">
                            {{ $item['nama_lengkap'] }}
                        </p>
                        <p class="truncate text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                            {{ $item['nis'] }} · {{ $item['kelas_nama'] }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $isSelected ? 'bg-primary-600 text-white' : 'bg-gray-200/80 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                        {{ $item['status_label'] }}
                    </span>
                </button>
            @empty
                <div class="px-3 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                    Santri tidak ditemukan untuk kata kunci "{{ $search }}".
                </div>
            @endforelse
        </div>

        {{-- Santri Terpilih & Tombol Aksi --}}
        @if($selectedItem)
            <div class="grid grid-cols-2 gap-2 pt-0.5">
                <a href="{{ $selectedItem['preview_url'] }}"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    <svg aria-hidden="true" class="size-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Pratinjau</span>
                </a>
                <a id="btn-quick-rapor-download"
                   href="{{ $selectedItem['download_url'] }}"
                   title="Download PDF Rapor Santri"
                   aria-label="Unduh / Download PDF Rapor Santri"
                   class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 px-3 text-xs font-semibold text-white shadow-xs transition">
                    <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh PDF</span>
                    <span class="sr-only">Download PDF</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-2 pt-0.5">
                <button type="button" disabled
                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-400 opacity-60 dark:border-gray-700 dark:bg-gray-800 cursor-not-allowed">
                    <span>Pratinjau</span>
                </button>
                <button id="btn-quick-rapor-download" type="button" disabled
                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3 text-xs font-semibold text-white opacity-50 cursor-not-allowed">
                    <span>Unduh PDF</span>
                    <span class="sr-only">Download PDF</span>
                </button>
            </div>
        @endif
    </div>
</div>
