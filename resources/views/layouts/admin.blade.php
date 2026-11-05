<!DOCTYPE html>
<html lang="id"
      x-data="{ sidebarOpen: false, isDarkMode: document.documentElement.classList.contains('dark') }"
      x-init="
          const saved = localStorage.getItem('theme');
          if (saved === 'dark') { isDarkMode = true; }
          else if (saved === 'light') { isDarkMode = false; }
          else {
              isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
              localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
          }
      "
      :class="{ 'dark': isDarkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Sistem') - Pondok Pesantren Nurul Khidmah</title>
    @include('partials.site-favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        /* Mencegah iOS Safari / Chrome Mobile auto-zoom saat staf/wali mengetuk kolom input di HP */
        @media (max-width: 767px) {
            input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            select,
            textarea {
                font-size: 16px !important;
            }
        }
    </style>
    <script>
        (function () {
            const theme = localStorage.getItem('theme');
            const isDark = theme === 'dark' || (theme !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased text-gray-900 dark:text-gray-100 transition-colors duration-200">
    {{-- Gunakan h-dvh (Dynamic Viewport Height) agar bar alamat browser HP tidak menutupi tombol Logout di bawah --}}
    <div class="flex h-dvh overflow-hidden">

        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-20 bg-black/55 lg:hidden"
             @click="sidebarOpen = false" x-cloak></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 w-72 sm:w-64 h-dvh bg-emerald-800 dark:bg-emerald-950 text-white transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:inset-auto flex flex-col shadow-xl border-r border-emerald-900 dark:border-emerald-900">

            <!-- Brand -->
            <div class="flex items-center justify-between lg:justify-center h-16 bg-emerald-900 dark:bg-emerald-900 font-bold text-base sm:text-lg px-4 text-center shadow-sm shrink-0">
                <span class="truncate">
                    @auth
                        @if(auth()->user()->isKepalaSekolah())
                            Monitoring Madrasah
                        @elseif(auth()->user()->isPimpinan())
                            Eksekutif Yayasan
                        @elseif(auth()->user()->isSantri())
                            Portal Wali Santri
                        @elseif(auth()->user()->isTenagaPengajar())
                            Portal Guru / Ustaz
                        @elseif(auth()->user()->isContentAdmin())
                            CMS Yayasan Nurul Khidmah
                        @else
                            SIAKAD Nurul Khidmah
                        @endif
                    @else
                        SIAKAD Nurul Khidmah
                    @endauth
                </span>
                <button type="button" @click="sidebarOpen = false" aria-label="Tutup menu navigasi"
                        class="lg:hidden min-h-11 min-w-11 inline-flex items-center justify-center rounded-lg text-emerald-200 hover:text-white hover:bg-emerald-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1.5 px-3">
                    @auth
                        @if(!auth()->user()->isSantri() && !auth()->user()->isPimpinan())
                            <li>
                                <a href="{{ route('dashboard') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    Dashboard
                                </a>
                            </li>
                        @endif

                        {{-- 0. Menu Eksekutif Pimpinan Pesantren (Khusus Pimpinan & Super Admin — FR-13) --}}
                        @if(auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin())
                            <li class="pt-3 pb-1 px-4 text-xs font-semibold text-amber-300 uppercase tracking-wider">
                                Eksekutif Pimpinan
                            </li>
                            <li>
                                <a href="{{ route('pimpinan.dashboard') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('pimpinan.dashboard') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                    Dashboard Eksekutif
                                </a>
                            </li>
                            @if(auth()->user()->isPimpinan())
                            <li>
                                <a href="{{ route('pimpinan.export-pdf') }}" target="_blank" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 hover:bg-emerald-700 text-emerald-100 hover:text-white">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Cetak Track Record PDF
                                </a>
                            </li>
                            @endif
                        @endif

                        {{-- 1. Master Data SIAKAD & PPDB (Pengurus Akademik, Super Admin, dan Kepala Sekolah) --}}
                        @if(auth()->user()->isPengurusAkademik() || auth()->user()->isKepalaSekolah())
                            <li class="pt-3 pb-1 px-4 text-xs font-semibold text-emerald-300 uppercase tracking-wider">
                                {{ auth()->user()->isKepalaSekolah() ? 'Data Akademik Madrasah' : 'SIAKAD & PPDB Santri' }}
                            </li>
                            @if(auth()->user()->isPengurusAkademik())
                            <li>
                                <a href="{{ route('admin.registrations.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.registrations.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    Pendaftar PPDB
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.academic-years.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.academic-years.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Tahun Ajaran
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.subjects.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.subjects.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    Mata Pelajaran
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('admin.teachers.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.teachers.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Data Pengajar
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.classes.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.classes.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Data Kelas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.santri.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.santri.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Data Santri
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.schedules.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.schedules.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Jadwal Pelajaran
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.promotions.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.promotions.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    Kenaikan &amp; Kelulusan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.imports.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.imports.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Migrasi &amp; Import Data
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('wali.dashboard') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('wali.*') || request()->routeIs('santri.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Pantau Portal Wali
                                </a>
                            </li>
                        @endif

                        {{-- 2. Menu Akademik & Pemantauan Guru (Tenaga Pengajar, Pengurus Akademik, Kepala Sekolah, & Super Admin) --}}
                        @if(auth()->user()->isTenagaPengajar() || auth()->user()->isPengurusAkademik() || auth()->user()->isKepalaSekolah())
                            <li class="pt-3 pb-1 px-4 text-xs font-semibold text-emerald-300 uppercase tracking-wider">
                                {{ auth()->user()->isKepalaSekolah() ? 'Supervisi Guru & Rapor' : 'Menu Akademik & Rapor' }}
                            </li>
                            <li>
                                <a href="{{ route('teacher.attendance.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('teacher.attendance.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    {{ auth()->user()->isKepalaSekolah() ? 'Pantau Absensi Guru' : 'Absensi Santri' }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('teacher.grades.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('teacher.grades.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    {{ auth()->user()->isKepalaSekolah() ? 'Supervisi Nilai Guru' : 'Pengolahan Nilai' }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.reports.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ auth()->user()->isKepalaSekolah() ? 'Validasi Leger & Rapor' : 'Cetak Rekap & Rapor PDF' }}
                                </a>
                            </li>
                        @endif

                        {{-- 3. Konten Website Yayasan (Staf Admin Yayasan, Super Admin, & Program/Biaya untuk Pengurus Akademik) --}}
                        @if(auth()->user()->canManageWebsite() || auth()->user()->canManageProgramsAndFees())
                            <li class="pt-3 pb-1 px-4 text-xs font-semibold text-sky-300 uppercase tracking-wider">
                                Konten Website Yayasan
                            </li>
                            @if(auth()->user()->canManageWebsite())
                                <li>
                                    <a href="{{ route('admin.news.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.news.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                                        Berita &amp; Artikel
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.facilities.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.facilities.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        Fasilitas Pondok
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('settings.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('settings.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Foto, Banner &amp; Ikon Situs
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->canManageProgramsAndFees())
                                <li>
                                    <a href="{{ route('admin.programs.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.programs.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        Program Pendidikan
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.fees.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.fees.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Biaya Pendidikan
                                    </a>
                                </li>
                            @endif
                        @endif

                        {{-- 4. Menu Wali Santri (FR-11) --}}
                        @if(auth()->user()->isSantri())
                            <li class="pt-2 pb-1 px-4 text-xs font-semibold text-teal-300 uppercase tracking-wider">
                                Portal Wali Santri
                            </li>
                            <li>
                                <a href="{{ route('wali.dashboard') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('wali.dashboard') || request()->routeIs('santri.dashboard') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-teal-700 text-teal-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    Dashboard Wali
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('wali.attendance') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('wali.attendance') || request()->routeIs('santri.attendance') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-teal-700 text-teal-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    Presensi Ananda
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('wali.grades') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('wali.grades') || request()->routeIs('santri.grades') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-teal-700 text-teal-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    Nilai &amp; Rapor Ananda
                                </a>
                            </li>
                        @endif

                        {{-- 5. Sistem & Keamanan (Khusus Super Admin) --}}
                        @if(auth()->user()->isSuperAdmin())
                            <li class="pt-3 pb-1 px-4 text-xs font-semibold text-emerald-300 uppercase tracking-wider">
                                Sistem &amp; Keamanan
                            </li>
                            <li>
                                <a href="{{ route('admin.users.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    Pengguna &amp; Hak Akses
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.activity-logs.index') }}" class="min-h-11 flex items-center gap-3 px-4 py-2.5 rounded-lg transition duration-200 {{ request()->routeIs('admin.activity-logs.*') ? 'bg-emerald-700 text-white font-semibold' : 'hover:bg-emerald-700 text-emerald-100 hover:text-white' }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Log Aktivitas
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </nav>



            <!-- Bottom profile actions -->
            <div class="p-3 bg-emerald-900 dark:bg-emerald-900 shadow-inner space-y-1.5 shrink-0">
                <button type="button" @click="isDarkMode = !isDarkMode; localStorage.setItem('theme', isDarkMode ? 'dark' : 'light')" class="min-h-11 w-full flex items-center justify-between px-4 py-2 rounded-lg text-emerald-200 hover:text-white hover:bg-emerald-800 dark:hover:bg-emerald-800 transition duration-200">
                    <div class="flex items-center gap-3 text-sm font-medium">
                        <svg x-show="!isDarkMode" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="isDarkMode" x-cloak class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span x-text="isDarkMode ? 'Mode Terang' : 'Mode Gelap'"></span>
                    </div>
                </button>
                <form id="logout-form-admin" method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button type="submit" class="min-h-11 w-full flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium text-emerald-200 hover:text-white hover:bg-emerald-800 dark:hover:bg-emerald-800 transition duration-200">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Keluar (Logout)
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            {{-- Toolbar desktop --}}
            @auth
                <div class="hidden lg:flex shrink-0 items-center justify-end gap-4 px-8 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="text-right leading-tight">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ auth()->user()->email }}</p>
                    </div>
                    @include('partials.admin-role-badge', ['user' => auth()->user()])
                    @if(auth()->user()->avatarUrl())
                        <img src="{{ auth()->user()->avatarUrl() }}"
                             alt="{{ auth()->user()->name }}"
                             class="h-9 w-9 rounded-full object-cover ring-2 ring-emerald-300 dark:ring-emerald-700 shadow">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-700 text-sm font-bold text-white ring-2 ring-emerald-300 dark:ring-emerald-700">
                            {{ auth()->user()->initials() }}
                        </span>
                    @endif
                </div>
            @endauth

            <!-- Header (Visible only on mobile/tablet < lg) -->
            <header class="shrink-0 flex items-center justify-between gap-2 min-h-16 py-2 lg:hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-3 sm:px-4 shadow-sm transition-colors duration-200">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <button type="button" @click="sidebarOpen = true" aria-label="Buka menu navigasi"
                            class="min-h-12 min-w-12 -ml-1 inline-flex items-center justify-center rounded-xl text-emerald-800 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm sm:text-base font-bold text-emerald-800 dark:text-emerald-400 truncate">
                            @yield('title', 'Nurul Khidmah')
                        </p>
                        @auth
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->name }}</p>
                        @endauth
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    @auth
                        <div class="hidden sm:block">
                            @include('partials.admin-role-badge', ['user' => auth()->user()])
                        </div>
                    @endauth
                    <button type="button" @click="isDarkMode = !isDarkMode; localStorage.setItem('theme', isDarkMode ? 'dark' : 'light')"
                            aria-label="Ubah tema tampilan"
                            class="min-h-12 min-w-12 inline-flex items-center justify-center rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                        <svg x-show="!isDarkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="isDarkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900 p-3.5 sm:p-5 lg:p-8 transition-colors duration-200">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.image-cropper-modal')
    @livewireScripts
</body>
</html>
