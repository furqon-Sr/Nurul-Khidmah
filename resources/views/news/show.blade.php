<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $news->title }} - Pondok Pesantren Nurul Khidmah</title>
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
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
            <!-- Back Link -->
            <div class="mb-8">
                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-primary-600 hover:underline font-semibold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Berita
                </a>
            </div>

            <!-- Category / Date -->
            <div class="flex items-center gap-4 mb-6">
                @if($news->category)
                    <span class="inline-block px-3 py-1 bg-green-100 text-xs font-bold text-green-800 tracking-wider rounded-sm uppercase">
                        {{ $news->category }}
                    </span>
                @endif
                <span class="text-xs text-gray-600 font-medium">
                    Dipublikasikan: {{ $news->published_at ? $news->published_at->translatedFormat('d F Y') : $news->created_at->translatedFormat('d F Y') }}
                </span>
            </div>

            <!-- Title -->
            <h1 class="text-3xl md:text-5xl font-bold text-primary-600 mb-8 leading-snug">
                {{ $news->title }}
            </h1>

            <!-- Image -->
            <div class="mb-10">
                @if($news->main_image_path)
                    <img src="{{ Storage::url($news->main_image_path) }}" alt="{{ $news->title }}" class="w-full h-auto rounded-3xl shadow-md object-cover max-h-[500px]">
                @endif
            </div>

            <!-- Content -->
            <div class="prose max-w-none text-gray-700 leading-relaxed text-justify text-sm md:text-base space-y-6">
                {!! nl2br($news->content) !!}
            </div>

            <!-- Share Bar -->
            <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <span class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Bagikan Berita:</span>
                <div class="flex flex-wrap gap-3" x-data="{ copied: false }">
                    <!-- WhatsApp -->
                    <a href="https://api.whatsapp.com/send?text={{ rawurlencode($news->title . ' - ' . url()->current()) }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-[#25D366] text-white hover:bg-[#20ba59] transition text-xs md:text-sm font-bold shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.739-1.456L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.449 5.4 0 9.794-4.394 9.797-9.793.002-2.612-1.013-5.067-2.86-6.918C16.36 2.04 13.9 1.025 11.287 1.025 5.889 1.025 1.5 5.419 1.497 10.816c-.001 1.53.414 3.01 1.205 4.316L1.758 21.05l6.09-1.597c.001-.001.001-.001.002-.001zM17.06 14.382c-.273-.137-1.617-.798-1.868-.89-.25-.091-.432-.137-.614.137-.182.273-.705.89-.864 1.072-.16.182-.319.205-.592.068-1.08-.544-1.782-.962-2.484-2.167-.182-.313-.182-.682.015-.992.179-.281.398-.592.592-.89.182-.273.25-.455.364-.683.114-.227.057-.432-.028-.592-.085-.16-.614-1.62-.864-2.22-.25-.598-.501-.518-.683-.518-.182 0-.387-.023-.592-.023-.205 0-.546.068-.83.387-.285.319-1.093 1.07-1.093 2.607 0 1.537 1.115 3.027 1.274 3.232.16.205 2.195 3.353 5.319 4.7.743.322 1.325.515 1.776.659.747.237 1.428.204 1.968.124.602-.09 1.617-.66 1.844-1.298.227-.637.227-1.184.159-1.298-.068-.115-.25-.183-.523-.319z"/></svg>
                        WhatsApp
                    </a>
                    
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-[#1877F2] text-white hover:bg-[#166fe5] transition text-xs md:text-sm font-bold shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </a>
                    
                    <!-- Twitter (X) -->
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($news->title) }}&url={{ urlencode(url()->current()) }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-[#000000] text-white hover:bg-gray-900 transition text-xs md:text-sm font-bold shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        X (Twitter)
                    </a>

                    <!-- Copy Link -->
                    <button @click="
                        navigator.clipboard.writeText('{{ url()->current() }}');
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                       " 
                       type="button"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition text-xs md:text-sm font-bold shadow-sm relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span x-text="copied ? 'Tersalin!' : 'Salin Link'"></span>
                    </button>
                </div>
            </div>
            
        </div>
    </main>

    <!-- Footer Component -->
    <x-frontend.footer />

</body>
</html>
