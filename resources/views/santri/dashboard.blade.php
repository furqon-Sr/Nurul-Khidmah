@extends('layouts.admin')

@section('title', 'Portal Santri')

@section('content')

@if(!$santri)
    <div class="bg-amber-100 dark:bg-amber-950/50 border-l-4 border-amber-500 text-amber-800 dark:text-amber-300 p-5 rounded-xl">
        <p class="font-bold text-base">Akun Santri Belum Terhubung dengan Profile Santri</p>
        <p class="text-sm mt-1">Silakan hubungi Pengurus Akademik untuk menautkan akun ini ke Data Santri aktif.</p>
    </div>
@else
    {{-- Profile Banner Santri --}}
    <div class="mb-8 bg-gradient-to-br from-teal-700 via-teal-800 to-slate-900 rounded-2xl shadow-lg overflow-hidden text-white relative">
        <div class="relative px-6 py-7 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5 z-10">
            <div class="shrink-0">
                @if(auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
                @else
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 ring-4 ring-white/30 shadow-xl flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ auth()->user()->initials() }}</span>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-teal-200 text-sm font-medium mb-0.5">Ahlan wa Sahlan, Santri Nurul Khidmah</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ $santri->nama_lengkap }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-teal-500 text-white rounded-full">
                        NIS: {{ $santri->nis }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-emerald-500 text-white rounded-full">
                        Kelas: {{ $santri->kelas->nama_kelas ?? 'Belum Diplot' }}
                    </span>
                    @if($santri->kelas && $santri->kelas->waliKelas)
                        <span class="text-teal-200 text-xs">
                            Wali Kelas: <strong class="text-white">{{ $santri->kelas->waliKelas->nama_lengkap }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('santri.rapor-pdf') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-amber-950 font-bold text-sm rounded-lg shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Cetak Rapor (KHS PDF)
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards Santri --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Hadir</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalHadir }}</p>
            <p class="text-xs text-emerald-600 mt-1 font-medium">Pertemuan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Izin / Sakit</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalIzin + $totalSakit }}</p>
            <p class="text-xs text-blue-600 mt-1 font-medium">Keterangan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-rose-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Alpa</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalAlpa }}</p>
            <p class="text-xs text-rose-600 mt-1 font-medium">Tanpa Keterangan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Rata-Rata Nilai (IP)</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($gpa, 2) }}</p>
            <p class="text-xs text-purple-600 mt-1 font-medium">Akumulasi Nilai</p>
        </div>
    </div>

    {{-- Tabel Jadwal Pelajaran Saya --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-base">Jadwal Pelajaran Saya</h3>
            <span class="text-xs text-gray-500">Kelas: {{ $santri->kelas->nama_kelas ?? '-' }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Hari & Jam</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Mata Pelajaran</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Pengajar (Ustaz/Ustazah)</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Ruang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-bold rounded bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300">
                                {{ $schedule->hari }}
                            </span>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                {{ date('H:i', strtotime($schedule->waktu_mulai)) }} - {{ date('H:i', strtotime($schedule->waktu_selesai)) }} WIB
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-gray-100">
                            {{ $schedule->mataPelajaran->nama_mapel ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-medium">
                            {{ $schedule->pengajar->nama_lengkap ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->ruang ?? 'Kelas Utama' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">
                            Belum ada jadwal pelajaran terdaftar di kelas Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
