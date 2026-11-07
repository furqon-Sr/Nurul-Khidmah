<nav x-data="{ mobileMenuOpen: false }" class="sticky top-0 bg-white/90 backdrop-blur-md border-b border-gray-200 py-3 w-full z-50">
    <div class="container mx-auto px-4 md:px-8">
        <div class="flex justify-between items-center gap-3">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 shrink-0 rounded-full bg-primary-50 ring-1 ring-primary-100 flex items-center justify-center p-1 overflow-hidden">
                    @if(isset($settings) && $settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="w-full h-full object-contain">
                    @else
                        <svg class="w-7 h-7 text-primary-700" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 12h3v8h14v-8h3L12 2z"/></svg>
                    @endif
                </div>
                <span class="text-gray-900 font-extrabold text-sm sm:text-base truncate">Nurul Khidmah</span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-6 xl:space-x-7 text-gray-700 text-sm font-semibold">
                <a href="{{ route('home') }}" class="transition-colors {{ request()->routeIs('home') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Home</a>
                <a href="{{ route('about') }}" class="transition-colors {{ request()->routeIs('about') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Tentang Kami</a>
                <a href="{{ route('education') }}" class="transition-colors {{ request()->routeIs('education') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Pendidikan</a>
                <a href="{{ route('facilities') }}" class="transition-colors {{ request()->routeIs('facilities') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Fasilitas</a>
                <a href="{{ route('news.index') }}" class="transition-colors {{ request()->routeIs('news.*') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Berita</a>
                <a href="{{ route('registration') }}" class="transition-colors {{ request()->routeIs('registration') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Pendaftaran</a>
                <a href="{{ route('contact') }}" class="transition-colors {{ request()->routeIs('contact') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Kontak</a>

                @auth
                    <a href="{{ auth()->user()->isSantri() ? route('wali.dashboard') : route('dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-primary-500 hover:bg-primary-600 text-white font-bold px-5 py-2.5 text-xs shadow-sm transition">
                        {{ auth()->user()->isSantri() ? 'Portal Ananda' : 'Panel Sistem' }} →
                    </a>
                @else
                    <a href="{{ route('wali.login') }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-primary-500 hover:bg-primary-600 text-white font-bold px-5 py-2.5 text-xs shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Portal Wali
                    </a>
                @endauth
            </div>

            <!-- Mobile Right Actions -->
            <div class="flex items-center gap-2 lg:hidden">
                @auth
                    <a href="{{ auth()->user()->isSantri() ? route('wali.dashboard') : route('dashboard') }}"
                       class="min-h-10 inline-flex items-center rounded-full bg-primary-500 text-white font-bold px-3.5 py-1.5 text-xs shadow-sm">
                        {{ auth()->user()->isSantri() ? 'Portal Wali' : 'Panel' }}
                    </a>
                @else
                    <a href="{{ route('wali.login') }}"
                       class="min-h-10 inline-flex items-center rounded-full bg-primary-500 text-white font-bold px-3.5 py-1.5 text-xs shadow-sm">
                        Portal Wali
                    </a>
                @endauth

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Buka menu navigasi" class="min-h-11 min-w-11 inline-flex items-center justify-center text-gray-800 focus:outline-none p-2">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden absolute top-full left-0 w-full bg-white shadow-xl border-t border-gray-200 z-50">
        <div class="px-6 py-4 flex flex-col space-y-3">
            <a href="{{ route('home') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('home') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Home</a>
            <a href="{{ route('about') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('about') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Tentang Kami</a>
            <a href="{{ route('education') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('education') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Pendidikan</a>
            <a href="{{ route('facilities') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('facilities') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Fasilitas</a>
            <a href="{{ route('news.index') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('news.*') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Berita</a>
            <a href="{{ route('registration') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('registration') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Pendaftaran</a>
            <a href="{{ route('contact') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('contact') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Kontak</a>
            <a href="{{ route('wali.login') }}" class="min-h-12 mt-1 flex items-center justify-center rounded-xl bg-primary-500 text-white font-bold text-sm shadow-sm">
                Masuk Portal Wali Santri (Cek Nilai &amp; Presensi)
            </a>
        </div>
    </div>
</nav>
