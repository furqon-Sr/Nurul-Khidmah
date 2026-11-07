<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Wali Santri - Pondok Pesantren Nurul Khidmah Lamongan</title>
    @include('partials.site-favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f0f7f4;
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
<body class="min-h-dvh flex flex-col bg-gradient-to-b from-[#045F33]/5 via-slate-50 to-emerald-50/60 text-slate-900 antialiased">

    <x-frontend.navbar />

    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-md bg-white border border-emerald-900/10 rounded-3xl shadow-xl shadow-emerald-950/10 overflow-hidden">
            {{-- Header Ramah Wali Santri --}}
            <div class="bg-gradient-to-br from-[#045F33] to-[#004524] px-6 py-7 text-center text-white">
                <div class="mx-auto mb-3 h-16 w-16 rounded-full bg-white p-1.5 shadow-md flex items-center justify-center overflow-hidden">
                    @if(isset($settings->logo_path) && $settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo Nurul Khidmah" class="h-full w-full object-contain" />
                    @else
                        <span class="text-xl font-extrabold text-[#045F33]">NK</span>
                    @endif
                </div>
                <span class="inline-block px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-200 text-xs font-semibold tracking-wide mb-2">
                    Khusus Bapak / Ibu Wali Santri
                </span>
                <h1 class="text-2xl font-bold leading-snug">Portal Pemantauan Ananda</h1>
                <p class="text-xs sm:text-sm text-emerald-100 mt-1.5 leading-relaxed">
                    Pantau kehadiran harian, jadwal ngaji &amp; madrasah, nilai pelajaran, serta unduh Rapor Resmi Ananda dari HP.
                </p>
            </div>

            <div class="px-6 py-7 sm:px-8">
                @if($errors->any())
                    <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-700">
                        <p class="font-bold mb-1">Belum berhasil masuk:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Panduan Singkat untuk Wali Santri --}}
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-3.5 text-xs text-emerald-900 leading-relaxed">
                    <strong>Cara mudah masuk:</strong> Cukup ketik <strong>Nomor Induk Santri (NIS 8 angka)</strong> yang tertera pada Kartu/Bukti Pendaftaran Ananda (contoh: <code class="font-mono font-bold">20260001</code>) dan kata sandi dari pesantren.
                </div>

                <form method="POST" action="{{ route('wali.login.store') }}" class="space-y-5" x-data="{ showPassword: false }">
                    @csrf

                    {{-- Honeypot anti-bot field (tersembunyi dari manusia) --}}
                    <div class="hp-field" aria-hidden="true">
                        <label for="website_url">Jangan diisi</label>
                        <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div>
                        <label for="identitas" class="block text-sm font-bold text-slate-800 mb-1.5">
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
                               placeholder="Contoh: 20260001"
                               class="w-full min-h-12 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 placeholder-slate-400 outline-none transition focus:border-[#045F33] focus:ring-2 focus:ring-[#045F33]/20" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-slate-800 mb-1.5">
                            Kata Sandi (Password)
                        </label>
                        <div class="relative flex items-center">
                            <input id="password"
                                   name="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Masukkan kata sandi"
                                   class="w-full min-h-12 rounded-2xl border border-slate-300 bg-white pl-4 pr-14 py-3 text-base text-slate-900 placeholder-slate-400 outline-none transition focus:border-[#045F33] focus:ring-2 focus:ring-[#045F33]/20" />
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    :aria-pressed="showPassword.toString()"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute right-1.5 min-h-11 min-w-11 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-[#045F33] hover:bg-slate-100 transition">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember" class="inline-flex items-center gap-2.5 cursor-pointer py-1 select-none">
                            <input type="checkbox" id="remember" name="remember" value="1" checked
                                   class="h-5 w-5 rounded border-slate-300 text-[#045F33] focus:ring-[#045F33]">
                            <span class="text-sm text-slate-700 font-medium">Ingat akun di HP ini</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full min-h-12 rounded-2xl bg-gradient-to-r from-[#045F33] to-[#004524] hover:from-[#034a28] hover:to-[#00381d] px-5 py-3.5 text-base font-bold text-white shadow-lg shadow-[#045F33]/20 transition flex items-center justify-center gap-2">
                        Masuk ke Portal Wali Santri →
                    </button>
                </form>

                @php
                    $waHelp = preg_replace('/[^0-9]/', '', (string) ($settings->whatsapp ?? '6285648239325'));
                    if (str_starts_with($waHelp, '0')) {
                        $waHelp = '62' . substr($waHelp, 1);
                    }
                @endphp
                <div class="mt-6 pt-5 border-t border-slate-100 text-center space-y-2">
                    <p class="text-xs text-slate-500">Lupa NIS Ananda atau kata sandi?</p>
                    <a href="https://wa.me/{{ $waHelp ?: '6285648239325' }}?text={{ rawurlencode('Assalamu\'alaikum Admin TU Ponpes Nurul Khidmah Lamongan, mohon bantuan informasi NIS / reset kata sandi Portal Wali Santri untuk Ananda: ') }}"
                       target="_blank" rel="noopener noreferrer"
                       class="min-h-11 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-green-50 hover:bg-green-100 text-[#045F33] font-semibold text-xs border border-green-200 transition">
                        💬 Hubungi Bagian Tata Usaha via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
