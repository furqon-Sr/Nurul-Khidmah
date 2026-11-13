<nav x-data="{ mobileMenuOpen: false }" class="sticky top-0 bg-white/95 backdrop-blur-md border-b border-gray-200 py-3 w-full z-50">
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

            <!-- Desktop Menu (15px — target navigasi 14–16px) -->
            <div class="hidden lg:flex items-center space-x-6 xl:space-x-7 text-gray-700 text-[15px] leading-relaxed font-semibold">
                <a href="{{ route('home') }}" class="transition-colors {{ request()->routeIs('home') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Home</a>
                <a href="{{ route('about') }}" class="transition-colors {{ request()->routeIs('about') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Tentang Kami</a>

                {{-- Dropdown Preview Program Pendidikan Aktif --}}
                <div class="relative"
                     x-data="{ eduOpen: false }"
                     @click.outside="eduOpen = false"
                     @keydown.escape.window="eduOpen = false">
                    <button type="button"
                            @click="eduOpen = !eduOpen"
                            :aria-expanded="eduOpen.toString()"
                            aria-haspopup="true"
                            class="inline-flex items-center gap-1.5 transition-colors cursor-pointer {{ request()->routeIs('education*') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">
                        <span>Pendidikan</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="eduOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="eduOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-0 mt-3 w-72 rounded-2xl border border-gray-200 bg-white p-2 shadow-xl z-50">
                        <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Pilih Program Pendidikan
                        </div>

                        <div class="space-y-1 max-h-80 overflow-y-auto">
                            @forelse($navPrograms ?? [] as $prog)
                                <a href="{{ route('education.program', $prog->slug) }}"
                                   class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 transition {{ request()->is('pendidikan/' . $prog->slug) ? 'bg-primary-50 text-primary-800' : 'hover:bg-gray-50 text-gray-800' }}">
                                    <div class="min-w-0 flex-1 text-left">
                                        <p class="truncate text-sm font-bold text-gray-900">{{ $prog->name }}</p>
                                        <p class="text-xs font-medium {{ $prog->is_registration_open ? 'text-emerald-700' : ($prog->is_coming_soon ? 'text-amber-700' : 'text-gray-500') }}">
                                            @if($prog->is_registration_open)
                                                Pendaftaran Terbuka
                                            @elseif($prog->is_coming_soon)
                                                Segera Hadir
                                            @else
                                                Unit Pendidikan Aktif
                                            @endif
                                        </p>
                                    </div>
                                    <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @empty
                                <p class="px-3 py-3 text-xs text-gray-500">Belum ada program pendidikan aktif.</p>
                            @endforelse
                        </div>

                        <div class="mt-2 border-t border-gray-100 pt-2">
                            <a href="{{ route('education') }}"
                               class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-bold text-primary-700 hover:bg-primary-50 transition">
                                <span>Semua Program &amp; Biaya</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('facilities') }}" class="transition-colors {{ request()->routeIs('facilities') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Fasilitas</a>
                <a href="{{ route('news.index') }}" class="transition-colors {{ request()->routeIs('news.*') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Berita</a>
                <a href="{{ route('registration') }}" class="transition-colors {{ request()->routeIs('registration') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Pendaftaran</a>
                <a href="{{ route('contact') }}" class="transition-colors {{ request()->routeIs('contact') ? 'text-primary-700 font-bold border-b-2 border-primary-500 pb-1' : 'hover:text-primary-600' }}">Kontak</a>

                @auth
                    <a href="{{ auth()->user()->isSantri() ? route('wali.dashboard') : route('dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-primary-500 hover:bg-primary-600 text-white font-bold px-5 py-2.5 text-sm shadow-sm transition">
                        {{ auth()->user()->isSantri() ? 'Portal Ananda' : 'Panel Sistem' }} →
                    </a>
                @else
                    <a href="{{ route('wali.login') }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-primary-500 hover:bg-primary-600 text-white font-bold px-5 py-2.5 text-sm shadow-sm transition">
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
        <div class="px-6 py-4 flex flex-col space-y-2 text-[15px]">
            <a href="{{ route('home') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('home') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Home</a>
            <a href="{{ route('about') }}" class="min-h-11 flex items-center font-medium border-b border-gray-200 pb-2 {{ request()->routeIs('about') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">Tentang Kami</a>

            <div x-data="{ eduMobileOpen: {{ request()->routeIs('education*') ? 'true' : 'false' }} }" class="border-b border-gray-200 pb-2">
                <button type="button"
                        @click="eduMobileOpen = !eduMobileOpen"
                        :aria-expanded="eduMobileOpen.toString()"
                        class="min-h-11 w-full flex items-center justify-between font-medium {{ request()->routeIs('education*') ? 'text-primary-700 font-bold' : 'text-gray-800' }}">
                    <span>Pendidikan</span>
                    <svg class="w-4 h-4 transition-transform" :class="eduMobileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="eduMobileOpen" x-cloak class="pl-3 mt-1 space-y-1.5">
                    @foreach($navPrograms ?? [] as $prog)
                        <a href="{{ route('education.program', $prog->slug) }}"
                           class="block rounded-lg px-3 py-2 text-sm font-semibold {{ request()->is('pendidikan/' . $prog->slug) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            {{ $prog->name }}
                        </a>
                    @endforeach
                    <a href="{{ route('education') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-bold text-primary-700 hover:bg-primary-50">
                        Semua Program &amp; Biaya &rarr;
                    </a>
                </div>
            </div>

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
