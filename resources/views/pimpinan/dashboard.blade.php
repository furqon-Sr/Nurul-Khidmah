@extends('layouts.admin')

@section('title', 'Dashboard Pimpinan Pesantren')

@section('content')

{{-- Banner Pimpinan --}}
<div class="mb-8 bg-gradient-to-br from-purple-800 via-purple-900 to-slate-900 rounded-2xl shadow-lg overflow-hidden text-white relative">
    <div class="relative px-6 py-7 sm:px-8 sm:py-8 flex flex-col sm:flex-row sm:items-center gap-5 z-10">
        <div class="shrink-0">
            @if(auth()->user()->avatarUrl())
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white/30 shadow-xl">
            @else
                <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-purple-400 to-indigo-600 ring-4 ring-white/30 shadow-xl flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">{{ auth()->user()->initials() }}</span>
                </div>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-purple-200 text-sm font-medium mb-0.5">Ahlan wa Sahlan, Pimpinan Pesantren</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ auth()->user()->name }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-2">
                @include('partials.admin-role-badge', ['user' => auth()->user()])
                <span class="text-purple-200 text-xs">
                    Tahun Ajaran Aktif: <strong class="text-white">{{ $activeYear->nama_tahun_ajaran ?? '-' }} (Semester {{ $activeYear->semester ?? '-' }})</strong>
                </span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-sm font-medium rounded-lg shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Pusat Laporan Akademik
            </a>
        </div>
    </div>
</div>

{{-- Top Summary Executive Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
        <h3 class="text-xs font-semibold text-gray-500 uppercase">Total Santri Aktif</h3>
        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalSantri }}</p>
        <p class="text-xs text-emerald-600 mt-1 font-medium">Santri Pesantren</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
        <h3 class="text-xs font-semibold text-gray-500 uppercase">Total Pengajar</h3>
        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalPengajar }}</p>
        <p class="text-xs text-indigo-600 mt-1 font-medium">Ustaz / Ustazah</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
        <h3 class="text-xs font-semibold text-gray-500 uppercase">Tingkat Kehadiran</h3>
        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $attendancePercentage }}%</p>
        <p class="text-xs text-blue-600 mt-1 font-medium">Persentase Presensi Santri</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
        <h3 class="text-xs font-semibold text-gray-500 uppercase">Rata-Rata Nilai Akhir</h3>
        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($averageGrade, 2) }}</p>
        <p class="text-xs text-purple-600 mt-1 font-medium">Hasil Pembelajaran</p>
    </div>
</div>

{{-- Grade Distribution & Class Monitoring Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Sebaran Predikat Nilai --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-base mb-4">Sebaran Predikat Nilai Santri</h3>
        <div class="space-y-3">
            @foreach(['A' => 'emerald', 'B' => 'blue', 'C' => 'amber', 'D' => 'orange', 'E' => 'rose'] as $g => $color)
            @php $cnt = $gradeDistribution[$g] ?? 0; @endphp
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-gray-700 dark:text-gray-300">Grade {{ $g }}</span>
                    <span class="text-gray-500">{{ $cnt }} Matkul</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                    <div class="bg-{{ $color }}-500 h-full" style="width: {{ array_sum($gradeDistribution) > 0 ? ($cnt / array_sum($gradeDistribution) * 100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Monitoring Kelas & Wali Kelas --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-base mb-4">Monitoring Kelas & Wali Kelas</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Wali Kelas</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase">Jumlah Santri</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($classes as $c)
                    <tr>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100 text-sm">
                            {{ $c->nama_kelas }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                            {{ $c->waliKelas->nama_lengkap ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-purple-700 dark:text-purple-400">
                            {{ $c->santris->count() }} Orang
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-6 text-gray-500 text-xs">Belum ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
