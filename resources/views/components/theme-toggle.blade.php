@props([
    'fullWidth' => false,
    'size' => 'md',
])

@php
    $paddingContainer = match($size) {
        'sm' => 'p-0.5',
        'lg' => 'p-1.5',
        default => 'p-1',
    };

    $buttonPad = match($size) {
        'sm' => 'px-3 py-1 text-xs',
        'lg' => 'px-5 py-2 text-sm',
        default => 'px-3.5 py-1.5 text-xs',
    };

    $iconSize = match($size) {
        'sm' => 'w-3.5 h-3.5',
        'lg' => 'w-4.5 h-4.5',
        default => 'w-4 h-4',
    };
@endphp

<div {{ $attributes->merge(['class' => ($fullWidth ? 'w-full grid grid-cols-2' : 'inline-flex items-center') . ' ' . $paddingContainer . ' rounded-full transition-colors duration-200 select-none']) }}
     :class="isDarkMode ? 'bg-[#0f172a] border border-[#334155] shadow-inner' : 'bg-[#eef1f6] border border-[#cbd5e1] shadow-inner'"
     role="group"
     aria-label="Pengaturan Tema Tampilan">

    {{-- TOMBOL LIGHT MODE --}}
    <button type="button"
            @click="typeof setTheme === 'function' ? setTheme(false) : (isDarkMode = false, localStorage.setItem('theme', 'light'), document.documentElement.classList.remove('dark'))"
            :aria-pressed="(!isDarkMode).toString()"
            class="group relative inline-flex items-center justify-center gap-1.5 rounded-full {{ $buttonPad }} transition-all duration-200 cursor-pointer focus:outline-none"
            :class="!isDarkMode
                ? 'bg-white text-[#0f172a] font-bold shadow-[0_2px_8px_rgba(15,23,42,0.10)] ring-1 ring-[#cbd5e1]/80'
                : 'bg-transparent text-[#cbd5e1] font-semibold hover:text-white hover:bg-white/10'">
        {{-- Sun Icon (Filled center circle with 8 dashed rays) --}}
        <svg class="{{ $iconSize }} shrink-0 transition-colors"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2"
             stroke-linecap="round"
             stroke-linejoin="round"
             :class="!isDarkMode ? 'text-[#0f172a]' : 'text-[#cbd5e1] group-hover:text-white'">
            <circle cx="12" cy="12" r="4.5" fill="currentColor"></circle>
            <path d="M12 2v2"></path>
            <path d="M12 20v2"></path>
            <path d="m4.93 4.93 1.41 1.41"></path>
            <path d="m17.66 17.66 1.41 1.41"></path>
            <path d="M2 12h2"></path>
            <path d="M20 12h2"></path>
            <path d="m6.34 17.66-1.41 1.41"></path>
            <path d="m19.07 4.93-1.41 1.41"></path>
        </svg>
        <span>Light</span>
    </button>

    {{-- TOMBOL DARK MODE --}}
    <button type="button"
            @click="typeof setTheme === 'function' ? setTheme(true) : (isDarkMode = true, localStorage.setItem('theme', 'dark'), document.documentElement.classList.add('dark'))"
            :aria-pressed="isDarkMode.toString()"
            class="group relative inline-flex items-center justify-center gap-1.5 rounded-full {{ $buttonPad }} transition-all duration-200 cursor-pointer focus:outline-none"
            :class="isDarkMode
                ? 'bg-[#273449] text-white font-bold shadow-sm ring-1 ring-white/15'
                : 'bg-transparent text-[#334155] font-semibold hover:text-[#0f172a] hover:bg-white/70'">
        {{-- Crescent Moon Icon (Solid fill) --}}
        <svg class="{{ $iconSize }} shrink-0 fill-current transition-colors"
             viewBox="0 0 24 24"
             :class="isDarkMode ? 'text-white' : 'text-[#475569] group-hover:text-[#0f172a]'">
            <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
        <span>Dark</span>
    </button>
</div>
