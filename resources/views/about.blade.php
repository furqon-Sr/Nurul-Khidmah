<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Pondok Pesantren Nurul Khidmah</title>
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

    <main class="py-16 bg-white min-h-screen">
        <!-- Rekam Jejak Perjuangan Section -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
            <div class="flex flex-col lg:flex-row gap-12 items-start">
                <div class="w-full lg:w-1/2 space-y-6">
                    <span class="inline-block px-3 py-1 bg-secondary-500 text-xs font-bold text-gray-900 tracking-wide rounded-s
                    
                    m">REKAM JEJAK PERJUANGAN</span>
                    <h1 class="text-3xl md:text-4xl font-bold text-primary-600 leading-snug">
                        {{ $contents['about_title']?->content_text ?? 'Berawal dari Langkah Nyata Merangkul Umat demi Perubahan Spiritual' }}
                    </h1>
                    <div class="text-gray-700 leading-relaxed text-justify text-sm md:text-base space-y-4">
                        {!! $contents['about_text_1']?->content_text ?? "Perjalanan Pondok Pesantren Nurul Khidmah diawali oleh KH Imam Machmudi Al Bantani dengan langkah dakwah secara sederhana, mengajar mengaji anak-anak dan generasi muda. Dorongan kuat untuk mendirikan lembaga formal lahir dari keprihatinan sosial yang mendalam serta komitmen untuk membina moral dan spiritual para remaja di wilayah Lamongan." !!}
                        {!! $contents['about_text_2']?->content_text ?? "Alih-alih menjauh atau menghakimi, beliau memilih metode dakwah yang merangkul langsung ke akar rumput. Para pemuda lokal yang saat itu akrab dengan dunia malam dan minuman keras didekati secara personal, diajak mengikuti istighosah, diajarkan tata cara berwudu, serta dituntun untuk kembali mendirikan salat wajib. Pendekatan humanis dan penuh kesabaran ini membuahkan hasil luar biasa. Para pemuda tersebut bertransformasi menjadi pribadi yang taat and justru berbalik menjadi garda terdepan dalam mendukung pembangunan fisik pesantren." !!}
                    </div>
                </div>
                <div class="w-full lg:w-1/2 flex flex-col">
                    <img src="{{ isset($contents['about_image']) && $contents['about_image']->image_path ? Storage::url($contents['about_image']->image_path) : 'https://images.unsplash.com/photo-1548625361-ec6e408c69fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}" alt="Bangunan Pesantren Nurul Khidmah" class="w-full h-auto rounded-xl shadow-md object-cover" style="max-height: 480px;">
                    <p class="text-[10px] text-gray-400 text-center mt-2 italic">Pembangunan Gedung Utama Pondok Pesantren Nurul Khidmah Lamongan</p>
                </div>
            </div>
            
            <div class="mt-8 text-gray-700 leading-relaxed text-justify text-sm md:text-base">
                {!! $contents['about_history_text']?->content_text ?? "Pada tahun 2009, lahan pertama berhasil dibeli untuk membangun fisik pesantren yang awalnya digunakan sebagai Pondok Putri, sebelum akhirnya berkembang luas hingga memiliki area Pondok Putra. Keberadaan lembaga ini diperkuat secara hukum melalui penerbitan akta notaris dan SK Kemenkumham dari Jakarta pada tahun 2011. Guna memperluas visi mencerdaskan kehidupan anak bangsa dan mengenalkan keluhuran agama, perjuangan difokuskan melalui Pondok Pesantren Nurul Khidmah di Lamongan yang memadukan pendidikan diniyah salafiyah dengan kurikulum formal." !!}
            </div>
        </div>

        <!-- Profil Pengasuh Section -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl mt-24 mb-12">
            <div class="flex flex-col lg:flex-row gap-12 items-start">
                <div class="w-full lg:w-5/12 flex flex-col items-center">
                    <img src="{{ isset($contents['about_kyai_image']) && $contents['about_kyai_image']->image_path ? Storage::url($contents['about_kyai_image']->image_path) : 'https://images.unsplash.com/photo-1579298245158-33e8f568f7d3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}" alt="KH. Imam Machmudi Al-Bantani" class="w-full h-auto rounded-xl shadow-md object-cover mb-8" style="max-height: 480px;">
                    <a href="#" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-primary-600 text-primary-600 text-sm font-semibold rounded-full hover:bg-primary-50 transition-colors w-full max-w-[280px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        Tonton Selengkapnya Di Youtube
                    </a>
                </div>
                <div class="w-full lg:w-7/12 space-y-6 pt-2">
                    <span class="inline-block px-3 py-1 bg-secondary-500 text-xs font-bold text-gray-900 tracking-wide rounded-sm">PROFIL PENGASUH</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-primary-600 leading-tight">
                        {!! $contents['about_kyai_name']?->content_text ?? 'KH. Imam Machmudi<br>Al-Bantani' !!}
                    </h2>
                    <div class="text-gray-700 leading-relaxed text-justify text-sm md:text-base mt-6 space-y-4">
                        {!! $contents['about_kyai_text_1']?->content_text ?? "Lahir di Lamongan pada tahun 1971, KH Imam Machmudi Al Bantani melewati masa kecil dengan perjuangan hidup yang luar biasa. Perjalanan spiritual dan ilmiah beliau ditempa melalui petualangan melintasi berbagai pesantren salaf terkemuka selama belasan tahun. Beliau memulai pendidikan agama di Pondok Pesantren Langitan Tuban pada usia 13 tahun, melanjutkan ke Pondok Pesantren Raudlatul Huda Pasuruan selama 3 tahun, hingga mendalami khazanah syariat Islam di Lombok Nusa Tenggara Barat selama 11 tahun penuh." !!}
                        {!! $contents['about_kyai_text_2']?->content_text ?? "Berbekal kematangan ilmu klasik tersebut, pengaruh dakwah beliau kini telah meluas lintas nusantara hingga menjangkau mancanegara seperti Malaysia dan Thailand. Selain mengasuh para santri, beliau juga menjadi rujukan spiritual yang dihormati oleh berbagai lapisan masyarakat serta kalangan birokrasi dan pejabat negara. Langkah beliau merintis jalur pendidikan formal Nurul Khidmah merupakan wujud dedikasi akhir untuk menghibahkan diri demi mencetak generasi masa depan yang bermanfaat bagi sesama." !!}
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Component -->
    <x-frontend.footer />

</body>
</html>
