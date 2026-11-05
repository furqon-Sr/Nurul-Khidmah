<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Internal Staf &amp; Pengajar - Nurul Khidmah</title>
    @include('partials.site-favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #F4F7F5;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-dvh flex items-center justify-center px-4 py-8 bg-slate-100 antialiased">
    <div class="w-full max-w-md bg-white border border-[#045F33]/10 rounded-[2rem] shadow-2xl shadow-slate-900/10 overflow-hidden">
        <div class="px-6 py-8 sm:px-8 sm:py-10">
            <div class="flex flex-col items-center gap-3 text-center">
                @if(isset($settings->logo_path) && $settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo Nurul Khidmah" class="h-16 sm:h-20 w-auto object-contain" />
                @else
                    <div class="h-16 w-16 rounded-full bg-[#045F33] flex items-center justify-center text-white text-2xl font-bold">
                        NK
                    </div>
                @endif

                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[#045F33]">Portal Staf &amp; Dewan Guru</h1>
                </div>
            </div>

            @if(session('status'))
                <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3.5 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-5" x-data="{ showPassword: false }">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Resmi</label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="email"
                               autocorrect="off"
                               autocapitalize="off"
                               spellcheck="false"
                               class="w-full min-h-12 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 outline-none transition focus:border-[#045F33] focus:ring-2 focus:ring-[#045F33]/15" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Kata Sandi</label>
                        <div class="relative flex items-center">
                            <input id="password"
                                   name="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   required
                                   autocomplete="current-password"
                                   class="w-full min-h-12 rounded-2xl border border-slate-300 bg-white pl-4 pr-14 py-3 text-base text-slate-900 outline-none transition focus:border-[#045F33] focus:ring-2 focus:ring-[#045F33]/15" />
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
                </div>

                <button type="submit" class="w-full min-h-12 rounded-2xl bg-[#045F33] px-4 py-3.5 text-base font-semibold text-white shadow-lg shadow-[#045F33]/15 transition hover:bg-[#034a2a]">
                    Masuk ke Panel Sistem
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center space-y-2.5 text-xs sm:text-sm text-slate-500">
                <p>
                    <a href="{{ route('home') }}" class="text-slate-500 hover:text-slate-700 hover:underline">
                        ← Kembali ke Beranda Website
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
