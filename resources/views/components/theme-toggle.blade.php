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
        'sm' => 'px-2.5 py-1 text-2xs',
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
     :class="isDarkMode ? 'bg-[#101827] border border-slate-800/80 shadow-inner' : 'bg-[#f4f5f7] border border-slate-200/60 shadow-inner'"
     role="group"
     aria-label="Pengaturan Tema Tampilan">

    {{-- TOMBOL LIGHT MODE --}}
    <button type="button"
            @click="typeof setTheme === 'function' ? setTheme(false) : (isDarkMode = false, localStorage.setItem('theme', 'light'), document.documentElement.classList.remove('dark'))"
            :aria-pressed="(!isDarkMode).toString()"
            class="relative inline-flex items-center justify-center gap-2 rounded-full {{ $buttonPad }} font-medium transition-all duration-200 cursor-pointer focus:outline-none"
            :class="!isDarkMode
                ? 'bg-white text-slate-900 font-semibold shadow-[0_2px_8px_rgba(0,0,0,0.08)]'
                : 'bg-transparent text-[#64748b] hover:text-[#94a3b8]'">
        {{-- Sun Icon (Filled center circle with 8 dashed rays) --}}
        <svg class="{{ $iconSize }} shrink-0"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2"
             stroke-linecap="round"
             stroke-linejoin="round"
             :class="!isDarkMode ? 'text-slate-900' : 'text-[#64748b]'">
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
            class="relative inline-flex items-center justify-center gap-2 rounded-full {{ $buttonPad }} font-medium transition-all duration-200 cursor-pointer focus:outline-none"
            :class="isDarkMode
                ? 'bg-[#313a49] text-white font-semibold shadow-sm'
                : 'bg-transparent text-slate-400 hover:text-slate-600'">
        {{-- Crescent Moon Icon (Solid fill) --}}
        <svg class="{{ $iconSize }} shrink-0 fill-current"
             viewBox="0 0 24 24"
             :class="isDarkMode ? 'text-white' : 'text-slate-400'">
            <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
        <span>Dark</span>
    </button>
</div>
