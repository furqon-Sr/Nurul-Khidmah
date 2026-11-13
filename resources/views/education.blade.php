<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Pendidikan - Pondok Pesantren Nurul Khidmah</title>
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

    <main class="py-12 lg:py-16 bg-white min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl space-y-16">

            {{-- Header Pilihan Unit Pendidikan --}}
            <header class="max-w-3xl text-left space-y-4">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800">
                    Unit &amp; Jenjang Pendidikan
                </span>
                <h1 class="text-4xl lg:text-[44px] font-extrabold tracking-tight text-primary-700 leading-[1.15]">
                    Program Pendidikan Nurul Khidmah
                </h1>
                <p class="text-base md:text-lg leading-[1.65] text-gray-700 text-left">
                    Pilih salah satu jenjang pendidikan di bawah ini untuk melihat kurikulum, profil lengkap unit, dan rincian biaya pendaftaran secara terpisah.
                </p>
                <div class="pt-1 flex flex-wrap items-center gap-3">
                    <a href="#biaya-pendidikan"
                       class="inline-flex h-11 items-center gap-2 rounded-full border border-gray-300 bg-white px-5 text-base font-semibold text-gray-800 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lihat Biaya
                    </a>
                </div>
            </header>

            {{-- Grid Kartu Preview Pilihan Program Pendidikan --}}
            <section aria-label="Daftar Program Pendidikan">
                @if(isset($programs) && $programs->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                        @foreach($programs as $program)
                            @php
                                $excerpt = \App\Support\EducationContentFormatter::excerpt($program->description, 155);
                                $ctaLabel = \App\Support\EducationContentFormatter::registrationCtaLabel($program->name);
                            @endphp
                            <article class="group flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden">
                                <a href="{{ route('education.program', $program->slug) }}" class="block aspect-[4/3] bg-gray-100 overflow-hidden relative">
                                    @if($program->image_path)
                                        <img src="{{ asset('storage/' . $program->image_path) }}"
                                             alt="{{ $program->name }}"
                                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                    @endif

                                    <div class="absolute top-3 left-3">
                                        @if($program->is_registration_open)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white shadow-xs">
                                                Pendaftaran Terbuka
                                            </span>
                                        @elseif(!$program->is_coming_soon)
                                            <span class="inline-flex items-center rounded-full bg-gray-900/75 px-3 py-1 text-xs font-bold text-white">
                                                Unit Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white shadow-xs">
                                                Tahap Perencanaan
                                            </span>
                                        @endif
                                    </div>
                                </a>

                                <div class="p-6 flex-1 flex flex-col text-left">
                                    <h2 class="text-2xl font-bold text-gray-900 leading-snug">
                                        <a href="{{ route('education.program', $program->slug) }}" class="hover:text-primary-700 transition-colors">
                                            {{ $program->name }}
                                        </a>
                                    </h2>

                                    @if($excerpt !== '')
                                        <p class="mt-3 text-base leading-[1.65] text-gray-700 text-left flex-1">
                                            {{ $excerpt }}
                                        </p>
                                    @endif

                                    <div class="mt-6 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                        <a href="{{ route('education.program', $program->slug) }}"
                                           class="inline-flex items-center gap-1.5 text-base font-bold text-primary-700 hover:text-primary-800 hover:underline">
                                            Lihat Detail
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>

                                        @if($program->is_registration_open)
                                            <a href="{{ route('register.create', $program->slug) }}"
                                               class="inline-flex h-10 items-center justify-center rounded-full bg-primary-600 px-5 text-sm font-bold text-white hover:bg-primary-700 transition shadow-xs">
                                                {{ $ctaLabel }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-6 py-16 text-center text-gray-500">
                        <p class="text-lg font-semibold">Program pendidikan sedang dalam proses pembaruan.</p>
                        <p class="text-base mt-1">Silakan hubungi sekretariat untuk informasi lebih lanjut.</p>
                    </div>
                @endif
            </section>

            {{-- Section: Pratinjau File Biaya Pendidikan (Gambar / PDF / CSV) --}}
            <section id="biaya-pendidikan" class="pt-12 border-t border-gray-200 space-y-8 scroll-mt-24">
                <div class="max-w-3xl text-left space-y-2">
                    <h2 class="text-3xl font-extrabold text-primary-700 leading-tight">
                        Biaya Pendidikan
                    </h2>
                    <p class="text-base md:text-lg leading-[1.65] text-gray-700 text-left">
                        Berikut dokumen resmi biaya pendidikan (Foto, PDF, atau CSV) untuk masing-masing unit di Pondok Pesantren Nurul Khidmah.
                    </p>
                </div>

                {{-- Pratinjau File Biaya Khusus Per Program (Jika Ada) --}}
                @if(! empty($programFeePreviews))
                    <div class="space-y-6">
                        @foreach($programFeePreviews as $preview)
                            @include('partials.fee-document-preview', ['preview' => $preview])
                        @endforeach
                    </div>
                @endif

                {{-- Pratinjau File Biaya Umum (Foto / JPG / CSV / PDF) --}}
                @if(! empty($globalFeePreview))
                    @include('partials.fee-document-preview', ['preview' => $globalFeePreview])
                @endif

                @if(empty($globalFeePreview) && empty($programFeePreviews))
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-8 text-center text-base text-gray-500">
                        Dokumen biaya pendidikan (Foto, PDF, atau CSV) belum diunggah.
                    </div>
                @endif
            </section>

        </div>
    </main>

    <x-frontend.footer />

</body>
</html>
