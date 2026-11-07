@extends('layouts.admin')

@section('title', 'Portal Wali Santri')

@section('content')

@include('santri.partials.portal-filter-bar', ['actionRoute' => route('wali.dashboard')])

@if(!$santri)
    <div class="bg-amber-100 dark:bg-amber-950/50 border-l-4 border-amber-500 text-amber-800 dark:text-amber-300 p-5 rounded-xl">
        <p class="font-bold text-base">Data Santri Tidak Ditemukan</p>
        <p class="text-sm mt-1">Tidak ada santri yang sesuai dengan kata kunci pencarian, atau akun Wali Santri belum ditautkan ke Data Santri aktif.</p>
    </div>
@else
    @php
        $queryParams = array_filter([
            'santri_id' => auth()->user()->isPengurusAkademik() ? $santri->id : null,
            'tahun_ajaran_id' => $tahunAjaran?->id,
            'tingkat' => auth()->user()->isPengurusAkademik() ? request('tingkat') : null,
            'filter_kelas_id' => auth()->user()->isPengurusAkademik() ? request('filter_kelas_id') : null,
            'search' => auth()->user()->isPengurusAkademik() ? request('search') : null,
        ], fn ($v) => $v !== null && $v !== '');
        $displayKelas = $kelasPeriode ?? $santri->kelas;
    @endphp

    {{-- Banner Selamat Datang Wali Santri --}}
    <div class="mb-7 bg-gradient-to-br from-primary-500 via-primary-600 to-primary-800 rounded-2xl shadow-lg overflow-hidden text-white relative">
        <div class="relative px-5 py-6 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5 z-10">
            <div class="shrink-0">
                @if($santri->foto)
                    <img src="{{ asset('storage/' . $santri->foto) }}" alt="{{ $santri->nama_lengkap }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
                @elseif(auth()->user()->avatarUrl() && auth()->user()->isSantri())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
                @else
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 ring-4 ring-white/30 shadow-xl flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ strtoupper(substr($santri->nama_lengkap, 0, 2)) }}</span>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-teal-200 text-xs sm:text-sm font-medium mb-0.5">Ahlan wa Sahlan, Portal Pemantauan Wali Santri</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">Ananda {{ $santri->nama_lengkap }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-teal-500 text-white rounded-full">
                        NIS: {{ $santri->nis }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-emerald-500 text-white rounded-full">
                        Kelas: {{ $displayKelas->nama_kelas ?? 'Belum Diplot' }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-white/15 text-teal-100 rounded-full">
                        Status: {{ $santri->status_santri }}
                    </span>
                    @if($santri->nama_wali)
                        <span class="px-2.5 py-0.5 text-xs font-medium bg-slate-800/70 text-teal-200 rounded-full border border-teal-500/30">
                            Wali: <strong class="text-white">{{ $santri->nama_wali }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="w-full sm:w-auto shrink-0">
                <a href="{{ route('wali.rapor-pdf', $queryParams) }}" target="_blank" class="min-h-12 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-amber-950 font-bold text-sm rounded-xl shadow transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh Rapor PDF ({{ $tahunAjaran->semester ?? 'Semester' }})
                </a>
            </div>
        </div>
    </div>

    {{-- Navigasi Cepat Antar Halaman Portal Wali --}}
    <div class="mb-6 p-4 rounded-xl bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm text-teal-900 dark:text-teal-200">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-xs sm:text-sm">Pemantauan <strong>Read-Only</strong> kehadiran harian, jadwal KBM madrasah, dan capaian nilai Ananda pada periode <strong>{{ isset($tahunAjaran) && $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran . ' (' . $tahunAjaran->semester . ')' : 'Semester Aktif' }}</strong>.</p>
        </div>
        <div class="grid grid-cols-2 sm:flex items-center gap-2 shrink-0">
            <a href="{{ route('wali.attendance', $queryParams) }}" class="min-h-10 inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold rounded-xl bg-teal-600 hover:bg-teal-700 text-white transition">Detail Presensi</a>
            <a href="{{ route('wali.grades', $queryParams) }}" class="min-h-10 inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition">Rincian Nilai Mapel</a>
        </div>
    </div>

    {{-- Stats Cards Kehadiran & Nilai Ananda --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-emerald-500">
            <h3 class="text-xs font-semibold text-gray-600 uppercase">Kehadiran Hadir</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalHadir }}</p>
            <p class="text-xs text-emerald-600 mt-1 font-medium">Pertemuan Kelas</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-blue-500">
            <h3 class="text-xs font-semibold text-gray-600 uppercase">Izin / Sakit</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalIzin + $totalSakit }}</p>
            <p class="text-xs text-blue-600 mt-1 font-medium">Izin: {{ $totalIzin }} • Sakit: {{ $totalSakit }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-rose-500">
            <h3 class="text-xs font-semibold text-gray-600 uppercase">Alpa</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalAlpa }}</p>
            <p class="text-xs text-rose-600 mt-1 font-medium">Tanpa Keterangan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-5 border-l-4 border-purple-500">
            <h3 class="text-xs font-semibold text-gray-600 uppercase">Rata-Rata Nilai</h3>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($gpa, 2) }}</p>
            <p class="text-xs text-purple-600 mt-1 font-medium">Capaian Semester</p>
        </div>
    </div>

    {{-- Catatan Wali Kelas & Sikap Kepesantrenan --}}
    @php
        $dashCatatan = $catatanWaliKelas ?? $santri->catatan_wali_kelas;
        $dashSikap = $nilaiSikap ?? $santri->nilai_sikap;
    @endphp
    @if($dashSikap || $dashCatatan)
    <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-emerald-200 dark:border-emerald-800/60 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm uppercase tracking-wider">Evaluasi Kepesantrenan &amp; Catatan Wali Kelas</h3>
            @if($dashSikap)
                <span class="px-2.5 py-1 text-2xs uppercase tracking-wide font-bold rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                    Predikat Sikap / Akhlaq: {{ $dashSikap }}
                </span>
            @endif
        </div>
        <p class="text-sm text-gray-700 dark:text-gray-300 italic">
            "{{ $dashCatatan ?: 'Terus tingkatkan semangat belajar, ibadah berjamaah, dan akhlaqul karimah di pondok.' }}"
        </p>
    </div>
    @endif

    {{-- Tabel Jadwal Pelajaran Ananda --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <h3 id="wali-schedule-caption" class="font-bold text-gray-800 dark:text-gray-100 text-base">Jadwal Pelajaran Ananda</h3>
            <span class="text-xs text-gray-600">Kelas: {{ $displayKelas->nama_kelas ?? '-' }}</span>
        </div>

        <div tabindex="0" role="region" aria-labelledby="wali-schedule-caption" class="overflow-auto max-w-full max-h-[70vh]">
            <table class="table-builder min-w-full border-separate border-spacing-0">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 sticky left-0 z-20">Hari &amp; Jam</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Mata Pelajaran</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Pengajar (Ustaz/Ustazah)</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Ruang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <th scope="row" class="px-5 py-4 whitespace-nowrap text-left border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky left-0 z-[1]">
                            <span class="px-2.5 py-1 text-xs font-bold rounded bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300">
                                {{ $schedule->hari }}
                            </span>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                {{ date('H:i', strtotime($schedule->waktu_mulai)) }} - {{ date('H:i', strtotime($schedule->waktu_selesai)) }} WIB
                            </div>
                        </th>
                        <td class="px-5 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                            {{ $schedule->mataPelajaran->nama_mapel ?? '-' }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-medium border-b border-gray-200 dark:border-gray-700">
                            {{ $schedule->pengajar->nama_lengkap ?? '-' }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 border-b border-gray-200 dark:border-gray-700">
                            {{ $schedule->ruang ?? 'Kelas Utama' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-600 text-sm">
                            Belum ada jadwal pelajaran terdaftar pada periode semester ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
