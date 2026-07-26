<nav x-data="{ mobileMenuOpen: false }" class="relative bg-[#045F33] py-4 w-full z-50 shadow-md">
    <div class="container mx-auto px-4 md:px-8">
        <div class="flex justify-between items-center">
            <!-- Logo -->
<a href="/" class="flex items-center gap-3">
    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center p-1 overflow-hidden">
        @if(isset($settings) && $settings->logo_path)
            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="w-full h-full object-contain">
        @else
            <!-- Fallback logo icon -->
            <svg class="w-8 h-8 text-[#045F33]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 12h3v8h14v-8h3L12 2z"/></svg>
        @endif
    </div>
    <span class="ml-2 text-white font-semibold">Nurul Khidmah</span>
</a>


            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-8 text-white text-sm font-medium">
                <a href="{{ route('home') }}" class="transition-colors {{ request()->routeIs('home') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Home</a>
                <a href="{{ route('about') }}" class="transition-colors {{ request()->routeIs('about') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Tentang Kami</a>
                <a href="{{ route('education') }}" class="transition-colors {{ request()->routeIs('education') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Pendidikan</a>
                <a href="{{ route('facilities') }}" class="transition-colors {{ request()->routeIs('facilities') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Fasilitas</a>
                <a href="{{ route('news.index') }}" class="transition-colors {{ request()->routeIs('news.*') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Berita</a>
                <a href="{{ route('registration') }}" class="transition-colors {{ request()->routeIs('registration') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Pendaftaran</a>
                <a href="{{ route('contact') }}" class="transition-colors {{ request()->routeIs('contact') ? 'text-[#F2C94C] border-b-2 border-[#F2C94C] pb-1' : 'hover:text-[#F2C94C]' }}">Kontak</a>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-white focus:outline-none p-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden absolute top-full left-0 w-full bg-[#045F33] shadow-xl border-t border-green-800 z-50">
        <div class="px-6 py-4 flex flex-col space-y-4">
            <a href="{{ route('home') }}" class="font-medium border-b border-green-800 pb-2 {{ request()->routeIs('home') ? 'text-[#F2C94C]' : 'text-white' }}">Home</a>
            <a href="{{ route('about') }}" class="font-medium border-b border-green-800 pb-2 {{ request()->routeIs('about') ? 'text-[#F2C94C]' : 'text-white' }}">Tentang Kami</a>
            <a href="{{ route('education') }}" class="font-medium border-b border-green-800 pb-2 {{ request()->routeIs('education') ? 'text-[#F2C94C]' : 'text-white' }}">Pendidikan</a>
            <a href="{{ route('facilities') }}" class="font-medium border-b border-green-800 pb-2 {{ request()->routeIs('facilities') ? 'text-[#F2C94C]' : 'text-white' }}">Fasilitas</a>
            <a href="{{ route('news.index') }}" class="font-medium border-b border-green-800 pb-2 {{ request()->routeIs('news.*') ? 'text-[#F2C94C]' : 'text-white' }}">Berita</a>
            <a href="{{ route('registration') }}" class="font-medium border-b border-green-800 pb-2 {{ request()->routeIs('registration') ? 'text-[#F2C94C]' : 'text-white' }}">Pendaftaran</a>
            <a href="{{ route('contact') }}" class="font-medium {{ request()->routeIs('contact') ? 'text-[#F2C94C]' : 'text-white' }}">Kontak</a>
        </div>
    </div>
</nav>
