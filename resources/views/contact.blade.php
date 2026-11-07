<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - Pondok Pesantren Nurul Khidmah</title>
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
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-5xl">
            
            <!-- Title -->
            <div class="text-center mb-16 max-w-2xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold text-primary-600 mb-4">
                    Hubungi Kami
                </h1>
                <p class="text-gray-600 text-sm md:text-base leading-relaxed">
                    Silakan tinggalkan pesan atau pertanyaan Anda. Tim sekretariat Pondok Pesantren Nurul Khidmah akan segera memberikan respons resmi.
                </p>
            </div>

            <!-- Contact Card Container -->
            <div class="bg-white rounded-[32px] shadow-2xl overflow-hidden flex flex-col md:flex-row border border-gray-100 min-h-[580px]">
                
                <!-- Left Pane: Info (Dark Green) -->
                <div class="w-full md:w-5/12 bg-gradient-to-br from-primary-700 to-primary-900 p-8 md:p-12 text-white flex flex-col justify-between">
                    
                    <div class="space-y-8">
                        <!-- Brand/Logo and description -->
                        <div class="flex items-start gap-4">
                            @if(isset($settings) && $settings->logo_path)
                                <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="w-14 h-14 bg-white p-2 rounded-full shadow">
                            @else
                                <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center shadow shrink-0">
                                    <svg class="w-8 h-8 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-xl font-bold tracking-wide">Informasi Kontak</h3>
                                <p class="text-green-200 text-xs mt-1 leading-relaxed">
                                    Hubungi layanan satu pintu sekretariat kami melalui saluran resmi di bawah ini.
                                </p>
                            </div>
                        </div>

                        <!-- Contact channels list -->
                        <div class="space-y-6 pt-6">
                            <!-- Phone -->
                            <div class="flex items-center gap-4">
                                <span class="w-10 h-10 rounded-full bg-emerald-800/40 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </span>
                                <span class="text-sm font-medium">{{ isset($settings) && !empty($settings->whatsapp) ? $settings->whatsapp : '08 54 77 68 98 11' }}</span>
                            </div>

                            <!-- Email -->
                            <div class="flex items-center gap-4">
                                <span class="w-10 h-10 rounded-full bg-emerald-800/40 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <span class="text-sm font-medium">{{ isset($settings) && !empty($settings->email) ? $settings->email : 'Sekretariat@media.ppnk' }}</span>
                            </div>

                            <!-- Address -->
                            <div class="flex items-start gap-4">
                                <span class="w-10 h-10 rounded-full bg-emerald-800/40 flex items-center justify-center shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </span>
                                <span class="text-sm leading-relaxed font-light">{{ isset($settings) && !empty($settings->address) ? $settings->address : 'Wrininganom, Lopang, Kembang Bahu, Lamongan, Jawa Timur' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div class="flex items-center gap-5 pt-8 md:pt-0">
                        <a href="https://instagram.com" target="_blank" class="text-white hover:text-secondary-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="text-white hover:text-secondary-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 00-2.11-2.107C19.528 3.545 12 3.545 12 3.545s-7.528 0-9.388.511a3.002 3.002 0 00-2.11 2.107C0 8.021 0 12 0 12s0 3.979.502 5.837a3.003 3.003 0 002.11 2.107C4.472 20.455 12 20.455 12 20.455s7.528 0 9.388-.511a3.002 3.002 0 002.11-2.107C24 15.979 24 12 24 12s0-3.979-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <a href="https://facebook.com" target="_blank" class="text-white hover:text-secondary-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://tiktok.com" target="_blank" class="text-white hover:text-secondary-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.03 2.61-.01 3.91-.02.08 1.53.63 3.02 1.63 4.18.96.99 2.26 1.66 3.65 1.83v3.9c-1.39-.08-2.76-.55-3.87-1.42-.48-.37-.89-.83-1.23-1.34v7.7c-.01 1.25-.33 2.5-.96 3.58-1.44 2.47-4.22 3.94-7.07 3.63-2.92-.31-5.46-2.39-6.3-5.21-.99-3.23.47-6.9 3.5-8.29 1.41-.64 3.01-.79 4.51-.4v3.9c-.83-.28-1.74-.25-2.54.17-.99.51-1.63 1.54-1.69 2.66-.1 1.71 1.15 3.23 2.85 3.43 1.56.19 3.11-.79 3.56-2.3.13-.42.18-.87.18-1.31V.02z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Right Pane: Message Form (White) -->
                <div class="w-full md:w-7/12 p-8 md:p-12 bg-white flex flex-col justify-center">
                    
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-8">
                        @csrf

                        <!-- Two Column Fields on Desktop -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                            
                            <!-- Nama Lengkap -->
                            <div class="relative">
                                <label for="name" class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap Anda</label>
                                <input type="text" name="name" id="name" required placeholder="|" 
                                       class="border-t-0 border-r-0 border-l-0 border-b border-gray-300 focus:border-primary-600 focus:ring-0 rounded-none px-0 py-2 w-full bg-transparent text-sm placeholder-gray-400">
                            </div>

                            <!-- Nomor WhatsApp -->
                            <div class="relative">
                                <label for="whatsapp" class="block text-xs font-semibold text-gray-600 mb-1">Nomor WhatsApp Wali Santri</label>
                                <input type="text" name="whatsapp" id="whatsapp" required placeholder="+1 012 3456 789" 
                                       class="border-t-0 border-r-0 border-l-0 border-b border-gray-300 focus:border-primary-600 focus:ring-0 rounded-none px-0 py-2 w-full bg-transparent text-sm placeholder-gray-400">
                            </div>

                        </div>

                        <!-- Email Address -->
                        <div class="relative">
                            <label for="email" class="block text-xs font-semibold text-gray-600 mb-1">Alamat Email Aktif</label>
                            <input type="email" name="email" id="email" required placeholder="|" 
                                   class="border-t-0 border-r-0 border-l-0 border-b border-gray-300 focus:border-primary-600 focus:ring-0 rounded-none px-0 py-2 w-full bg-transparent text-sm placeholder-gray-400">
                        </div>

                        <!-- Topic choices -->
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-4">Pilih Topik Pertanyaan</span>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-medium text-gray-700">
                                
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input type="radio" name="topic" value="Pendaftaran Santri Baru atau PPDB" checked
                                           class="w-4 h-4 text-primary-700 border-gray-300 focus:ring-primary-700">
                                    <span>Pendaftaran Santri Baru atau PPDB</span>
                                </label>

                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input type="radio" name="topic" value="Informasi Biaya Pendidikan" 
                                           class="w-4 h-4 text-primary-700 border-gray-300 focus:ring-primary-700">
                                    <span>Informasi Biaya Pendidikan</span>
                                </label>

                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input type="radio" name="topic" value="Kunjungan Kampus Pesantren" 
                                           class="w-4 h-4 text-primary-700 border-gray-300 focus:ring-primary-700">
                                    <span>Kunjungan Kampus Pesantren</span>
                                </label>

                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input type="radio" name="topic" value="Pertanyaan Umum Lainnya" 
                                           class="w-4 h-4 text-primary-700 border-gray-300 focus:ring-primary-700">
                                    <span>Pertanyaan Umum Lainnya</span>
                                </label>

                            </div>
                        </div>

                        <!-- Message Content -->
                        <div class="relative">
                            <label for="message" class="block text-xs font-semibold text-gray-600 mb-1">Isi Pesan Anda</label>
                            <textarea name="message" id="message" required rows="3" placeholder="Tuliskan pertanyaan atau kendala Anda secara detail di sini"
                                      class="border-t-0 border-r-0 border-l-0 border-b border-gray-300 focus:border-primary-600 focus:ring-0 rounded-none px-0 py-2 w-full bg-transparent text-sm placeholder-gray-400 resize-none"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="w-full sm:w-auto px-10 py-3.5 bg-secondary-500 hover:bg-secondary-400 text-gray-900 font-bold text-sm rounded-xl shadow-md transition duration-200">
                                Kirim Pesan Sekarang
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>
    </main>

    <!-- Footer Component -->
    <x-frontend.footer />

</body>
</html>
