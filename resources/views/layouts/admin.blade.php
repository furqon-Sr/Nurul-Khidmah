<!DOCTYPE html>
<html lang="id"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
          isDarkMode: document.documentElement.classList.contains('dark'),
          toggleSidebarCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed ? 'true' : 'false');
              this.$nextTick(() => {
                  this.$refs.sidebarNav?.querySelector('.nav-active')?.scrollIntoView({ block: 'nearest' });
              });
          },
          setTheme(dark) {
              this.isDarkMode = dark;
              localStorage.setItem('theme', dark ? 'dark' : 'light');
              document.documentElement.classList.toggle('dark', dark);
              if (window.$flux) {
                  try { window.$flux.appearance = dark ? 'dark' : 'light'; } catch(e) {}
              }
          }
      }"
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
    @include('partials.fonts')    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
</head>
<body class="bg-gray-100 dark:bg-gray-950 font-sans antialiased text-gray-900 dark:text-gray-100 transition-colors duration-200">
    {{-- Gunakan h-dvh (Dynamic Viewport Height) agar bar alamat browser HP tidak menutupi area bawah --}}
    <div class="flex h-dvh overflow-hidden">

        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-20 bg-black/50 lg:hidden"
             @click="sidebarOpen = false" x-cloak></div>

        <!-- Sidebar (264px expanded <-> 72px collapsed pada desktop) -->
        <aside :class="[
                   sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                   sidebarCollapsed ? 'lg:w-[72px]' : 'lg:w-[264px]'
               ]"
               class="fixed inset-y-0 left-0 z-30 w-[264px] shrink-0 h-dvh bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 transition-[width,transform] duration-200 ease-out lg:static lg:translate-x-0 lg:inset-auto flex flex-col shadow-xl lg:shadow-none border-r border-gray-200 dark:border-gray-800">

            <!-- Brand -->
            <div :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : 'lg:justify-start lg:px-4'"
                 class="flex items-center justify-between h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white font-bold text-sm px-4 shrink-0 gap-2.5">
                <div class="flex items-center gap-2.5 min-w-0" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-xl bg-primary-500 text-white text-xs font-extrabold tracking-tight">NK</span>
                    <span class="truncate font-bold text-sm text-gray-900 dark:text-white" :class="sidebarCollapsed ? 'lg:hidden' : ''">
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
                </div>
                <button type="button" @click="sidebarOpen = false" aria-label="Tutup menu navigasi"
                        class="lg:hidden min-h-10 min-w-10 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav x-ref="sidebarNav"
                 x-init="$nextTick(() => { $el.querySelector('.nav-active')?.scrollIntoView({ block: 'nearest' }); })"
                 aria-label="Navigasi Utama"
                 class="flex-1 overflow-y-auto overflow-x-hidden py-2.5 px-2.5">
                <ul class="sidebar-nav-list space-y-0.5">
                    @auth
                        @if(!auth()->user()->isSantri() && !auth()->user()->isPimpinan())
                            <li>
                                <a href="{{ route('dashboard') }}"
                                   title="Dashboard"
                                   :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                   class="sidebar-item {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                                    <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Dashboard</span>
                                </a>
                            </li>
                        @endif

                        {{-- 0. Menu Eksekutif Pimpinan Pesantren (Khusus Pimpinan & Super Admin — FR-13) --}}
                        @if(auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin())
                            <li x-data="{ open: false }" x-init="open = $el.querySelector('.nav-active') !== null" class="pt-1.5">
                                <div x-show="sidebarCollapsed" x-cloak class="hidden lg:block my-1.5 mx-2 border-t border-gray-100 dark:border-gray-800" aria-hidden="true"></div>
                                <button type="button"
                                        @click="open = !open"
                                        :aria-expanded="open"
                                        :class="sidebarCollapsed ? 'lg:hidden' : ''"
                                        class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 hover:bg-gray-100/70 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800/60 dark:hover:text-gray-300 transition cursor-pointer">
                                    <span class="truncate text-left">Eksekutif Pimpinan</span>
                                    <svg class="size-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <ul x-show="open || (sidebarCollapsed && !sidebarOpen)" x-cloak x-collapse class="sidebar-nav-list mt-0.5 space-y-0.5">
                                    <li>
                                        <a href="{{ route('pimpinan.dashboard') }}"
                                           title="Dashboard Eksekutif"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('pimpinan.dashboard') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Dashboard Eksekutif</span>
                                        </a>
                                    </li>
                                    @if(auth()->user()->isPimpinan())
                                        <li>
                                            <a href="{{ route('pimpinan.export-pdf') }}"
                                               target="_blank"
                                               title="Cetak Track Record PDF"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Cetak Track Record PDF</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- 1. Master Data SIAKAD & PPDB (Pengurus Akademik, Super Admin, dan Kepala Sekolah) --}}
                        @if(auth()->user()->isPengurusAkademik() || auth()->user()->isKepalaSekolah())
                            <li x-data="{ open: false }" x-init="open = $el.querySelector('.nav-active') !== null" class="pt-1.5">
                                <div x-show="sidebarCollapsed" x-cloak class="hidden lg:block my-1.5 mx-2 border-t border-gray-100 dark:border-gray-800" aria-hidden="true"></div>
                                <button type="button"
                                        @click="open = !open"
                                        :aria-expanded="open"
                                        :class="sidebarCollapsed ? 'lg:hidden' : ''"
                                        class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 hover:bg-gray-100/70 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800/60 dark:hover:text-gray-300 transition cursor-pointer">
                                    <span class="truncate text-left">{{ auth()->user()->isKepalaSekolah() ? 'Data Akademik Madrasah' : 'SIAKAD & PPDB Santri' }}</span>
                                    <svg class="size-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <ul x-show="open || (sidebarCollapsed && !sidebarOpen)" x-cloak x-collapse class="sidebar-nav-list mt-0.5 space-y-0.5">
                                    @if(auth()->user()->isPengurusAkademik())
                                        <li>
                                            <a href="{{ route('admin.registrations.index') }}"
                                               title="Pendaftar PPDB"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('admin.registrations.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Pendaftar PPDB</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.academic-years.index') }}"
                                               title="Tahun Ajaran"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('admin.academic-years.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Tahun Ajaran</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.subjects.index') }}"
                                               title="Mata Pelajaran"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('admin.subjects.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Mata Pelajaran</span>
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="{{ route('admin.teachers.index') }}"
                                           title="Data Pengajar"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.teachers.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Data Pengajar</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.classes.index') }}"
                                           title="Data Kelas"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.classes.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Data Kelas</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.santri.index') }}"
                                           title="Data Santri"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.santri.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Data Santri</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.schedules.index') }}"
                                           title="Jadwal Pelajaran"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.schedules.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Jadwal Pelajaran</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.promotions.index') }}"
                                           title="Kenaikan & Kelulusan"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.promotions.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Kenaikan &amp; Kelulusan</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.imports.index') }}"
                                           title="Migrasi & Import Data"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.imports.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Migrasi &amp; Import Data</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('wali.dashboard') }}"
                                           title="Pantau Portal Wali"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('wali.*') || request()->routeIs('santri.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Pantau Portal Wali</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- 2. Menu Akademik & Pemantauan Guru (Tenaga Pengajar, Pengurus Akademik, Kepala Sekolah, & Super Admin) --}}
                        @if(auth()->user()->isTenagaPengajar() || auth()->user()->isPengurusAkademik() || auth()->user()->isKepalaSekolah())
                            <li x-data="{ open: {{ auth()->user()->isTenagaPengajar() ? 'true' : 'false' }} }" x-init="open = open || $el.querySelector('.nav-active') !== null" class="pt-1.5">
                                <div x-show="sidebarCollapsed" x-cloak class="hidden lg:block my-1.5 mx-2 border-t border-gray-100 dark:border-gray-800" aria-hidden="true"></div>
                                <button type="button"
                                        @click="{{ auth()->user()->isTenagaPengajar() ? '' : 'open = !open' }}"
                                        :aria-expanded="open"
                                        :class="sidebarCollapsed ? 'lg:hidden' : ''"
                                        class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 hover:bg-gray-100/70 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800/60 dark:hover:text-gray-300 transition cursor-pointer">
                                    <span class="truncate text-left">{{ auth()->user()->isKepalaSekolah() ? 'Supervisi Guru & Rapor' : 'Menu Akademik & Rapor' }}</span>
                                    @unless(auth()->user()->isTenagaPengajar())
                                    <svg class="size-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    @endunless
                                </button>
                                <ul x-show="open || (sidebarCollapsed && !sidebarOpen)" x-cloak x-collapse class="sidebar-nav-list mt-0.5 space-y-0.5">
                                    <li>
                                        <a href="{{ route('teacher.attendance.index') }}"
                                           title="{{ auth()->user()->isKepalaSekolah() ? 'Pantau Absensi Guru' : 'Absensi Santri' }}"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('teacher.attendance.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ auth()->user()->isKepalaSekolah() ? 'Pantau Absensi Guru' : 'Absensi Santri' }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('teacher.grades.index') }}"
                                           title="{{ auth()->user()->isKepalaSekolah() ? 'Supervisi Nilai Guru' : 'Pengolahan Nilai' }}"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('teacher.grades.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ auth()->user()->isKepalaSekolah() ? 'Supervisi Nilai Guru' : 'Pengolahan Nilai' }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.reports.index') }}"
                                           title="{{ auth()->user()->isKepalaSekolah() ? 'Validasi Leger & Rapor' : 'Cetak Rekap & Rapor PDF' }}"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ auth()->user()->isKepalaSekolah() ? 'Validasi Leger & Rapor' : 'Cetak Rekap & Rapor PDF' }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- 3. Konten Website Yayasan (Staf Admin Yayasan, Super Admin, & Program/Biaya untuk Pengurus Akademik) --}}
                        @if(auth()->user()->canManageWebsite() || auth()->user()->canManageProgramsAndFees())
                            <li x-data="{ open: false }" x-init="open = $el.querySelector('.nav-active') !== null" class="pt-1.5">
                                <div x-show="sidebarCollapsed" x-cloak class="hidden lg:block my-1.5 mx-2 border-t border-gray-100 dark:border-gray-800" aria-hidden="true"></div>
                                <button type="button"
                                        @click="open = !open"
                                        :aria-expanded="open"
                                        :class="sidebarCollapsed ? 'lg:hidden' : ''"
                                        class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 hover:bg-gray-100/70 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800/60 dark:hover:text-gray-300 transition cursor-pointer">
                                    <span class="truncate text-left">Konten Website Yayasan</span>
                                    <svg class="size-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <ul x-show="open || (sidebarCollapsed && !sidebarOpen)" x-cloak x-collapse class="sidebar-nav-list mt-0.5 space-y-0.5">
                                    @if(auth()->user()->canManageWebsite())
                                        <li>
                                            <a href="{{ route('admin.news.index') }}"
                                               title="Berita & Artikel"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('admin.news.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Berita &amp; Artikel</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.facilities.index') }}"
                                               title="Fasilitas Pondok"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('admin.facilities.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Fasilitas Pondok</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('settings.index') }}"
                                               title="Foto, Banner & Ikon Situs"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('settings.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Foto, Banner &amp; Ikon Situs</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->canManageProgramsAndFees())
                                        <li>
                                            <a href="{{ route('admin.programs.index') }}"
                                               title="Program Pendidikan"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('admin.programs.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Program Pendidikan</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.fees.index') }}"
                                               title="Biaya Pendidikan"
                                               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                               class="sidebar-item {{ request()->routeIs('admin.fees.*') ? 'nav-active' : '' }}">
                                                <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Biaya Pendidikan</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- 4. Menu Wali Santri (FR-11) --}}
                        @if(auth()->user()->isSantri())
                            <li x-data="{ open: false }" x-init="open = $el.querySelector('.nav-active') !== null" class="pt-1.5">
                                <div x-show="sidebarCollapsed" x-cloak class="hidden lg:block my-1.5 mx-2 border-t border-gray-100 dark:border-gray-800" aria-hidden="true"></div>
                                <button type="button"
                                        @click="open = !open"
                                        :aria-expanded="open"
                                        :class="sidebarCollapsed ? 'lg:hidden' : ''"
                                        class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 hover:bg-gray-100/70 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800/60 dark:hover:text-gray-300 transition cursor-pointer">
                                    <span class="truncate text-left">Portal Wali Santri</span>
                                    <svg class="size-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <ul x-show="open || (sidebarCollapsed && !sidebarOpen)" x-cloak x-collapse class="sidebar-nav-list mt-0.5 space-y-0.5">
                                    <li>
                                        <a href="{{ route('wali.dashboard') }}"
                                           title="Dashboard Wali"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('wali.dashboard') || request()->routeIs('santri.dashboard') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Dashboard Wali</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('wali.attendance') }}"
                                           title="Presensi Ananda"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('wali.attendance') || request()->routeIs('santri.attendance') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Presensi Ananda</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('wali.grades') }}"
                                           title="Nilai & Rapor Ananda"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('wali.grades') || request()->routeIs('santri.grades') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Nilai &amp; Rapor Ananda</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- 5. Sistem & Keamanan (Khusus Super Admin) --}}
                        @if(auth()->user()->isSuperAdmin())
                            <li x-data="{ open: false }" x-init="open = $el.querySelector('.nav-active') !== null" class="pt-1.5">
                                <div x-show="sidebarCollapsed" x-cloak class="hidden lg:block my-1.5 mx-2 border-t border-gray-100 dark:border-gray-800" aria-hidden="true"></div>
                                <button type="button"
                                        @click="open = !open"
                                        :aria-expanded="open"
                                        :class="sidebarCollapsed ? 'lg:hidden' : ''"
                                        class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 hover:bg-gray-100/70 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-gray-800/60 dark:hover:text-gray-300 transition cursor-pointer">
                                    <span class="truncate text-left">Sistem &amp; Keamanan</span>
                                    <svg class="size-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <ul x-show="open || (sidebarCollapsed && !sidebarOpen)" x-cloak x-collapse class="sidebar-nav-list mt-0.5 space-y-0.5">
                                    <li>
                                        <a href="{{ route('admin.users.index') }}"
                                           title="Pengguna & Hak Akses"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Pengguna &amp; Hak Akses</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.activity-logs.index') }}"
                                           title="Log Aktivitas"
                                           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                                           class="sidebar-item {{ request()->routeIs('admin.activity-logs.*') ? 'nav-active' : '' }}">
                                            <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Log Aktivitas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>
            </nav>

            <!-- Desktop Sidebar Collapse Footer -->
            <div class="hidden lg:flex shrink-0 border-t border-gray-100 dark:border-gray-800 p-2">
                <button type="button"
                        @click="toggleSidebarCollapse()"
                        :aria-expanded="(!sidebarCollapsed).toString()"
                        :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                        :title="sidebarCollapsed ? 'Perluas sidebar (264px)' : 'Ciutkan sidebar (72px)'"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-2.5'"
                        class="flex h-9 w-full items-center rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100/80 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800/70 dark:hover:text-gray-100 transition cursor-pointer">
                    <span class="truncate" :class="sidebarCollapsed ? 'hidden' : ''">Ciutkan Menu</span>
                    <svg class="size-[18px] shrink-0 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            {{-- Header ringkas: toggle sidebar · breadcrumb · periode · tema · profil --}}
            @php($activePeriod = \App\Models\TahunAjaran::getAktif())
            <header class="shrink-0 flex h-16 items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex min-w-0 items-center gap-2.5">
                    {{-- Mobile Hamburger --}}
                    <button type="button" @click="sidebarOpen = true" aria-label="Buka menu navigasi"
                            class="lg:hidden -ml-2 inline-flex size-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>

                    {{-- Desktop Sidebar Collapse Toggle (264px <-> 72px) --}}
                    <button type="button"
                            @click="toggleSidebarCollapse()"
                            :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                            :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                            class="hidden lg:inline-flex -ml-2 size-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100 transition cursor-pointer">
                        <svg class="size-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4v16"/>
                        </svg>
                    </button>

                    <nav aria-label="Breadcrumb" class="min-w-0">
                        <ol class="flex min-w-0 items-center gap-2 text-sm">
                            <li class="hidden sm:block"><a href="{{ route('dashboard') }}" class="font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Beranda</a></li>
                            <li class="hidden sm:block text-gray-400" aria-hidden="true">/</li>
                            <li class="truncate font-bold text-gray-900 dark:text-white" aria-current="page">@yield('title', 'Nurul Khidmah')</li>
                        </ol>
                    </nav>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @if($activePeriod)
                        <span class="hidden md:inline-flex h-9 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" title="Periode akademik aktif">
                            <svg class="size-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $activePeriod->nama_tahun_ajaran }}
                            <span class="font-medium text-gray-500">· {{ $activePeriod->semester }}</span>
                        </span>
                    @endif

                    <x-theme-toggle size="sm" />

                    @auth
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu" aria-label="Menu profil"
                                    class="flex items-center gap-2 rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                @if(auth()->user()->avatarUrl())
                                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="size-8 rounded-full object-cover">
                                @else
                                    <span class="avatar avatar-primary size-8 text-xs">{{ auth()->user()->initials() }}</span>
                                @endif
                                <svg class="hidden sm:block size-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity role="menu"
                                 class="absolute right-0 z-40 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                <div class="p-3">
                                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-gray-600 dark:text-gray-400">{{ auth()->user()->email }}</p>
                                    <div class="mt-2">@include('partials.admin-role-badge', ['user' => auth()->user()])</div>
                                </div>
                                <div class="border-t border-gray-200 pt-2 dark:border-gray-700">
                                    <form method="POST" action="{{ url('/logout') }}">
                                        @csrf
                                        <button type="submit" role="menuitem" class="flex min-h-10 w-full items-center gap-2 rounded-lg px-3 text-sm font-semibold text-gray-700 hover:bg-error-50 hover:text-error-600 dark:text-gray-300 dark:hover:bg-error-500/10 dark:hover:text-error-400">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-950 p-4 sm:p-6 lg:p-8 transition-colors duration-200">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.image-cropper-modal')
    @stack('scripts')
    @livewireScripts
</body>
</html>
