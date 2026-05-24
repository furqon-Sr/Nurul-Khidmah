<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fasilitas - Pondok Pesantren Nurul Khidmah</title>
    @include('partials.site-favicon')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
    <!-- AlpineJS for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    
    <!-- Navbar Component -->
    <x-frontend.navbar />

    <!-- Header Section -->
    <section class="py-16 md:py-24 bg-white">
        <div class="container mx-auto px-4 md:px-8 text-center max-w-4xl">
            <h1 class="text-3xl md:text-5xl font-bold text-[#0D6B3A] mb-6 tracking-tight">Fasilitas</h1>
            <p class="text-gray-600 text-sm md:text-base leading-relaxed">
                Kami menyediakan infrastruktur penunjang pendidikan yang lengkap dan modern guna memastikan kenyamanan serta efektivitas proses belajar mengajar bagi seluruh santri selama berada di lingkungan pesantren.
            </p>
        </div>
    </section>

    <!-- Facilities Grid Section -->
    <section class="pb-24 bg-white">
        <div class="container mx-auto px-4 md:px-8 max-w-6xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                @forelse($facilities as $facility)
                    <div class="flex flex-col sm:flex-row bg-[#045F33] rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-shadow duration-300">
                        <!-- Image Container -->
                        <div class="sm:w-2/5 h-48 sm:h-auto shrink-0 relative">
                            <img src="{{ Storage::url($facility->icon_or_image_path) }}" alt="{{ $facility->title }}" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                        
                        <!-- Content Container -->
                        <div class="sm:w-3/5 p-6 md:p-8 flex flex-col justify-center">
                            <h3 class="text-[#F2C94C] font-bold text-xl mb-3">{{ $facility->title }}</h3>
                            <p class="text-green-50 text-sm font-light leading-relaxed">
                                {{ $facility->description }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 text-center text-gray-500 py-12">
                        Belum ada data fasilitas yang ditambahkan.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Footer Component -->
    <x-frontend.footer />

</body>
</html>
