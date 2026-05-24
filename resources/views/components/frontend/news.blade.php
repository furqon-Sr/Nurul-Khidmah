<section class="py-20 bg-white">
    <div class="container mx-auto px-4 md:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-[#045F33] text-center mb-12">
            Berita Terbaru
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($news as $item)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden flex flex-col items-center p-4">
                <div class="w-full h-64 md:h-56 lg:h-64 rounded-lg overflow-hidden mb-4 relative">
                    @if($item->main_image_path)
                        <img src="{{ Storage::url($item->main_image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                    @else
                        <!-- Placeholder Image based on screenshot -->
                        <img src="https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?q=80&w=1476&auto=format&fit=crop" alt="Berita" class="w-full h-full object-cover">
                    @endif
                    
                    @if($item->category)
                        <span class="absolute top-4 left-4 z-10 px-3 py-1 bg-green-800/90 backdrop-blur-sm text-white text-[10px] font-bold tracking-wider rounded-full uppercase shadow-sm">
                            {{ $item->category }}
                        </span>
                    @endif
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2 line-clamp-2">
                    {{ $item->title }}
                </h3>
                
                <p class="text-gray-500 text-sm text-center mb-4">
                    {{ \Carbon\Carbon::parse($item->published_at ?? $item->created_at)->format('d M Y') }}
                </p>

                <p class="text-gray-600 text-sm text-center mb-6 line-clamp-3">
                    {{ Str::limit(strip_tags($item->content), 100) }}
                </p>

                <div class="mt-auto pb-4">
                    <a href="{{ route('news.show', $item->slug) }}" class="inline-flex items-center justify-center px-6 py-2 rounded-full bg-[#E8F81D] text-gray-900 font-bold text-sm hover:bg-yellow-400 transition shadow-sm">
                        Selengkapnya
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center text-gray-500 py-12 border border-dashed border-gray-300 rounded-xl">
                Belum ada berita terbaru.
            </div>
            @endforelse
        </div>
    </div>
</section>
