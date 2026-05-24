@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

{{-- ═══ Welcome / Profile Hero ═══ --}}
<div class="mb-8 bg-gradient-to-br from-emerald-700 via-emerald-800 to-emerald-950 dark:from-emerald-900 dark:via-emerald-950 dark:to-slate-900 rounded-2xl shadow-lg overflow-hidden">
    <div class="relative px-6 py-7 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5">

        {{-- Dekorasi lingkaran background --}}
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/5 rounded-full -translate-y-1/3 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-24 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 pointer-events-none"></div>

        {{-- Foto Profil --}}
        <div class="relative z-10 shrink-0">
            @if(auth()->user()->avatarUrl())
                <img src="{{ auth()->user()->avatarUrl() }}"
                     alt="{{ auth()->user()->name }}"
                     class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
            @else
                <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 ring-4 ring-white/30 shadow-xl flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">{{ auth()->user()->initials() }}</span>
                </div>
            @endif
            {{-- Indikator online --}}
            <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-green-400 ring-2 ring-emerald-800"></span>
        </div>

        {{-- Info Profil --}}
        <div class="relative z-10 flex-1 min-w-0">
            <p class="text-emerald-300 text-sm font-medium mb-0.5">Selamat datang kembali,</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ auth()->user()->name }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-2">
                @include('partials.admin-role-badge', ['user' => auth()->user()])
                <span class="text-emerald-300 text-xs flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ auth()->user()->email }}
                </span>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="relative z-10 flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('admin.registrations.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium rounded-lg border border-white/20 transition backdrop-blur-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Lihat Pendaftar
            </a>
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium rounded-lg border border-white/20 transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kelola Admin
                </a>
            @endif
        </div>
    </div>
</div>

{{-- ═══ Quick Action Panel ═══ --}}
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Aksi Cepat</h2>
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('admin.news.create') }}" class="inline-flex items-center gap-2 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 px-4 py-2.5 rounded-lg font-medium transition-colors border border-indigo-100 dark:border-indigo-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Berita
        </a>
        <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center gap-2 bg-sky-50 dark:bg-sky-500/10 hover:bg-sky-100 dark:hover:bg-sky-500/20 text-sky-700 dark:text-sky-300 px-4 py-2.5 rounded-lg font-medium transition-colors border border-sky-100 dark:border-sky-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Fasilitas
        </a>
        <a href="{{ route('admin.programs.create') }}" class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 px-4 py-2.5 rounded-lg font-medium transition-colors border border-emerald-100 dark:border-emerald-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Program
        </a>
    </div>
</div>

{{-- ═══ Statistik Cards ═══ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-amber-500 transition hover:shadow-md relative overflow-hidden">
        <div class="flex justify-between">
            <div class="z-10 relative">
                <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase tracking-wider">Pendaftar Pending</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $newRegistrantsCount ?? 0 }}</p>
                <p class="text-sm font-medium text-amber-600 dark:text-amber-400 mt-1">Butuh Tindakan</p>
            </div>
            <div class="p-3 bg-amber-50 dark:bg-amber-500/20 rounded-full text-amber-500 dark:text-amber-400 z-10 relative self-start">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-emerald-500 transition hover:shadow-md relative overflow-hidden">
        <div class="flex justify-between">
            <div class="z-10 relative">
                <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase tracking-wider">Total Santri</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalSantri ?? 0 }}</p>
                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400 mt-1">Disetujui</p>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-500/20 rounded-full text-emerald-500 dark:text-emerald-400 z-10 relative self-start">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-blue-500 transition hover:shadow-md relative overflow-hidden">
        <div class="flex justify-between">
            <div class="z-10 relative">
                <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase tracking-wider">Program Aktif</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeProgramsCount ?? 0 }}</p>
                <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mt-1">Berjalan</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-500/20 rounded-full text-blue-500 dark:text-blue-400 z-10 relative self-start">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-purple-500 transition hover:shadow-md relative overflow-hidden">
        <div class="flex justify-between">
            <div class="z-10 relative">
                <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase tracking-wider">Total Berita</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalNews ?? 0 }}</p>
                <p class="text-sm font-medium text-purple-600 dark:text-purple-400 mt-1">Publikasi</p>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-500/20 rounded-full text-purple-500 dark:text-purple-400 z-10 relative self-start">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H14"></path></svg>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Dua Kolom: Pendaftar Pending + Tim Admin ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Kolom Kiri: Pendaftar Pending (2/3 lebar) --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/80">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Pendaftar Perlu Ditinjau
            </h2>
            @if($pendingRegistrants->count() > 0)
                <a href="{{ route('admin.registrations.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">Lihat Semua</a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Nama Pendaftar</th>
                        <th class="px-6 py-4 font-semibold">Program</th>
                        <th class="px-6 py-4 font-semibold">Tanggal Daftar</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($pendingRegistrants as $registrant)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $registrant->student_name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $registrant->registration_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-500/30">
                                {{ $registrant->target_class ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $registrant->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.registrations.show', $registrant) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/20 border border-indigo-100 dark:border-indigo-500/30 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-500/40 transition-colors">
                                Tinjau
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-base font-medium text-gray-500 dark:text-gray-400">Tidak ada pendaftar pending</p>
                                <p class="text-sm mt-1">Semua pendaftaran telah ditinjau.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kolom Kanan: Tim Admin (1/3 lebar) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/80">
            <h2 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Tim Admin
            </h2>
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline">Kelola</a>
            @endif
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($adminUsers as $adminUser)
            <div class="px-5 py-4 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                {{-- Foto profil --}}
                <div class="relative shrink-0">
                    @if($adminUser->avatarUrl())
                        <img src="{{ $adminUser->avatarUrl() }}"
                             alt="{{ $adminUser->name }}"
                             class="h-11 w-11 rounded-full object-cover ring-2 ring-white dark:ring-gray-700 shadow">
                    @else
                        <div class="h-11 w-11 rounded-full bg-gradient-to-br
                            {{ $adminUser->isSuperAdmin() ? 'from-amber-400 to-orange-500' : 'from-emerald-400 to-emerald-600' }}
                            flex items-center justify-center ring-2 ring-white dark:ring-gray-700 shadow">
                            <span class="text-sm font-bold text-white">{{ $adminUser->initials() }}</span>
                        </div>
                    @endif
                    {{-- Dot "ini Anda" --}}
                    @if($adminUser->id === auth()->id())
                        <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full bg-green-400 ring-2 ring-white dark:ring-gray-800" title="Anda"></span>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $adminUser->name }}</span>
                        @if($adminUser->id === auth()->id())
                            <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">(Anda)</span>
                        @endif
                    </div>
                    <div class="mt-0.5">
                        @include('partials.admin-role-badge', ['user' => $adminUser])
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-400 dark:text-slate-500 text-center">
                {{ $adminUsers->count() }} pengguna admin terdaftar
            </p>
        </div>
    </div>

</div>

@endsection
