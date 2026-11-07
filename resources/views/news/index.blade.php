<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - Pondok Pesantren Nurul Khidmah</title>
    @include('partials.site-favicon')
    @include('partials.fonts')    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Manrope', sans-serif; }
    </style>
    <!-- AlpineJS for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    
    <!-- Navbar Component -->
    <x-frontend.navbar />

    <main class="py-16 md:py-24 bg-white min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
            
            <!-- Featured News (Berita Utama) -->
            @if($mainNews)
                <div class="flex flex-col lg:flex-row gap-12 items-start mb-24">
                    <div class="w-full lg:w-1/2 space-y-6">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-block px-3 py-1 bg-secondary-500 text-xs font-bold text-gray-900 tracking-wide rounded-sm">BERITA UTAMA</span>
                            @if($mainNews->category)
                                <span class="inline-block px-3 py-1 bg-primary-600 text-xs font-bold text-white tracking-wide rounded-sm uppercase">
                                    {{ $mainNews->category }}
                                </span>
                            @endif
                        </div>
                        
                        <p class="text-xs text-gray-600 font-medium">
                            Tanggal Rilis: {{ $mainNews->published_at ? $mainNews->published_at->translatedFormat('d F Y') : $mainNews->created_at->translatedFormat('d F Y') }}
                        </p>
                        
                        <h1 class="text-3xl md:text-4xl font-bold text-primary-600 leading-snug">
                            <a href="{{ route('news.show', $mainNews->slug) }}" class="hover:underline">
                                {{ $mainNews->title }}
                            </a>
                        </h1>
                        
                        <div class="text-gray-700 leading-relaxed text-justify text-sm md:text-base space-y-4">
                            {!! Str::limit(strip_tags($mainNews->content), 450) !!}
                        </div>
                        
                        <div class="pt-4">
                            <a href="{{ route('news.show', $mainNews->slug) }}" class="inline-flex items-center justify-center px-6 py-2 bg-secondary-500 text-gray-900 font-bold text-sm rounded-full hover:bg-secondary-400 transition shadow">
                                Selengkapnya
                            </a>
                        </div>
                    </div>
                    
                    <div class="w-full lg:w-1/2 flex flex-col">
                        @if($mainNews->main_image_path)
                            <img src="{{ Storage::url($mainNews->main_image_path) }}" alt="{{ $mainNews->title }}" class="w-full h-auto rounded-3xl shadow-lg object-cover" style="max-height: 480px;">
                        @else
                            <div class="w-full h-80 rounded-3xl bg-gray-200 flex items-center justify-center text-gray-400 shadow-inner">
                                No Image Available
                            </div>
                        @endif
                        <p class="text-[11px] text-gray-400 text-center mt-3 italic">
                            {{ $mainNews->title }}
                        </p>
                    </div>
                </div>
            @else
                <div class="text-center text-gray-600 py-12">
                    Belum ada berita utama yang dipublikasikan.
                </div>
            @endif

            <!-- News Grid (Berita Lainnya) -->
            <div class="border-t border-gray-100 pt-16">
                <h2 class="text-3xl font-bold text-primary-600 text-center mb-16">Berita Lainnya</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12">
                    @forelse($otherNews as $item)
                        <div class="flex flex-col bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                            <!-- Image Container -->
                            <div class="h-56 relative shrink-0 overflow-hidden rounded-t-3xl">
                                @if($item->main_image_path)
                                    <img src="{{ Storage::url($item->main_image_path) }}?v={{ $item->updated_at->timestamp }}" alt="{{ $item->title }}" class="absolute inset-0 w-full h-full object-cover">
                                @else
                                    <div class="absolute inset-0 bg-gray-200 flex items-center justify-center text-gray-400">
                                        No Image
                                    </div>
                                @endif
                                
                                @if($item->category)
                                    <span class="absolute top-4 left-4 z-10 px-3 py-1 bg-primary-700/90 backdrop-blur-sm text-white text-[10px] font-bold tracking-wider rounded-full uppercase shadow-sm">
                                        {{ $item->category }}
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Content Container -->
                            <div class="p-6 flex flex-col flex-grow text-center items-center">
                                <h3 class="text-primary-600 font-bold text-lg mb-2 line-clamp-2 min-h-[3.5rem]">{{ $item->title }}</h3>
                                
                                <p class="text-gray-400 text-xs mb-4">
                                    {{ $item->published_at ? $item->published_at->translatedFormat('d F Y') : $item->created_at->translatedFormat('d F Y') }}
                                </p>
                                
                                <p class="text-gray-600 text-sm font-light leading-relaxed mb-6 line-clamp-3">
                                    {{ Str::limit(strip_tags($item->content), 120) }}
                                </p>
                                
                                <div class="mt-auto">
                                    <a href="{{ route('news.show', $item->slug) }}" class="inline-flex items-center justify-center px-6 py-2 bg-secondary-500 text-gray-900 font-bold text-xs rounded-full hover:bg-secondary-400 transition shadow-sm">
                                        Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center text-gray-600 py-12">
                            Belum ada berita lainnya.
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>
    </main>

    <!-- Footer Component -->
    <x-frontend.footer />

</body>
</html>
