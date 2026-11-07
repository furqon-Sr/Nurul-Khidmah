@extends('layouts.admin')

@section('title', 'Dashboard Eksekutif Pimpinan')

@section('content')

{{-- Banner Eksekutif Pimpinan Yayasan & Pengasuh Pondok (Read-Only + Unduh PDF) --}}
<div class="mb-8 bg-gradient-to-br from-purple-800 via-purple-900 to-slate-900 rounded-2xl shadow-lg overflow-hidden text-white relative">
    <div class="relative px-6 py-7 sm:px-8 sm:py-8 flex flex-col lg:flex-row lg:items-center gap-5 z-10">
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
            <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ auth()->user()->name }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-2">
                @include('partials.admin-role-badge', ['user' => auth()->user()])
                <span class="text-purple-200 text-xs">
                    Periode Aktif: <strong class="text-white">{{ $activeYear->nama_tahun_ajaran ?? '-' }} (Semester {{ $activeYear->semester ?? '-' }})</strong>
                </span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('pimpinan.export-pdf') }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-900 text-sm font-bold rounded-xl shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh Ringkasan Eksekutif (PDF)
            </a>
        </div>
    </div>
</div>

{{-- Kartu Ringkasan Track Record Makro --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-emerald-500">
        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Santri Aktif</h3>
        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalSantriAktif }}</p>
        <p class="text-[11px] text-emerald-600 mt-1 font-medium">Mukim &amp; Belajar</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-teal-500">
        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Lulus / Alumni</h3>
        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalSantriLulus }}</p>
        <p class="text-[11px] text-teal-600 mt-1 font-medium">Mutasi: {{ $totalSantriMutasi }} Santri</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-indigo-500">
        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Ustaz &amp; Kelas</h3>
        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalPengajar }} <span class="text-sm font-normal text-gray-400">/ {{ $totalKelas }} Kls</span></p>
        <p class="text-[11px] text-indigo-600 mt-1 font-medium">Pengajar Aktif &amp; Rombel</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-amber-500">
        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">PPDB (Diterima)</h3>
        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $ppdbTotals['diterima'] }} <span class="text-sm font-normal text-gray-400">/ {{ $ppdbTotals['pendaftar'] }}</span></p>
        <p class="text-[11px] text-amber-600 mt-1 font-medium">Total Pendaftar Masuk</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Rata-Rata Hadir</h3>
        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $attendancePercentage }}%</p>
        <p class="text-[11px] text-blue-600 mt-1 font-medium">Presensi Semester Aktif</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Rata-Rata Nilai</h3>
        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($averageGrade, 2) }}</p>
        <p class="text-[11px] text-purple-600 mt-1 font-medium">Capaian Akademik</p>
    </div>
</div>

{{-- Indikator Ketuntasan Pengisian Nilai Rapor oleh Dewan Guru --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-3">
        <div>
            <h2 class="font-bold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                Indikator Ketuntasan Pengisian Nilai Rapor Seluruh Kelas
                @if($expectedTotalEntries > 0 && $gradeCompletionPercentage >= 100)
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">100% Tuntas Siap Cetak Rapor</span>
                @else
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Dalam Proses Pengisian Guru</span>
                @endif
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Tercatat <strong>{{ $filledTotalEntries }}</strong> dari <strong>{{ $expectedTotalEntries }}</strong> entri nilai santri aktif pada semester ini telah diinput oleh ustaz/ustazah pengampu.
            </p>
        </div>
        <div class="text-right shrink-0">
            <span class="text-3xl font-extrabold {{ $gradeCompletionPercentage >= 100 && $expectedTotalEntries > 0 ? 'text-emerald-600' : 'text-purple-700 dark:text-purple-400' }}">
                {{ $gradeCompletionPercentage }}%
            </span>
        </div>
    </div>

    <div class="w-full bg-gray-100 dark:bg-gray-700 h-3 rounded-full overflow-hidden">
        <div class="{{ $gradeCompletionPercentage >= 100 && $expectedTotalEntries > 0 ? 'bg-emerald-500' : 'bg-purple-600' }} h-full transition-all duration-500"
             style="width: {{ min($gradeCompletionPercentage, 100) }}%"></div>
    </div>

    @if(!empty($incompleteSchedules))
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">
                Mata Pelajaran yang Belum 100% Terisi ({{ count($incompleteSchedules) }} Jadwal):
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                @foreach(array_slice($incompleteSchedules, 0, 6) as $inc)
                    <div class="px-3 py-2 rounded-lg bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex items-center justify-between gap-2 text-xs">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $inc['mapel'] }} ({{ $inc['kelas'] }})</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ $inc['pengajar'] }}</p>
                        </div>
                        <span class="font-bold text-amber-700 dark:text-amber-300 shrink-0">{{ $inc['filled'] }}/{{ $inc['expected'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Grid Grafik Track Record PPDB Tahunan & Sebaran Predikat Nilai --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Track Record PPDB Tahunan --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-base">Track Record PPDB Tahunan (Pendaftar vs Diterima)</h3>
                <p class="text-xs text-gray-500">Perbandingan animo calon santri baru dan santri yang resmi diterima tiap periode.</p>
            </div>
        </div>

        @php
            $maxPpdb = max(1, (int) ($ppdbTrends->max('total_pendaftar') ?? 1));
        @endphp

        <div class="space-y-4">
            @forelse($ppdbTrends as $row)
                @php
                    $pctTotal = round(($row->total_pendaftar / $maxPpdb) * 100);
                    $pctAcc   = $row->total_pendaftar > 0 ? round(($row->diterima / $row->total_pendaftar) * 100, 1) : 0;
                @endphp
                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200/70 dark:border-gray-700">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <span class="font-bold text-sm text-gray-900 dark:text-gray-100">Tahun Ajaran {{ $row->periode }}</span>
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 font-semibold text-gray-800 dark:text-gray-200">
                                Pendaftar: {{ $row->total_pendaftar }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 font-bold text-emerald-800 dark:text-emerald-300">
                                Diterima: {{ $row->diterima }} ({{ $pctAcc }}%)
                            </span>
                            @if($row->pending > 0)
                                <span class="px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950 font-semibold text-amber-800 dark:text-amber-300">
                                    Pending: {{ $row->pending }}
                                </span>
                            @endif
                            @if($row->ditolak > 0)
                                <span class="px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-950 font-semibold text-rose-800 dark:text-rose-300">
                                    Ditolak: {{ $row->ditolak }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden flex">
                        <div class="bg-emerald-500 h-full" style="width: {{ ($row->diterima / $maxPpdb) * 100 }}%" title="Diterima"></div>
                        <div class="bg-amber-400 h-full" style="width: {{ ($row->pending / $maxPpdb) * 100 }}%" title="Pending"></div>
                        <div class="bg-rose-400 h-full" style="width: {{ ($row->ditolak / $maxPpdb) * 100 }}%" title="Ditolak"></div>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-gray-500 text-xs">Belum ada riwayat pendaftar PPDB tercatat.</p>
            @endforelse
        </div>
    </div>

    {{-- Sebaran Predikat Nilai --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-base mb-1">Sebaran Predikat Nilai</h3>
        <p class="text-xs text-gray-500 mb-4">Distribusi huruf mutu akademik semester aktif.</p>
        @php
            $gradeStyles = [
                'A' => ['bar' => 'bg-emerald-500', 'label' => 'Grade A (≥85 Sangat Baik)'],
                'B' => ['bar' => 'bg-blue-500',    'label' => 'Grade B (75–84 Baik)'],
                'C' => ['bar' => 'bg-amber-500',   'label' => 'Grade C (65–74 Cukup)'],
                'D' => ['bar' => 'bg-orange-500',  'label' => 'Grade D (50–64 Kurang)'],
                'E' => ['bar' => 'bg-rose-500',    'label' => 'Grade E (<50 Perlu Pembinaan)'],
            ];
            $sumGrades = array_sum($gradeDistribution);
        @endphp
        <div class="space-y-3.5">
            @foreach($gradeStyles as $g => $meta)
            @php $cnt = $gradeDistribution[$g] ?? 0; @endphp
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-gray-700 dark:text-gray-300">{{ $meta['label'] }}</span>
                    <span class="text-gray-500">{{ $cnt }} Nilai</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                    <div class="{{ $meta['bar'] }} h-full" style="width: {{ $sumGrades > 0 ? ($cnt / $sumGrades * 100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Tabel Rekapitulasi Evaluasi Per Kelas --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h3 id="executive-class-table-heading" class="font-bold text-gray-800 dark:text-gray-100 text-base">
                Daftar Rekapitulasi Akademik &amp; Kehadiran Per Kelas
            </h3>
            <p class="text-xs text-gray-500">Ringkasan wali kelas, jumlah santri aktif, tingkat kehadiran, rata-rata nilai, dan progres pengisian rapor.</p>
        </div>
    </div>

    <div tabindex="0" role="region" aria-labelledby="executive-class-table-heading" class="overflow-x-auto max-h-[70vh]">
        <table class="min-w-full border-separate border-spacing-0">
            <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Kelas</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Wali Kelas</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Santri Aktif</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">% Kehadiran</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Rata-Rata Nilai</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase min-w-[200px] border-b border-gray-200 dark:border-gray-700">Ketuntasan Input Rapor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($classSummaries as $c)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                    <th scope="row" class="sticky left-0 z-[1] bg-white dark:bg-gray-800 px-5 py-3.5 whitespace-nowrap text-left font-bold text-gray-900 dark:text-gray-100 text-sm border-b border-gray-100 dark:border-gray-700/60">
                        {{ $c['nama_kelas'] }}
                        <span class="block text-[11px] font-normal text-gray-500">{{ $c['jumlah_mapel'] }} Mata Pelajaran</span>
                    </th>
                    <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60">
                        {{ $c['wali_kelas'] }}
                    </td>
                    <td class="px-4 py-3.5 text-center text-sm font-semibold text-purple-700 dark:text-purple-400 whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60">
                        {{ $c['jumlah_santri'] }} Santri
                    </td>
                    <td class="px-4 py-3.5 text-center text-sm font-semibold text-blue-700 dark:text-blue-400 whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60">
                        {{ $c['attendance_pct'] }}%
                    </td>
                    <td class="px-4 py-3.5 text-center text-sm font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60">
                        {{ $c['avg_grade'] > 0 ? number_format($c['avg_grade'], 2) : '-' }}
                    </td>
                    <td class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center justify-between text-xs font-semibold mb-1">
                            <span class="{{ $c['completion_pct'] >= 100 && $c['expected_grades'] > 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $c['completion_pct'] }}%
                            </span>
                            <span class="text-gray-400 text-[11px]">{{ $c['filled_grades'] }}/{{ $c['expected_grades'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                            <div class="{{ $c['completion_pct'] >= 100 && $c['expected_grades'] > 0 ? 'bg-emerald-500' : 'bg-amber-500' }} h-full"
                                 style="width: {{ min($c['completion_pct'], 100) }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500 text-xs">Belum ada data rombongan belajar terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
