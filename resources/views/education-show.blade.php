<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $program->name }} - Pondok Pesantren Nurul Khidmah</title>
    @include('partials.site-favicon')
    @include('partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Manrope', sans-serif; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">

    <x-frontend.navbar />

    <main class="py-10 lg:py-16 bg-white min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl space-y-16">

            {{-- Breadcrumb & Pintasan Antar Unit Pendidikan --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-5">
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm md:text-[15px] text-gray-600">
                    <a href="{{ route('home') }}" class="hover:text-primary-700 font-medium">Home</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('education') }}" class="hover:text-primary-700 font-medium">Pendidikan</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-bold text-gray-900">{{ $program->name }}</span>
                </nav>

                @if(isset($programs) && $programs->count() > 1)
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($programs as $item)
                            <a href="{{ route('education.program', $item->slug) }}"
                               class="inline-flex h-9 items-center rounded-full px-3.5 text-sm font-semibold transition
                                      {{ $item->id === $program->id ? 'bg-primary-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $item->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Hero Unit Pendidikan --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                <div class="lg:col-span-7 space-y-6 text-left order-2 lg:order-1">
                    {{-- Badge status --}}
                    @if($program->is_registration_open)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            Pendaftaran Terbuka
                        </span>
                    @elseif(!$program->is_coming_soon)
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-gray-700">
                            Unit Pendidikan Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-amber-800">
                            Tahap Perencanaan
                        </span>
                    @endif

                    {{-- Judul Unit: elemen teks paling menonjol (36–48px pada desktop) --}}
                    <h1 class="text-4xl lg:text-[44px] font-extrabold tracking-tight text-primary-700 leading-[1.15] text-left">
                        {{ $program->name }}
                    </h1>

                    {{-- Deskripsi Rata Kiri (16–18px, jarak baris 1,65): pengantar 2-3 kalimat + poin penting --}}
                    @if(! empty($parsedDescription['intro']) || ! empty($parsedDescription['points']))
                        <div class="space-y-4 text-left text-base md:text-lg leading-[1.65] text-gray-700">
                            @foreach($parsedDescription['intro'] as $paragraph)
                                <p class="text-left">{{ $paragraph }}</p>
                            @endforeach

                            @if(! empty($parsedDescription['points']))
                                <div class="pt-1 space-y-2.5">
                                    @if(! empty($parsedDescription['points_title']))
                                        <p class="font-bold text-gray-900 text-base md:text-lg">
                                            {{ $parsedDescription['points_title'] }}
                                        </p>
                                    @endif

                                    <ul class="space-y-2.5 text-left">
                                        @foreach($parsedDescription['points'] as $point)
                                            <li class="flex items-start gap-3">
                                                <svg class="w-5 h-5 shrink-0 text-primary-600 mt-1" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span class="text-base md:text-[17px] leading-[1.65] text-gray-700">
                                                    @if(! empty($point['label']))
                                                        <strong class="font-bold text-gray-900">{{ $point['label'] }}:</strong>
                                                    @endif
                                                    {{ $point['text'] }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Tombol Aksi (CTA ringkas + tautan Lihat Biaya) --}}
                    <div class="pt-2 flex flex-wrap items-center gap-3.5">
                        @if($program->is_registration_open)
                            <a href="{{ route('register.create', $program->slug) }}"
                               class="inline-flex h-12 items-center gap-2 justify-center px-7 bg-primary-600 text-white text-base font-bold rounded-full hover:bg-primary-700 transition shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $ctaLabel }}
                            </a>
                        @elseif(!$program->is_coming_soon)
                            <button disabled class="inline-flex h-12 items-center gap-2 justify-center px-6 bg-gray-100 text-gray-500 text-base font-bold rounded-full cursor-not-allowed border border-gray-200">
                                Pendaftaran Ditutup
                            </button>
                        @else
                            <button disabled class="inline-flex h-12 items-center gap-2 justify-center px-6 bg-amber-50 text-amber-700 text-base font-bold rounded-full cursor-not-allowed border border-amber-200">
                                Akan Segera Hadir
                            </button>
                        @endif

                        <a href="#biaya-pendidikan"
                           class="inline-flex h-12 items-center gap-2 justify-center px-6 rounded-full border border-gray-300 bg-white text-base font-bold text-gray-800 hover:bg-gray-50 hover:border-gray-400 transition">
                            Lihat Biaya
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5 order-1 lg:order-2">
                    @if($program->image_path)
                        <img src="{{ asset('storage/' . $program->image_path) }}"
                             alt="{{ $program->name }}"
                             class="w-full h-auto rounded-3xl shadow-xl object-cover aspect-[4/3] border border-gray-100">
                    @else
                        <div class="w-full aspect-[4/3] rounded-3xl bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center shadow-lg border border-emerald-100">
                            <svg class="w-24 h-24 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Section: Rincian & Pratinjau Biaya Pendaftaran --}}
            <section id="biaya-pendidikan" class="pt-12 border-t border-gray-200 space-y-8 scroll-mt-24">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div class="max-w-2xl text-left space-y-2">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-primary-700 leading-tight">
                            Biaya Pendaftaran — {{ $program->name }}
                        </h2>
                        <p class="text-base md:text-lg leading-[1.65] text-gray-700 text-left">
                            Informasi resmi biaya pendidikan dan pendaftaran santri untuk unit {{ $program->name }}.
                        </p>
                    </div>

                    @if($program->is_registration_open)
                        <a href="{{ route('register.create', $program->slug) }}"
                           class="inline-flex h-11 items-center gap-2 rounded-full bg-primary-600 px-6 text-base font-bold text-white hover:bg-primary-700 transition shadow-sm">
                            {{ $ctaLabel }}
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    @endif
                </div>

                {{-- Pratinjau File Biaya (Foto / JPG / CSV / PDF) --}}
                @if(! empty($activeFeePreview))
                    @include('partials.fee-document-preview', ['preview' => $activeFeePreview])
                @else
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-8 text-center text-base text-gray-500">
                        Dokumen biaya pendidikan (Foto, PDF, atau CSV) untuk unit {{ $program->name }} belum diunggah.
                    </div>
                @endif
            </section>

        </div>
    </main>

    <x-frontend.footer />

</body>
</html>
