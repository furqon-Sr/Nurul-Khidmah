<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contents['wali_login_title']->content_text ?? 'Portal Wali Santri' }} - {{ $contents['site_name']->content_text ?? 'Nurul Khidmah' }}</title>
    @include('partials.site-favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #FFFFFF;
        }
        .hp-field {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased flex items-center justify-center p-4 sm:p-6 lg:p-8 xl:p-12">

    <div class="w-full max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 xl:gap-16 items-center">
        
        {{-- KOLOM KIRI: FORMULIR LOGIN WALI SANTRI --}}
        <div class="w-full max-w-md mx-auto lg:col-span-6 xl:col-span-5 flex flex-col justify-between py-6 lg:py-8">
            
            <div>
                {{-- Logo & Brand Header --}}
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 group">
                    @if(isset($settings->logo_path) && $settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" 
                             alt="{{ $contents['site_name']->content_text ?? 'Logo' }}" 
                             class="h-8 w-auto object-contain group-hover:opacity-90 transition" />
                    @else
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-[#045F33] to-[#004524] flex items-center justify-center text-white shadow-sm font-bold text-xs">
                            NK
                        </div>
                    @endif
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ $contents['site_name']->content_text ?? 'Nurul Khidmah' }}
                    </span>
                </a>

                {{-- Judul Sambutan & Tagline --}}
                <div class="mt-8 sm:mt-12 lg:mt-14">
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>{{ $contents['wali_login_title']->content_text ?? 'Portal Wali Santri' }}</span>
                        <span class="inline-block transform origin-bottom-right hover:rotate-12 transition-transform duration-200">👋</span>
                    </h1>
                    <p class="mt-2 text-sm sm:text-base text-slate-500 font-normal leading-relaxed">
                        {{ $contents['wali_login_subtitle']->content_text ?? 'Pantau kehadiran harian, jadwal ngaji & madrasah, nilai pelajaran, serta unduh Rapor Resmi Ananda.' }}
                    </p>
                </div>

                {{-- Notifikasi Error Validasi --}}
                @if($errors->any())
                    <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm text-rose-700">
                        <p class="font-bold mb-1">Belum berhasil masuk:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulir Login Wali --}}
                <form method="POST" action="{{ route('wali.login.store') }}" class="mt-8 space-y-5" x-data="{ showPassword: false }">
                    @csrf

                    {{-- Honeypot anti-bot field (tersembunyi dari manusia) --}}
                    <div class="hp-field" aria-hidden="true">
                        <label for="website_url">Jangan diisi</label>
                        <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="space-y-4">
                        {{-- Input Identitas (NIS atau Email) --}}
                        <div>
                            <label for="identitas" class="block text-xs font-bold text-slate-900 mb-2">
                                NIS Ananda atau Email Wali
                            </label>
                            <input id="identitas"
                                   name="identitas"
                                   type="text"
                                   value="{{ old('identitas') }}"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   autocorrect="off"
                                   autocapitalize="off"
                                   spellcheck="false"
                                   placeholder="Contoh: 20260001 atau nama@email.com"
                                   class="w-full h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm sm:text-base text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#045F33] focus:ring-4 focus:ring-[#045F33]/15 shadow-xs" />
                        </div>

                        {{-- Input Password --}}
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-900 mb-2">
                                Kata Sandi
                            </label>
                            <div class="relative flex items-center">
                                <input id="password"
                                       name="password"
                                       :type="showPassword ? 'text' : 'password'"
                                       required
                                       autocomplete="current-password"
                                       placeholder="Masukkan kata sandi"
                                       class="w-full h-12 rounded-xl border border-slate-200 bg-white pl-4 pr-12 text-sm sm:text-base text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#045F33] focus:ring-4 focus:ring-[#045F33]/15 shadow-xs" />
                                <button type="button"
                                        @click="showPassword = !showPassword"
                                        :aria-pressed="showPassword.toString()"
                                        aria-label="Tampilkan atau sembunyikan kata sandi"
                                        class="absolute right-2 p-2 text-slate-400 hover:text-slate-700 transition">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            
                            {{-- Bantuan Lupa NIS / Password via WhatsApp --}}
                            @php
                                $waHelp = preg_replace('/[^0-9]/', '', (string) ($settings->whatsapp ?? '6285648239325'));
                                if (str_starts_with($waHelp, '0')) {
                                    $waHelp = '62' . substr($waHelp, 1);
                                }
                            @endphp
                            <div class="flex justify-end pt-2">
                                <a href="https://wa.me/{{ $waHelp ?: '6285648239325' }}?text={{ rawurlencode('Assalamu\'alaikum Admin TU Ponpes Nurul Khidmah Lamongan, mohon bantuan informasi NIS / reset kata sandi Portal Wali Santri untuk Ananda: ') }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="text-xs sm:text-sm font-semibold text-[#045F33] hover:text-[#004524] transition">
                                    Lupa NIS / Sandi? Bantuan TU WhatsApp →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Remember Me Checkbox --}}
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="remember" name="remember" value="1" checked
                                   class="h-4 w-4 rounded border-slate-300 text-[#045F33] focus:ring-[#045F33]">
                            <span class="text-xs sm:text-sm text-slate-600 font-medium">Ingat akun di HP ini</span>
                        </label>
                    </div>

                    {{-- Tombol Submit Sign In --}}
                    <button type="submit" 
                            class="w-full h-12 rounded-xl bg-gradient-to-r from-[#045F33] to-[#004524] hover:from-[#034d29] hover:to-[#00361c] text-white font-semibold text-sm sm:text-base transition duration-200 shadow-lg shadow-[#045F33]/25 flex items-center justify-center gap-2 mt-6 active:scale-[0.99] cursor-pointer">
                        Masuk ke Portal Wali Santri →
                    </button>
                </form>
            </div>

            {{-- Footer Bawah Sesuai Referensi --}}
            <div class="mt-12 lg:mt-16 text-center space-y-3">
                <p class="text-[11px] sm:text-xs font-semibold tracking-wider text-slate-400 uppercase">
                    &copy; {{ date('Y') }} ALL RIGHTS RESERVED
                </p>
                <div class="flex items-center justify-center text-xs text-slate-400">
                    <a href="{{ route('home') }}" class="hover:text-slate-700 transition">
                        ← Kembali ke Beranda
                    </a>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: KARTU BANNER VISUAL (SESUAI GAMBAR REFERENSI & DAPAT DIUBAH ADMIN) --}}
        <div class="hidden lg:flex lg:col-span-6 xl:col-span-7 h-full min-h-[580px] lg:min-h-[660px] xl:min-h-[720px] rounded-[2.5rem] relative overflow-hidden shadow-2xl shadow-slate-900/10 border border-slate-100 items-center justify-center">
            
            @if(isset($contents['wali_login_image']) && $contents['wali_login_image']->image_path)
                {{-- GAMBAR KUSTOM YANG DIUNGGAH MELALUI PANEL ADMIN --}}
                <img src="{{ asset('storage/' . $contents['wali_login_image']->image_path) }}" 
                     alt="Banner Login Wali Santri" 
                     class="absolute inset-0 w-full h-full object-cover">
                
                {{-- Subtle branding badge in corner --}}
                <div class="absolute bottom-6 left-6 right-6 p-4 rounded-2xl bg-white/85 backdrop-blur-md border border-white/60 shadow-lg flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if(isset($settings->logo_path) && $settings->logo_path)
                            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="" class="h-8 w-auto object-contain">
                        @endif
                        <span class="text-sm font-bold text-slate-900">
                            {{ $contents['site_name']->content_text ?? 'Nurul Khidmah' }}
                        </span>
                    </div>
                    <span class="text-xs text-emerald-800 font-bold uppercase tracking-wider">Portal Wali Santri</span>
                </div>
            @else
                {{-- ILUSTRASI GRAFIS BAWAAN (PERSIS GAMBAR REFERENSI DENGAN AKSEN EMERALD GREEN GRADIENT) --}}
                <div class="absolute inset-0 w-full h-full bg-gradient-to-b from-[#EBF5F0] via-[#E2EFE9] to-[#CDE4DA]">
                    
                    {{-- 3D Sphere / Crescent di Sudut Kanan Atas dengan Gradien #045F33 -> #004524 --}}
                    <div class="absolute -top-16 -right-16 w-80 h-80 rounded-full bg-gradient-to-br from-[#045F33] via-[#02522c] to-[#004524] opacity-95 shadow-2xl"></div>
                    <div class="absolute -top-6 -right-6 w-64 h-64 rounded-full border-[18px] border-white/20 pointer-events-none"></div>

                    {{-- Partikel Bulat Kanan Atas --}}
                    <div class="absolute top-24 right-20 w-3 h-3 rounded-full bg-[#004524] opacity-80 shadow-sm"></div>
                    <div class="absolute top-36 right-24 w-3.5 h-3.5 rounded-full bg-[#045F33] opacity-85 shadow-sm"></div>

                    {{-- 3D Sphere / Crescent di Sudut Kiri Bawah --}}
                    <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-gradient-to-tr from-[#004524] via-[#02522c] to-[#045F33] opacity-95 shadow-2xl"></div>

                    {{-- Partikel Bulat Kiri Bawah --}}
                    <div class="absolute bottom-28 left-28 w-3 h-3 rounded-full bg-[#004524] opacity-80 shadow-sm"></div>
                    <div class="absolute bottom-40 left-36 w-2.5 h-2.5 rounded-full bg-[#045F33] opacity-75 shadow-sm"></div>
                </div>

                {{-- Emblem Lingkaran Putih & Teks Brand di Tengah --}}
                <div class="relative z-10 flex items-center gap-5 sm:gap-6 px-8 py-6">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-white shadow-2xl shadow-slate-900/15 flex items-center justify-center p-3 sm:p-4 border border-white/80 shrink-0">
                        @if(isset($settings->logo_path) && $settings->logo_path)
                            <img src="{{ asset('storage/' . $settings->logo_path) }}" 
                                 alt="{{ $contents['site_name']->content_text ?? 'Logo' }}" 
                                 class="max-h-full max-w-full object-contain">
                        @else
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#045F33] to-[#004524] flex items-center justify-center text-white font-extrabold text-lg">
                                NK
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-left">
                        <span class="block text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-none">
                            {{ $contents['site_name']->content_text ?? 'Nurul Khidmah' }}
                        </span>
                        <span class="block text-sm sm:text-base font-bold text-[#045F33] tracking-widest uppercase mt-1.5">
                            Portal Wali Santri
                        </span>
                    </div>
                </div>
            @endif

        </div>

    </div>

</body>
</html>
