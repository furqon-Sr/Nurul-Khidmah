@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@if(auth()->user()->isTenagaPengajar())
    {{-- ═══ TAMPILAN DASHBOARD KHUSUS USTAZ / PENGAJAR ═══ --}}
    <div class="mb-8 bg-gradient-to-br from-indigo-800 via-indigo-900 to-slate-900 rounded-2xl shadow-lg overflow-hidden text-white relative">
        <div class="relative px-5 py-6 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5 z-10">
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
                <div class="flex flex-wrap items-center gap-2.5 mt-2">
                    @include('partials.admin-role-badge', ['user' => auth()->user()])
                    @if(isset($myWaliKelas) && $myWaliKelas)
                        <span class="px-2.5 py-0.5 text-xs font-bold bg-amber-400 text-amber-950 rounded-full">
                            Wali Kelas: {{ $myWaliKelas->nama_kelas }}
                        </span>
                    @endif
                    <span class="text-indigo-200 text-xs flex items-center gap-1 break-all">
                        {{ auth()->user()->email }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:flex sm:flex-wrap gap-2 shrink-0 w-full sm:w-auto">
                <a href="{{ route('teacher.attendance.index') }}" class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl shadow transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Input Presensi
                </a>
                <a href="{{ route('teacher.grades.index') }}" class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-xl shadow transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Input Nilai
                </a>
                <a href="{{ route('admin.reports.index') }}" class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-amber-950 text-sm font-bold rounded-xl shadow transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Cetak Rapor &amp; Rekap
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-indigo-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Jadwal Mengajar</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $mySchedules->count() }}</p>
            <p class="text-xs text-indigo-600 mt-1 font-medium">Sesi Pelajaran</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-emerald-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Kelas Diampu</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $myClassesCount }}</p>
            <p class="text-xs text-emerald-600 mt-1 font-medium">Kelompok Belajar</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-purple-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Mata Pelajaran</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $mySubjectsCount }}</p>
            <p class="text-xs text-purple-600 mt-1 font-medium">Bidang Studi</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-amber-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Total Santri Diajar</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $mySantriCount }}</p>
            <p class="text-xs text-amber-600 mt-1 font-medium">Santri Aktif</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <h3 id="teacher-schedule-heading" class="font-bold text-gray-800 dark:text-gray-100 text-base">Jadwal Mengajar Anda</h3>
            <span class="text-xs text-gray-500">{{ $activeYear?->nama_tahun_ajaran ?? 'Semester Aktif' }}</span>
        </div>

        <div tabindex="0" role="region" aria-labelledby="teacher-schedule-heading" class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Hari &amp; Waktu</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Kelas</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Mata Pelajaran</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Ruang</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($mySchedules as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-bold rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                {{ $schedule->hari }}
                            </span>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                {{ date('H:i', strtotime($schedule->waktu_mulai)) }} - {{ date('H:i', strtotime($schedule->waktu_selesai)) }} WIB
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                            {{ $schedule->kelas->nama_kelas ?? '-' }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap font-semibold text-emerald-700 dark:text-emerald-400">
                            {{ $schedule->mataPelajaran->nama_mapel ?? '-' }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->ruang ?? 'Kelas Utama' }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                            <div class="inline-flex flex-wrap justify-end gap-2">
                                <a href="{{ route('teacher.attendance.create', ['schedule_id' => $schedule->id, 'tanggal' => date('Y-m-d')]) }}" class="min-h-10 inline-flex items-center text-xs px-3 py-2 bg-emerald-100 text-emerald-800 hover:bg-emerald-200 font-semibold rounded-lg">Absen Hari Ini</a>
                                <a href="{{ route('teacher.grades.edit', $schedule->id) }}" class="min-h-10 inline-flex items-center text-xs px-3 py-2 bg-purple-100 text-purple-800 hover:bg-purple-200 font-semibold rounded-lg">Input Nilai</a>
                            </div>
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

@elseif(auth()->user()->isKepalaSekolah())
    {{-- ═══ TAMPILAN DASHBOARD KHUSUS KEPALA SEKOLAH / MADRASAH ═══ --}}
    <div class="mb-8 bg-gradient-to-br from-cyan-900 via-sky-900 to-slate-900 rounded-2xl shadow-lg overflow-hidden text-white relative">
        <div class="relative px-5 py-6 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5 z-10">
            <div class="shrink-0">
                @if(auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
                @else
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-700 ring-4 ring-white/30 shadow-xl flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ auth()->user()->initials() }}</span>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-cyan-200 text-sm font-medium mb-0.5">Ahlan wa Sahlan, Kepala Madrasah</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ auth()->user()->name }}</h1>
                <div class="flex flex-wrap items-center gap-2.5 mt-2">
                    @include('partials.admin-role-badge', ['user' => auth()->user()])
                    <span class="text-cyan-200 text-xs flex items-center gap-1 break-all">
                        {{ auth()->user()->email }}
                    </span>
                </div>
                <p class="text-xs text-cyan-100/90 mt-2">
                    Panel Pemantauan Kepatuhan Guru, Rekapitulasi Pembelajaran Santri, dan Validasi Rapor Akademik.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:flex sm:flex-wrap gap-2 shrink-0 w-full sm:w-auto">
                <a href="{{ route('teacher.attendance.index') }}" class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl shadow transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Pantau Presensi Guru
                </a>
                <a href="{{ route('teacher.grades.index') }}" class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-xl shadow transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Pantau Input Nilai
                </a>
                <a href="{{ route('admin.reports.index') }}" class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-amber-950 text-sm font-bold rounded-xl shadow transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Leger &amp; Rekap Rapor
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-cyan-600">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Kepatuhan Nilai Guru</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $complianceRate }}%</p>
            <p class="text-xs text-cyan-600 mt-1 font-medium">{{ $gradedSchedulesCount }} dari {{ $totalSchedules }} Kelas Dinilai</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Total Guru Aktif</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalTeachers }}</p>
            <p class="text-xs text-indigo-600 mt-1 font-medium">Tenaga Pengajar</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Santri Aktif</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeSantriCount }}</p>
            <p class="text-xs text-emerald-600 mt-1 font-medium">Dalam {{ $totalClasses }} Rombel Aktif</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Tahun Ajaran Aktif</h3>
            <p class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeYear?->nama_tahun_ajaran ?? '-' }}</p>
            <p class="text-xs text-amber-600 mt-1 font-medium">{{ $activeYear?->semester ?? 'Semester Belum Diset' }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm sm:text-base">Jadwal Belum Diinput Nilai Oleh Guru</h3>
                <p class="text-xs text-gray-500 mt-0.5">Pantau sesi pelajaran yang belum memiliki entri nilai rapor santri</p>
            </div>
            <a href="{{ route('teacher.grades.index') }}" class="text-xs font-semibold text-cyan-700 dark:text-cyan-400 hover:underline">Lihat Semua Guru →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Mata Pelajaran &amp; Kelas</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Guru Pengampu</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Waktu</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Aksi Supervisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($ungradedSchedules ?? [] as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $schedule->mataPelajaran->nama_mapel ?? '-' }}</span>
                            <div class="text-xs text-gray-500 font-medium">Kelas: {{ $schedule->kelas->nama_kelas ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">{{ $schedule->pengajar->nama_lengkap ?? '-' }}</span>
                            <div class="text-xs text-gray-500">NIP/KODE: {{ $schedule->pengajar->nip ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">
                            {{ $schedule->hari }}, {{ date('H:i', strtotime($schedule->waktu_mulai)) }} WIB
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                            <a href="{{ route('teacher.grades.edit', $schedule->id) }}" class="inline-flex items-center text-xs px-3 py-1.5 bg-cyan-100 text-cyan-800 hover:bg-cyan-200 font-semibold rounded-lg">
                                Periksa Lembar Nilai
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-emerald-600 font-semibold text-sm">
                            Alhamdulillah! Seluruh jadwal pelajaran telah diinput nilainya oleh para guru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif(auth()->user()->isContentAdmin())
    {{-- ═══ TAMPILAN DASHBOARD KHUSUS ADMIN KONTEN WEBSITE YAYASAN ═══ --}}
    <div class="mb-8 bg-gradient-to-br from-sky-800 via-emerald-800 to-emerald-950 rounded-2xl shadow-lg overflow-hidden text-white relative">
        <div class="relative px-5 py-6 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5 z-10">
            <div class="shrink-0">
                @if(auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
                @else
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-sky-400 to-emerald-600 ring-4 ring-white/30 shadow-xl flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ auth()->user()->initials() }}</span>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sky-200 text-sm font-medium mb-0.5">Panel Pengelola Website Yayasan,</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ auth()->user()->name }}</h1>
                <div class="flex flex-wrap items-center gap-2.5 mt-2">
                    @include('partials.admin-role-badge', ['user' => auth()->user()])
                    <span class="text-sky-200 text-xs break-all">{{ auth()->user()->email }}</span>
                </div>
                <p class="text-xs text-emerald-100/90 mt-2">
                    Akses terisolasi khusus manajemen berita, dokumentasi foto, banner beranda, logo/ikon situs, dan profil Yayasan Pondok Pesantren Nurul Khidmah Lamongan.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:flex sm:flex-wrap gap-2 shrink-0 w-full sm:w-auto">
                <a href="{{ route('home') }}" target="_blank" class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white text-sm font-semibold rounded-xl border border-white/25 transition">
                    Lihat Website Publik ↗
                </a>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Aksi Cepat Pengelola Website Yayasan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <a href="{{ route('admin.news.create') }}" class="min-h-12 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-800 dark:text-emerald-300 px-4 py-3.5 rounded-xl font-semibold transition border border-emerald-200 dark:border-emerald-800">
                <span class="text-lg">📰</span> + Tulis Berita Baru
            </a>
            <a href="{{ route('settings.index') }}" class="min-h-12 flex items-center gap-3 bg-sky-50 dark:bg-sky-500/10 hover:bg-sky-100 text-sky-800 dark:text-sky-300 px-4 py-3.5 rounded-xl font-semibold transition border border-sky-200 dark:border-sky-800">
                <span class="text-lg">🖼️</span> Ganti Foto, Banner &amp; Ikon
            </a>
            <a href="{{ route('admin.facilities.index') }}" class="min-h-12 flex items-center gap-3 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 text-amber-800 dark:text-amber-300 px-4 py-3.5 rounded-xl font-semibold transition border border-amber-200 dark:border-amber-800">
                <span class="text-lg">🕌</span> Kelola Foto Fasilitas
            </a>
            <a href="{{ route('admin.programs.index') }}" class="min-h-12 flex items-center gap-3 bg-purple-50 dark:bg-purple-500/10 hover:bg-purple-100 text-purple-800 dark:text-purple-300 px-4 py-3.5 rounded-xl font-semibold transition border border-purple-200 dark:border-purple-800">
                <span class="text-lg">📚</span> Program &amp; Brosur Biaya
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs font-semibold uppercase">Total Berita</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalNews ?? 0 }}</p>
            <p class="text-xs font-medium text-emerald-600 mt-1">Artikel Terpublikasi</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-sky-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs font-semibold uppercase">Galeri Fasilitas</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalFacilities ?? 0 }}</p>
            <p class="text-xs font-medium text-sky-600 mt-1">Sarana Pesantren</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs font-semibold uppercase">Program Pendidikan</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeProgramsCount ?? 0 }}</p>
            <p class="text-xs font-medium text-purple-600 mt-1">Ditampilkan di Website</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs font-semibold uppercase">Rincian Biaya</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $totalFees ?? 0 }}</p>
            <p class="text-xs font-medium text-amber-600 mt-1">Komponen Informasi Biaya</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm sm:text-base">Publikasi Berita Terbaru</h3>
            <a href="{{ route('admin.news.index') }}" class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:underline">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($latestNews ?? [] as $item)
                <div class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <div class="min-w-0">
                        <p class="font-semibold text-sm text-gray-900 dark:text-gray-100 truncate">{{ $item->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $item->category ?? 'Kegiatan' }} • {{ optional($item->published_at ?? $item->created_at)->format('d M Y') }}</p>
                    </div>
                    <a href="{{ route('admin.news.edit', $item->id) }}" class="shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100">
                        Edit
                    </a>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-gray-500">Belum ada berita dipublikasikan.</div>
            @endforelse
        </div>
    </div>

@else
    {{-- ═══ TAMPILAN DASHBOARD PENGURUS AKADEMIK & SUPER ADMIN ═══ --}}
    <div class="mb-8 bg-gradient-to-br from-emerald-700 via-emerald-800 to-emerald-950 dark:from-emerald-900 dark:via-emerald-950 dark:to-slate-900 rounded-2xl shadow-lg overflow-hidden">
        <div class="relative px-5 py-6 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5">
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
                    <span class="text-emerald-300 text-xs flex items-center gap-1 break-all">
                        {{ auth()->user()->email }}
                    </span>
                </div>
            </div>

            <div class="relative z-10 grid grid-cols-1 sm:flex sm:flex-wrap gap-2 shrink-0 w-full sm:w-auto">
                <a href="{{ route('admin.registrations.index') }}"
                   class="min-h-12 sm:min-h-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white text-sm font-semibold rounded-xl border border-white/20 transition backdrop-blur-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Verifikasi PPDB ({{ $newRegistrantsCount ?? 0 }})
                </a>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Aksi Cepat Akademik &amp; SIAKAD</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <a href="{{ route('admin.santri.create') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 bg-purple-50 dark:bg-purple-500/10 hover:bg-purple-100 text-purple-700 dark:text-purple-300 px-4 py-3 rounded-xl font-semibold transition border border-purple-200 dark:border-purple-800">
                + Tambah Santri
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 px-4 py-3 rounded-xl font-semibold transition border border-indigo-200 dark:border-indigo-800">
                + Tambah Pengajar
            </a>
            <a href="{{ route('admin.schedules.create') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl font-semibold transition border border-emerald-200 dark:border-emerald-800">
                + Plotting Jadwal
            </a>
            <a href="{{ route('admin.promotions.index') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-xl font-semibold transition border border-amber-200 dark:border-amber-800">
                🎓 Kenaikan &amp; Kelulusan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 sm:p-6 border-l-4 border-amber-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs sm:text-sm font-semibold uppercase">Pendaftar Pending</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $newRegistrantsCount ?? 0 }}</p>
            <p class="text-xs sm:text-sm font-medium text-amber-600 mt-1">Butuh Tindakan</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 sm:p-6 border-l-4 border-emerald-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs sm:text-sm font-semibold uppercase">Santri Aktif SIAKAD</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeSantriCount ?? 0 }}</p>
            <p class="text-xs sm:text-sm font-medium text-emerald-600 mt-1">Diterima PPDB: {{ $totalSantri ?? 0 }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 sm:p-6 border-l-4 border-blue-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs sm:text-sm font-semibold uppercase">Program Aktif</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeProgramsCount ?? 0 }}</p>
            <p class="text-xs sm:text-sm font-medium text-blue-600 mt-1">Berjalan</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 sm:p-6 border-l-4 border-purple-500">
            <h3 class="text-gray-500 dark:text-gray-300 text-xs sm:text-sm font-semibold uppercase">Tahun Ajaran</h3>
            <p class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mt-2">{{ $activeYear?->nama_tahun_ajaran ?? '-' }}</p>
            <p class="text-xs sm:text-sm font-medium text-purple-600 mt-1">{{ $activeYear?->semester ?? 'Belum Aktif' }}</p>
        </div>
    </div>
@endif

@endsection
