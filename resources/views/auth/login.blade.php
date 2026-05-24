<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nurul Khidmah</title>
    @include('partials.site-favicon')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F4F7F5;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8 bg-slate-50">
    <div class="w-full max-w-md bg-white border border-[#045F33]/10 rounded-[2rem] shadow-2xl shadow-slate-900/10 overflow-hidden">
        <div class="px-8 py-10">
            <div class="flex flex-col items-center gap-4 text-center">
                @if(isset($settings->logo_path) && $settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo Nurul Khidmah" class="h-20 w-auto object-contain" />
                @else
                    <div class="h-20 w-20 rounded-full bg-[#045F33] flex items-center justify-center text-white text-2xl font-bold">
                        NK
                    </div>
                @endif

                <div>
                    <h1 class="text-3xl font-bold text-[#045F33]">Masuk ke Nurul Khidmah</h1>
                    <p class="text-sm text-slate-500 mt-2">Dashboard admin untuk kelola pendaftaran dan konten.</p>
                </div>
            </div>

            @if(session('status'))
                <div class="mt-8 rounded-3xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mt-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-6">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                            class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#045F33] focus:ring-2 focus:ring-[#045F33]/15" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#045F33] focus:ring-2 focus:ring-[#045F33]/15" />
                    </div>
                </div>

                <button type="submit" class="w-full rounded-3xl bg-[#045F33] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-[#045F33]/15 transition hover:bg-[#034a2a]">
                    Login
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                <a href="https://wa.me/6285648239325?text=Halo%20Admin%2C%20saya%20membutuhkan%20bantuan%20reset%20password%20untuk%20akun%20dashboard%20Nurul%20Khidmah." target="_blank" rel="noopener noreferrer"
                   class="font-semibold text-[#045F33] hover:underline">
                    Lupa Password? Hubungi Admin
                </a>
            </p>
        </div>
    </div>
</body>
</html>
