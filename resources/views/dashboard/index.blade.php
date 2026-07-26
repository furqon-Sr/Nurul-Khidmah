@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@if(auth()->user()->isTenagaPengajar())
    {{-- ═══ TAMPILAN DASHBOARD KHUSUS USTAZ / PENGAJAR ═══ --}}
    
    {{-- Profile Banner Ustaz --}}
    <div class="mb-8 bg-gradient-to-br from-indigo-800 via-indigo-900 to-slate-900 rounded-2xl shadow-lg overflow-hidden text-white relative">
        <div class="relative px-6 py-7 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5 z-10">
            <div class="shrink-0">
                @if(auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
                @else
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-600 ring-4 ring-white/30 shadow-xl flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ auth()->user()->initials() }}</span>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-indigo-200 text-sm font-medium mb-0.5">Ahlan wa Sahlan,</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ auth()->user()->name }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    @include('partials.admin-role-badge', ['user' => auth()->user()])
                    @if(isset($myWaliKelas) && $myWaliKelas)
                        <span class="px-2.5 py-0.5 text-xs font-bold bg-amber-400 text-amber-950 rounded-full">
                            Wali Kelas: {{ $myWaliKelas->nama_kelas }}
                        </span>
                    @endif
                    <span class="text-indigo-200 text-xs flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ auth()->user()->email }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('teacher.attendance.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-lg shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Input Presensi
                </a>
                <a href="{{ route('teacher.grades.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-sm font-medium rounded-lg shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Input Nilai
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards Ustaz --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Jadwal Mengajar</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $mySchedules->count() }}</p>
            <p class="text-xs text-indigo-600 mt-1 font-medium">Sesi Pelajaran</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Kelas Diampu</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $myClassesCount }}</p>
            <p class="text-xs text-emerald-600 mt-1 font-medium">Kelompok Belajar</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Mata Pelajaran</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $mySubjectsCount }}</p>
            <p class="text-xs text-purple-600 mt-1 font-medium">Bidang Studi</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Total Santri Diajar</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $mySantriCount }}</p>
            <p class="text-xs text-amber-600 mt-1 font-medium">Santri Aktif</p>
        </div>
    </div>

    {{-- Tabel Jadwal Mengajar Ustaz --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-base">Jadwal Mengajar Anda</h3>
            <span class="text-xs text-gray-500">Semester Aktif</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Hari & Waktu</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Mata Pelajaran</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Ruang</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($mySchedules as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-bold rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                {{ $schedule->hari }}
                            </span>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                {{ date('H:i', strtotime($schedule->waktu_mulai)) }} - {{ date('H:i', strtotime($schedule->waktu_selesai)) }} WIB
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                            {{ $schedule->kelas->nama_kelas ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-emerald-700 dark:text-emerald-400">
                            {{ $schedule->mataPelajaran->nama_mapel ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->ruang ?? 'Kelas Utama' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="{{ route('teacher.attendance.create', ['schedule_id' => $schedule->id, 'tanggal' => date('Y-m-d')]) }}" class="text-xs px-2.5 py-1 bg-emerald-100 text-emerald-800 hover:bg-emerald-200 font-semibold rounded mr-2 inline-block">Absen Hari Ini</a>
                            <a href="{{ route('teacher.grades.edit', $schedule->id) }}" class="text-xs px-2.5 py-1 bg-purple-100 text-purple-800 hover:bg-purple-200 font-semibold rounded inline-block">Input Nilai</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                            Belum ada jadwal mengajar yang diplotkan untuk Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@else
    {{-- ═══ TAMPILAN DASHBOARD ADMIN / PENGURUS AKADEMIK ═══ --}}
    
    {{-- Welcome / Profile Hero --}}
    <div class="mb-8 bg-gradient-to-br from-emerald-700 via-emerald-800 to-emerald-950 dark:from-emerald-900 dark:via-emerald-950 dark:to-slate-900 rounded-2xl shadow-lg overflow-hidden">
        <div class="relative px-6 py-7 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5">

            <div class="absolute top-0 right-0 w-72 h-72 bg-white/5 rounded-full -translate-y-1/3 translate-x-1/3 pointer-events-none"></div>
            <div class="absolute bottom-0 left-24 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 pointer-events-none"></div>

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
                <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-green-400 ring-2 ring-emerald-800"></span>
            </div>

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

            <div class="relative z-10 flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('admin.registrations.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium rounded-lg border border-white/20 transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Lihat Pendaftar
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Action Panel Admin --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Aksi Cepat Admin</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('admin.santri.create') }}" class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-500/10 hover:bg-purple-100 text-purple-700 dark:text-purple-300 px-4 py-2.5 rounded-lg font-medium transition border border-purple-100">
                + Tambah Santri
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="inline-flex items-center gap-2 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 px-4 py-2.5 rounded-lg font-medium transition border border-indigo-100">
                + Tambah Pengajar
            </a>
            <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 px-4 py-2.5 rounded-lg font-medium transition border border-emerald-100">
                + Plotting Jadwal
            </a>
        </div>
    </div>

    {{-- Statistik Cards Admin --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase">Pendaftar Pending</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $newRegistrantsCount ?? 0 }}</p>
            <p class="text-sm font-medium text-amber-600 mt-1">Butuh Tindakan</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-emerald-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase">Total Santri PPDB</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalSantri ?? 0 }}</p>
            <p class="text-sm font-medium text-emerald-600 mt-1">Diterima</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase">Program Aktif</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeProgramsCount ?? 0 }}</p>
            <p class="text-sm font-medium text-blue-600 mt-1">Berjalan</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-sm font-semibold uppercase">Total Berita</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalNews ?? 0 }}</p>
            <p class="text-sm font-medium text-purple-600 mt-1">Publikasi</p>
        </div>
    </div>
@endif

@endsection
