<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendidikan - Pondok Pesantren Nurul Khidmah</title>
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

    <main class="py-16 lg:py-24 bg-white min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl space-y-24 lg:space-y-32">

            {{-- ====================================================
                 SECTION DINAMIS: Program dari database
            ====================================================== --}}
            @if(isset($programs) && $programs->count() > 0)
                @foreach($programs as $i => $program)
                    <div class="flex flex-col-reverse {{ $loop->even ? 'lg:flex-row-reverse' : 'lg:flex-row' }} gap-12 lg:gap-20 items-center">
                        <div class="w-full lg:w-1/2 space-y-6">
                            {{-- Badge status --}}
                            @if($program->is_registration_open)
                                <span class="inline-block px-3 py-1 bg-green-100 text-[10px] sm:text-xs font-bold text-green-800 tracking-wider rounded-sm">
                                    ✅ PENDAFTARAN TERBUKA
                                </span>
                            @elseif(!$program->is_coming_soon)
                                <span class="inline-block px-3 py-1 bg-gray-100 text-[10px] sm:text-xs font-bold text-gray-500 tracking-wider rounded-sm">
                                    UNIT AKTIF
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 bg-amber-100 text-[10px] sm:text-xs font-bold text-amber-800 tracking-wider rounded-sm">
                                    🚧 TAHAP PERENCANAAN
                                </span>
                            @endif

                            <h2 class="text-3xl md:text-4xl font-bold text-[#0D6B3A] leading-snug">
                                {{ $program->name }}
                            </h2>

                            @if($program->description)
                                <p class="text-gray-700 leading-relaxed text-justify text-sm md:text-base">
                                    {{ $program->description }}
                                </p>
                            @endif

                            {{-- CTA Daftar --}}
                            @if($program->is_registration_open)
                                <a href="{{ route('register.create', $program->slug) }}"
                                   class="inline-flex items-center gap-2 justify-center px-6 py-2.5 bg-[#0D6B3A] text-white text-sm font-bold rounded-full hover:bg-green-800 transition shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Mulai Pendaftaran Unit Ini →
                                </a>
                            @elseif(!$program->is_coming_soon)
                                <button disabled class="inline-flex items-center gap-2 justify-center px-6 py-2.5 bg-gray-100 text-gray-400 text-sm font-bold rounded-full cursor-not-allowed border border-gray-200">
                                    Pendaftaran Ditutup
                                </button>
                            @else
                                <button disabled class="inline-flex items-center gap-2 justify-center px-6 py-2.5 bg-amber-50 text-amber-600 text-sm font-bold rounded-full cursor-not-allowed border border-amber-200">
                                    🚧 Rencana Pembangunan / Akan Segera Hadir
                                </button>
                            @endif
                        </div>

                        <div class="w-full lg:w-1/2">
                            @if($program->image_path)
                                <img src="{{ asset('storage/' . $program->image_path) }}"
                                     alt="{{ $program->name }}"
                                     class="w-full h-auto rounded-[2rem] shadow-xl object-cover aspect-[4/3] lg:aspect-square">
                            @else
                                <div class="w-full aspect-[4/3] lg:aspect-square rounded-[2rem] bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center shadow-xl">
                                    <svg class="w-24 h-24 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Fallback jika belum ada program di database --}}
                <div class="text-center py-16 text-gray-400">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <p class="text-lg font-medium">Program pendidikan sedang dalam proses pembaruan.</p>
                    <p class="text-sm mt-1">Silakan hubungi sekretariat untuk informasi lebih lanjut.</p>
                </div>
            @endif

            <!-- Section: Rincian Biaya Pendidikan -->
            <div class="pt-16 border-t border-gray-100">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-3xl font-bold text-[#0D6B3A] mb-4">Rincian Biaya Pendidikan</h2>
                    <p class="text-gray-600 text-sm md:text-base leading-relaxed">
                        Berikut merupakan rincian rencana komponen biaya pendidikan pada masing-masing unit pendidikan Pondok Pesantren Nurul Khidmah.
                    </p>
                    
                    @if(isset($contents['education_fee_pdf']) && $contents['education_fee_pdf']->image_path)
                        @php
                            $ext = pathinfo($contents['education_fee_pdf']->image_path, PATHINFO_EXTENSION);
                            $typeLabel = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']) ? 'Gambar' : strtoupper($ext);
                        @endphp
                        <div class="mt-6">
                            <a href="{{ route('education.download-fee') }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#E8F81D] hover:bg-yellow-400 text-gray-900 font-bold text-sm shadow-md hover:shadow-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Unduh Brosur & Dokumen Lengkap ({{ $typeLabel }})
                            </a>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse($programs as $program)
                        @php $categoryFees = $fees->where('category', $program->name); @endphp
                        <div class="bg-gray-50 rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-[#0D6B3A] border-b border-gray-200 pb-3 mb-4 uppercase tracking-wide">
                                    {{ $program->name }}
                                </h3>
                                
                                @if($categoryFees->count() > 0)
                                    <div class="space-y-3.5 mb-6">
                                        @foreach($categoryFees as $fee)
                                            <div class="flex justify-between items-start text-sm">
                                                <div>
                                                    <p class="font-semibold text-gray-800">{{ $fee->name }}</p>
                                                    @if($fee->description)
                                                        <p class="text-xs text-gray-400 mt-0.5">{{ $fee->description }}</p>
                                                    @endif
                                                </div>
                                                <span class="font-bold text-[#0D6B3A] shrink-0 ml-4">
                                                    Rp {{ number_format($fee->amount, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 italic py-6 text-center">Data rincian biaya sedang dalam proses penyusunan.</p>
                                @endif
                            </div>

                            @if($program->is_registration_open)
                            <div class="pt-4 border-t border-gray-200/50 flex justify-end">
                                <a href="{{ route('registration') }}" class="text-xs font-bold text-[#0D6B3A] hover:underline flex items-center gap-1">
                                    Mulai Pendaftaran Unit Ini &rarr;
                                </a>
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full">
                            <p class="text-center text-gray-400 italic py-8">Belum ada program pendidikan yang aktif.</p>
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
