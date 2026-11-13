@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@if(auth()->user()->isTenagaPengajar())
    {{-- ═══ TAMPILAN DASHBOARD KHUSUS USTAZ / PENGAJAR ═══ --}}
    <x-page-heading title="Dashboard Pengajar" :description="'Jadwal dan aktivitas mengajar Anda pada periode aktif. Selamat datang, '.auth()->user()->name.'.'">
        <x-slot:actions>
            <a href="{{ route('teacher.attendance.index') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-primary-600 text-white hover:bg-primary-700 border-transparent">Input Presensi</a>
            <a href="{{ route('teacher.grades.index') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-white text-gray-700 hover:bg-gray-50 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800">Input Nilai</a>
            <a href="{{ route('admin.reports.index') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-white text-gray-700 hover:bg-gray-50 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800">Cetak Rapor &amp; Rekap</a>
        </x-slot:actions>
    </x-page-heading>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Jadwal Mengajar</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $mySchedules->count() }}</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">Sesi Pelajaran</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Kelas Diampu</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $myClassesCount }}</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">Kelompok Belajar</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Mata Pelajaran</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $mySubjectsCount }}</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">Bidang Studi</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Santri Diajar</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $mySantriCount }}</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">Santri Aktif</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="xl:col-span-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan pengajaran</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Beban ajar pada periode aktif</p>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3v18h18M7 16l4-5 3 3 5-7"/></svg>
                </span>
            </div>
            <div class="h-64"><canvas id="teacherWorkloadChart" role="img" aria-label="Grafik ringkasan beban pengajaran"></canvas></div>
        </div>

        <div class="bg-indigo-600 rounded-2xl shadow-sm p-6 text-white flex flex-col justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-100">Kelas wali</p>
                <p class="text-3xl font-bold mt-2">{{ ($myWaliKelas ?? null)?->nama_kelas ?? '—' }}</p>
                <p class="text-sm text-indigo-100 mt-2">{{ isset($myWaliKelas) && $myWaliKelas ? 'Pantau kebutuhan kelas Anda.' : 'Belum ada kelas perwalian.' }}</p>
            </div>
            <a href="{{ route('teacher.attendance.index') }}" class="mt-6 inline-flex items-center justify-between rounded-xl bg-white/15 hover:bg-white/25 px-4 py-3 text-sm font-semibold transition">
                Presensi kelas
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <h3 id="teacher-schedule-heading" class="font-bold text-gray-800 dark:text-gray-100 text-base">Jadwal Mengajar Anda</h3>
            <span class="text-xs text-gray-600">{{ $activeYear?->nama_tahun_ajaran ?? 'Semester Aktif' }}</span>
        </div>

        <div tabindex="0" role="region" aria-labelledby="teacher-schedule-heading" class="overflow-x-auto">
            <table class="table-builder min-w-full border-separate border-spacing-0">
                <thead>
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Hari &amp; Waktu</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Kelas</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Mata Pelajaran</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Ruang</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Aksi Cepat</th>
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
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
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
                        <td colspan="5" class="px-6 py-8 text-center text-gray-600 text-sm">
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
    <x-page-heading title="Monitoring Madrasah" :description="'Kepatuhan guru dan capaian akademik madrasah. Selamat datang, '.auth()->user()->name.'.'">
        <x-slot:actions>
            <a href="{{ route('teacher.attendance.index') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-primary-600 text-white hover:bg-primary-700 border-transparent">Pantau Presensi Guru</a>
            <a href="{{ route('teacher.grades.index') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-white text-gray-700 hover:bg-gray-50 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800">Pantau Input Nilai</a>
            <a href="{{ route('admin.reports.index') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-white text-gray-700 hover:bg-gray-50 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800">Leger &amp; Rekap Rapor</a>
        </x-slot:actions>
    </x-page-heading>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Kepatuhan Nilai Guru</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $complianceRate }}%</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">{{ $gradedSchedulesCount }} dari {{ $totalSchedules }} Kelas Dinilai</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Guru Aktif</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $totalTeachers }}</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">Tenaga Pengajar</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Santri Aktif</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $activeSantriCount }}</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">Dalam {{ $totalClasses }} Rombel Aktif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Tahun Ajaran Aktif</h3>
            <p class="text-2xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $activeYear?->nama_tahun_ajaran ?? '-' }}</p>
            <p class="text-xs text-gray-700 dark:text-gray-300 mt-2 font-medium">{{ $activeYear?->semester ?? 'Semester Belum Diset' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="xl:col-span-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Kepatuhan input nilai</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Status entri nilai pada seluruh jadwal aktif</p>
                </div>
                <span class="text-2xl font-bold text-cyan-700 dark:text-cyan-300">{{ $complianceRate }}%</span>
            </div>
            <div class="h-64"><canvas id="complianceChart" role="img" aria-label="Grafik kepatuhan input nilai"></canvas></div>
        </div>

        <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">
            <p class="text-sm font-medium text-slate-300">Periode aktif</p>
            <p class="mt-2 text-2xl font-bold">{{ $activeYear?->nama_tahun_ajaran ?? 'Belum diatur' }}</p>
            <p class="mt-1 text-sm text-slate-300">{{ $activeYear?->semester ?? 'Semester belum dipilih' }}</p>
            <div class="mt-6 border-t border-white/10 pt-5">
                <p class="text-3xl font-bold">{{ count($ungradedSchedules ?? []) }}</p>
                <p class="mt-1 text-sm text-slate-300">jadwal perlu ditindaklanjuti</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm sm:text-base">Jadwal Belum Diinput Nilai Oleh Guru</h3>
                <p class="text-xs text-gray-600 mt-0.5">Pantau sesi pelajaran yang belum memiliki entri nilai rapor santri</p>
            </div>
            <a href="{{ route('teacher.grades.index') }}" class="text-xs font-semibold text-cyan-700 dark:text-cyan-400 hover:underline">Lihat Semua Guru →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="table-builder min-w-full border-separate border-spacing-0">
                <thead>
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Mata Pelajaran &amp; Kelas</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Guru Pengampu</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Waktu</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Aksi Supervisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($ungradedSchedules ?? [] as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $schedule->mataPelajaran->nama_mapel ?? '-' }}</span>
                            <div class="text-xs text-gray-600 font-medium">Kelas: {{ $schedule->kelas->nama_kelas ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">{{ $schedule->pengajar->nama_lengkap ?? '-' }}</span>
                            <div class="text-xs text-gray-600">NIP/KODE: {{ $schedule->pengajar->nip ?? '-' }}</div>
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
    <x-page-heading title="Konten Website Yayasan" :description="'Kelola berita, fasilitas, dan identitas situs. Selamat datang, '.auth()->user()->name.'.'">
        <x-slot:actions>
            <a href="{{ route('home') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-primary-600 text-white hover:bg-primary-700 border-transparent">Lihat Website Publik ↗</a>
        </x-slot:actions>
    </x-page-heading>

    <div class="mb-8">
        <h2 class="mb-4 text-base font-bold text-gray-900 dark:text-white">Aksi cepat</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.news.create') }}" class="min-h-12 flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                <span class="text-lg">+</span> Tulis berita
            </a>
            <a href="{{ route('settings.index') }}" class="min-h-12 flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                Kelola identitas situs
            </a>
            <a href="{{ route('admin.facilities.index') }}" class="min-h-12 flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                Kelola fasilitas
            </a>
            <a href="{{ route('admin.programs.index') }}" class="min-h-12 flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                Program &amp; biaya
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-gray-600 dark:text-gray-300 text-xs font-semibold uppercase">Total Berita</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $totalNews ?? 0 }}</p>
            <p class="text-xs font-medium text-emerald-600 mt-1">Artikel Terpublikasi</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-gray-600 dark:text-gray-300 text-xs font-semibold uppercase">Galeri Fasilitas</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $totalFacilities ?? 0 }}</p>
            <p class="text-xs font-medium text-sky-600 mt-1">Sarana Pesantren</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-gray-600 dark:text-gray-300 text-xs font-semibold uppercase">Program Pendidikan</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $activeProgramsCount ?? 0 }}</p>
            <p class="text-xs font-medium text-purple-600 mt-1">Ditampilkan di Website</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-gray-600 dark:text-gray-300 text-xs font-semibold uppercase">Dokumen Biaya</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $totalFees ?? 0 }}</p>
            <p class="text-xs font-medium text-amber-600 mt-1">File Foto / PDF / CSV Aktif</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="xl:col-span-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Komposisi konten</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Konten yang tersedia untuk website publik</p>
            </div>
            <div class="h-64"><canvas id="contentDistributionChart" role="img" aria-label="Grafik komposisi konten website"></canvas></div>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="rounded-2xl bg-emerald-600 p-6 text-white shadow-sm transition hover:bg-emerald-700">
            <p class="text-sm font-medium text-emerald-100">Website publik</p>
            <p class="mt-2 text-2xl font-bold">Tinjau hasil publikasi</p>
            <p class="mt-6 inline-flex items-center gap-2 text-sm font-semibold">Buka situs <span aria-hidden="true">↗</span></p>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm sm:text-base">Publikasi Berita Terbaru</h3>
            <a href="{{ route('admin.news.index') }}" class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:underline">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($latestNews ?? [] as $item)
                <div class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <div class="min-w-0">
                        <p class="font-semibold text-sm text-gray-900 dark:text-gray-100 truncate">{{ $item->title }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $item->category ?? 'Kegiatan' }} • {{ optional($item->published_at ?? $item->created_at)->format('d M Y') }}</p>
                    </div>
                    <a href="{{ route('admin.news.edit', $item->id) }}" class="shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100">
                        Edit
                    </a>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-gray-600">Belum ada berita dipublikasikan.</div>
            @endforelse
        </div>
    </div>

@else
    {{-- ═══ TAMPILAN DASHBOARD PENGURUS AKADEMIK & SUPER ADMIN ═══ --}}
    <x-page-heading title="Dashboard" :description="'Ringkasan SIAKAD, PPDB, dan konten website. Selamat datang, '.auth()->user()->name.'.'">
        <x-slot:actions>
            <a href="{{ route('admin.registrations.index') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-bold transition bg-primary-600 text-white hover:bg-primary-700 border-transparent">Verifikasi PPDB ({{ $newRegistrantsCount ?? 0 }})</a>
        </x-slot:actions>
    </x-page-heading>

    <div class="mb-8">
        <h2 class="mb-4 text-base font-bold text-gray-900 dark:text-white">Aksi cepat</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.santri.create') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                + Tambah Santri
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                + Tambah Pengajar
            </a>
            <a href="{{ route('admin.schedules.create') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                + Plotting Jadwal
            </a>
            <a href="{{ route('admin.promotions.index') }}" class="min-h-12 flex items-center justify-center sm:justify-start gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                🎓 Kenaikan &amp; Kelulusan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Pendaftar Pending</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $newRegistrantsCount ?? 0 }}</p>
            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-2">Butuh Tindakan</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Santri Aktif SIAKAD</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $activeSantriCount ?? 0 }}</p>
            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-2">Diterima PPDB: {{ $totalSantri ?? 0 }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Program Aktif</h3>
            <p class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $activeProgramsCount ?? 0 }}</p>
            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-2">Berjalan</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Tahun Ajaran</h3>
            <p class="text-2xl font-bold leading-none tracking-tight text-gray-900 dark:text-white mt-2">{{ $activeYear?->nama_tahun_ajaran ?? '-' }}</p>
            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-2">{{ $activeYear?->semester ?? 'Belum Aktif' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="xl:col-span-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan operasional</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Snapshot data akademik saat ini</p>
            </div>
            <div class="h-64"><canvas id="academicOverviewChart" role="img" aria-label="Grafik ringkasan operasional akademik"></canvas></div>
        </div>
        <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div>
                <span class="chip chip-warning">Perlu perhatian</span>
                <p class="mt-4 text-4xl font-bold leading-none text-gray-900 dark:text-white">{{ $newRegistrantsCount ?? 0 }}</p>
                <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">pendaftar menunggu verifikasi</p>
            </div>
            <a href="{{ route('admin.registrations.index') }}" class="mt-6 inline-flex h-10 items-center justify-between rounded-lg bg-primary-600 px-4 text-sm font-bold text-white transition hover:bg-primary-700">
                Tinjau pendaftar
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
@endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            const chartTextColor = '#718096';
            const chartGridColor = 'rgba(148, 163, 184, 0.18)';
            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: chartTextColor,
                            boxWidth: 10,
                            boxHeight: 10,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 18,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1A202C',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: true
                    }
                }
            };

            const renderChart = function (id, config) {
                const canvas = document.getElementById(id);

                if (canvas) {
                    new Chart(canvas, config);
                }
            };

            renderChart('teacherWorkloadChart', {
                type: 'bar',
                data: {
                    labels: ['Jadwal', 'Kelas', 'Mapel', 'Santri'],
                    datasets: [{
                        data: [
                            {{ count($mySchedules ?? []) }},
                            {{ $myClassesCount ?? 0 }},
                            {{ $mySubjectsCount ?? 0 }},
                            {{ $mySantriCount ?? 0 }}
                        ],
                        backgroundColor: ['#27A376', '#55C792', '#88D8AE', '#A9E3C8'],
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 42
                    }]
                },
                options: {
                    ...baseOptions,
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: chartTextColor, font: { size: 12, weight: '600' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: chartGridColor },
                            border: { display: false },
                            ticks: { color: chartTextColor, precision: 0 }
                        }
                    }
                }
            });

            renderChart('complianceChart', {
                type: 'doughnut',
                data: {
                    labels: ['Sudah diinput', 'Belum diinput'],
                    datasets: [{
                        data: [
                            {{ $gradedSchedulesCount ?? 0 }},
                            {{ max(0, ($totalSchedules ?? 0) - ($gradedSchedulesCount ?? 0)) }}
                        ],
                        backgroundColor: ['#27A376', '#E2E8F0'],
                        borderWidth: 0,
                        spacing: 4,
                        borderRadius: 8,
                        hoverOffset: 4
                    }]
                },
                options: {
                    ...baseOptions,
                    cutout: '72%',
                    plugins: {
                        ...baseOptions.plugins,
                        legend: {
                            ...baseOptions.plugins.legend,
                            position: 'bottom'
                        }
                    }
                }
            });

            renderChart('contentDistributionChart', {
                type: 'doughnut',
                data: {
                    labels: ['Berita', 'Fasilitas', 'Program', 'Rincian biaya'],
                    datasets: [{
                        data: [
                            {{ $totalNews ?? 0 }},
                            {{ $totalFacilities ?? 0 }},
                            {{ $activeProgramsCount ?? 0 }},
                            {{ $totalFees ?? 0 }}
                        ],
                        backgroundColor: ['#27A376', '#88D8AE', '#CBD5E0', '#718096'],
                        borderWidth: 0,
                        spacing: 4,
                        borderRadius: 8,
                        hoverOffset: 4
                    }]
                },
                options: {
                    ...baseOptions,
                    cutout: '66%',
                    plugins: {
                        ...baseOptions.plugins,
                        legend: {
                            ...baseOptions.plugins.legend,
                            position: 'bottom'
                        }
                    }
                }
            });

            renderChart('academicOverviewChart', {
                type: 'bar',
                data: {
                    labels: ['Pendaftar', 'Santri aktif', 'Program aktif'],
                    datasets: [{
                        label: 'Jumlah',
                        data: [
                            {{ $newRegistrantsCount ?? 0 }},
                            {{ $activeSantriCount ?? 0 }},
                            {{ $activeProgramsCount ?? 0 }}
                        ],
                        backgroundColor: ['#CBD5E0', '#27A376', '#88D8AE'],
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 58
                    }]
                },
                options: {
                    ...baseOptions,
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: chartTextColor, font: { size: 12, weight: '600' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: chartGridColor },
                            border: { display: false },
                            ticks: { color: chartTextColor, precision: 0 }
                        }
                    }
                }
            });
        });
    </script>
@endsection
