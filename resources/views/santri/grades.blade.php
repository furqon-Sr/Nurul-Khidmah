@extends('layouts.admin')

@section('title', 'Transkrip Nilai Ananda')

@section('content')
@php
    $queryParams = array_filter([
        'santri_id' => auth()->user()->isPengurusAkademik() && isset($santri) ? $santri->id : null,
        'tahun_ajaran_id' => $tahunAjaran?->id,
        'tingkat' => auth()->user()->isPengurusAkademik() ? request('tingkat') : null,
        'filter_kelas_id' => auth()->user()->isPengurusAkademik() ? request('filter_kelas_id') : null,
        'search' => auth()->user()->isPengurusAkademik() ? request('search') : null,
    ], fn ($v) => $v !== null && $v !== '');
    $displayKelas = $kelasPeriode ?? $santri->kelas;
@endphp

@include('santri.partials.portal-filter-bar', ['actionRoute' => route('wali.grades')])

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 id="wali-grades-caption" class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Transkrip Nilai Spesifik Per Mata Pelajaran (KHS)</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Rincian nilai spesifik tiap mata pelajaran: Tugas (30%), UTS (30%), UAS (40%), Nilai Akhir, Predikat, serta Jumlah &amp; Rata-Rata.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('wali.dashboard', $queryParams) }}" class="min-h-11 inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 text-gray-800 dark:text-gray-200 text-sm font-semibold rounded-xl transition">
            ← Dashboard
        </a>
        <a href="{{ route('wali.rapor-pdf', $queryParams) }}" target="_blank" class="min-h-11 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-amber-950 font-bold rounded-xl transition text-sm inline-flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Unduh Rapor Ananda (PDF)
        </a>
    </div>
</div>

@php
    $activeCatatan = $catatanWaliKelas ?? $santri->catatan_wali_kelas;
    $activeSikap = $nilaiSikap ?? $santri->nilai_sikap;
@endphp

@if($activeSikap || $activeCatatan)
<div class="mb-6 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Catatan Evaluasi Wali Kelas ({{ $displayKelas->waliKelas->nama_lengkap ?? '-' }})</p>
        <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 italic">"{{ $activeCatatan ?: 'Pertahankan ketekunan belajar dan adab di pesantren.' }}"</p>
    </div>
    @if($activeSikap)
        <span class="px-3 py-1.5 text-xs font-extrabold rounded-lg bg-emerald-600 text-white shrink-0 w-fit">
            Sikap / Kepesantrenan: {{ $activeSikap }}
        </span>
    @endif
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-4 bg-teal-50 dark:bg-teal-950/40 border-b border-teal-100 dark:border-teal-900/40 flex flex-wrap justify-between items-center gap-3 text-sm">
        <div class="flex flex-wrap items-center gap-4">
            <span class="font-semibold text-teal-900 dark:text-teal-300">Jumlah Nilai: <strong class="text-base text-teal-950 dark:text-white">{{ number_format($jumlahNilai ?? 0, 2) }}</strong></span>
            <span class="font-semibold text-teal-900 dark:text-teal-300">Rata-Rata Akhir: <strong class="text-base text-teal-950 dark:text-white">{{ number_format($gpa, 2) }}</strong></span>
        </div>
        <span class="text-xs text-teal-700 dark:text-teal-400">Ananda: {{ $santri->nama_lengkap }} (NIS: {{ $santri->nis }} • {{ $displayKelas->nama_kelas ?? '-' }})</span>
    </div>

    <div tabindex="0" role="region" aria-labelledby="wali-grades-caption" class="overflow-auto max-w-full max-h-[75vh]">
        <table class="min-w-full border-separate border-spacing-0">
            <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 sticky left-0 z-20">Mata Pelajaran</th>
                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Pengajar</th>
                    <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Tugas (30%)</th>
                    <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">UTS (30%)</th>
                    <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">UAS (40%)</th>
                    <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Nilai Akhir</th>
                    <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Predikat</th>
                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Catatan Evaluasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjectRows ?? [] as $row)
                @php
                    $gradeColor = match($row->grade) {
                        'A' => 'bg-emerald-100 text-emerald-800',
                        'B' => 'bg-blue-100 text-blue-800',
                        'C' => 'bg-amber-100 text-amber-800',
                        'D' => 'bg-orange-100 text-orange-800',
                        'E' => 'bg-rose-100 text-rose-800',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <th scope="row" class="px-5 py-4 whitespace-nowrap text-left font-bold text-gray-900 dark:text-gray-100 text-sm border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky left-0 z-[1]">
                        {{ $row->nama_mapel }}
                    </th>
                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        {{ $row->pengajar_nama }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        {{ $row->has_nilai && $row->nilai_tugas !== null ? number_format($row->nilai_tugas, 2) : '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        {{ $row->has_nilai && $row->nilai_uts !== null ? number_format($row->nilai_uts, 2) : '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        {{ $row->has_nilai && $row->nilai_uas !== null ? number_format($row->nilai_uas, 2) : '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                        {{ $row->has_nilai && $row->nilai_akhir !== null ? number_format($row->nilai_akhir, 2) : '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center border-b border-gray-200 dark:border-gray-700">
                        <span class="px-2.5 py-1 text-xs font-extrabold rounded {{ $gradeColor }}">
                            {{ $row->has_nilai ? ($row->grade ?? '-') : '-' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        {{ $row->has_nilai ? ($row->keterangan ?: '-') : 'Menunggu input nilai pengajar' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-500 text-sm">
                        Belum ada mata pelajaran / nilai pada periode semester ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
