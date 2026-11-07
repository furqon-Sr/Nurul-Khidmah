<nav x-data="{ mobileMenuOpen: false }" class="relative bg-gradient-to-r from-[#045F33] to-[#004524] py-3.5 w-full z-50 shadow-md">
    <div class="container mx-auto px-4 md:px-8">
        <div class="flex justify-between items-center gap-3">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 shrink-0 rounded-full bg-white flex items-center justify-center p-1 overflow-hidden">
                    @if(isset($settings) && $settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="w-full h-full object-contain">
                    @else
                        <svg class="w-7 h-7 text-[#045F33]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 12h3v8h14v-8h3L12 2z"/></svg>
                    @endif
                </div>
                <span class="text-white font-semibold text-sm sm:text-base truncate">Nurul Khidmah</span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-6 xl:space-x-7 text-white text-sm font-medium">
                <a href="{{ route('home') }}" class="transition-colors {{ request()->routeIs('home') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Home</a>
                <a href="{{ route('about') }}" class="transition-colors {{ request()->routeIs('about') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Tentang Kami</a>
                <a href="{{ route('education') }}" class="transition-colors {{ request()->routeIs('education') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Pendidikan</a>
                <a href="{{ route('facilities') }}" class="transition-colors {{ request()->routeIs('facilities') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Fasilitas</a>
                <a href="{{ route('news.index') }}" class="transition-colors {{ request()->routeIs('news.*') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Berita</a>
                <a href="{{ route('registration') }}" class="transition-colors {{ request()->routeIs('registration') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Pendaftaran</a>
                <a href="{{ route('contact') }}" class="transition-colors {{ request()->routeIs('contact') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Kontak</a>

                @auth
                    <a href="{{ auth()->user()->isSantri() ? route('wali.dashboard') : route('dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-[#F2C94C] hover:bg-yellow-300 text-[#045F33] font-bold px-4 py-2 text-xs shadow transition">
                        {{ auth()->user()->isSantri() ? 'Portal Ananda' : 'Panel Sistem' }} →
                    </a>
                @else
                    <a href="{{ route('wali.login') }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-[#F2C94C] hover:bg-yellow-300 text-[#045F33] font-bold px-4 py-2 text-xs shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Portal Wali
                    </a>
                @endauth
            </div>

            <!-- Mobile Right Actions -->
            <div class="flex items-center gap-2 lg:hidden">
                @auth
                    <a href="{{ auth()->user()->isSantri() ? route('wali.dashboard') : route('dashboard') }}"
                       class="min-h-10 inline-flex items-center rounded-full bg-[#F2C94C] text-[#045F33] font-bold px-3.5 py-1.5 text-xs shadow">
                        {{ auth()->user()->isSantri() ? 'Portal Wali' : 'Panel' }}
                    </a>
                @else
                    <a href="{{ route('wali.login') }}"
                       class="min-h-10 inline-flex items-center rounded-full bg-[#F2C94C] text-[#045F33] font-bold px-3.5 py-1.5 text-xs shadow">
                        Portal Wali
                    </a>
                @endauth

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Buka menu navigasi" class="min-h-11 min-w-11 inline-flex items-center justify-center text-white focus:outline-none p-2">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden absolute top-full left-0 w-full bg-gradient-to-b from-[#045F33] to-[#004524] shadow-xl border-t border-green-800 z-50">
        <div class="px-6 py-4 flex flex-col space-y-3">
            <a href="{{ route('home') }}" class="min-h-11 flex items-center font-medium border-b border-green-800 pb-2 {{ request()->routeIs('home') ? 'text-[#F2C94C]' : 'text-white' }}">Home</a>
            <a href="{{ route('about') }}" class="min-h-11 flex items-center font-medium border-b border-green-800 pb-2 {{ request()->routeIs('about') ? 'text-[#F2C94C]' : 'text-white' }}">Tentang Kami</a>
            <a href="{{ route('education') }}" class="min-h-11 flex items-center font-medium border-b border-green-800 pb-2 {{ request()->routeIs('education') ? 'text-[#F2C94C]' : 'text-white' }}">Pendidikan</a>
            <a href="{{ route('facilities') }}" class="min-h-11 flex items-center font-medium border-b border-green-800 pb-2 {{ request()->routeIs('facilities') ? 'text-[#F2C94C]' : 'text-white' }}">Fasilitas</a>
            <a href="{{ route('news.index') }}" class="min-h-11 flex items-center font-medium border-b border-green-800 pb-2 {{ request()->routeIs('news.*') ? 'text-[#F2C94C]' : 'text-white' }}">Berita</a>
            <a href="{{ route('registration') }}" class="min-h-11 flex items-center font-medium border-b border-green-800 pb-2 {{ request()->routeIs('registration') ? 'text-[#F2C94C]' : 'text-white' }}">Pendaftaran</a>
            <a href="{{ route('contact') }}" class="min-h-11 flex items-center font-medium border-b border-green-800 pb-2 {{ request()->routeIs('contact') ? 'text-[#F2C94C]' : 'text-white' }}">Kontak</a>
            <a href="{{ route('wali.login') }}" class="min-h-12 mt-1 flex items-center justify-center rounded-xl bg-[#F2C94C] text-[#045F33] font-bold text-sm shadow">
                Masuk Portal Wali Santri (Cek Nilai &amp; Presensi)
            </a>
        </div>
    </div>
</nav>
