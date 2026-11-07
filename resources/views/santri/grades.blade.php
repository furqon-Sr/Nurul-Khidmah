@extends('layouts.admin')

@section('title', 'Rincian Nilai & KHS Santri')

@section('content')
@php
    $displayKelas = $kelasPeriode ?? $santri->kelas;
    $activeCatatan = $catatanWaliKelas ?? null;
    $activeSikap = $nilaiSikap ?? null;
    $hasValidGrades = ($gradedCount ?? collect($subjectRows ?? [])->where('has_nilai', true)->count()) > 0;
@endphp

@include('santri.partials.portal-filter-bar', [
    'actionRoute' => route('wali.grades'),
    'headingId' => 'wali-grades-heading',
])

@if($activeSikap || $activeCatatan)
    <div class="mb-5 rounded-xl border border-gray-200/90 bg-white p-4 sm:p-5 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Catatan Evaluasi Wali Kelas ({{ $displayKelas->waliKelas->nama_lengkap ?? '-' }})
            </h2>
            @if($activeSikap)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-500/15 dark:text-primary-300">
                    Sikap / Kepesantrenan: {{ $activeSikap }}
                </span>
            @endif
        </div>
        <p class="text-sm text-gray-700 dark:text-gray-300">
            "{{ $activeCatatan ?: 'Pertahankan ketekunan belajar dan adab di pesantren.' }}"
        </p>
    </div>
@endif

<div class="rounded-xl border border-gray-200/90 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 sm:px-5 py-3.5 dark:border-gray-800/80">
        <div>
            <h2 id="wali-grades-caption" class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                Transkrip Nilai per Mata Pelajaran
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Bobot komponen: Tugas 30% · UTS 30% · UAS 40%
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm">
            @if($hasValidGrades)
                <span class="rounded-lg bg-gray-100 px-3 py-1 font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    Jumlah Nilai: <strong class="font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($jumlahNilai ?? 0, 2) }}</strong>
                </span>
                <span class="rounded-lg bg-primary-50 px-3 py-1 font-medium text-primary-800 dark:bg-primary-500/15 dark:text-primary-300">
                    Rata-Rata: <strong class="font-bold tabular-nums">{{ number_format($gpa, 2) }}</strong>
                </span>
            @else
                <span class="rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    Rata-Rata: <strong>—</strong> (Belum ada nilai)
                </span>
            @endif
        </div>
    </div>

    <div tabindex="0" role="region" aria-labelledby="wali-grades-caption" class="overflow-x-auto focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500">
        <table class="min-w-[780px] w-full border-collapse text-left">
            <thead class="border-b border-gray-100 bg-white dark:border-gray-800/80 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                    <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Pengajar</th>
                    <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Tugas (30%)</th>
                    <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">UTS (30%)</th>
                    <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">UAS (40%)</th>
                    <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Nilai Akhir</th>
                    <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Predikat</th>
                    <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Catatan Evaluasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800/80 dark:bg-gray-900">
                @forelse($subjectRows ?? [] as $row)
                    @php
                        $gradeColor = match ($row->grade) {
                            'A' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-500/15 dark:text-success-300',
                            'B' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/15 dark:text-blue-300',
                            'C' => 'bg-warning-50 text-warning-800 ring-warning-600/20 dark:bg-warning-400/15 dark:text-warning-300',
                            'D' => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/15 dark:text-orange-300',
                            'E' => 'bg-error-50 text-error-700 ring-error-600/20 dark:bg-error-500/15 dark:text-error-300',
                            default => 'bg-gray-100 text-gray-500 ring-gray-300/40 dark:bg-gray-800 dark:text-gray-400',
                        };
                    @endphp
                    <tr class="h-14 hover:bg-gray-50/75 dark:hover:bg-gray-800/40 transition-colors">
                        <th scope="row" class="px-5 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $row->nama_mapel }}
                        </th>
                        <td class="px-4 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            {{ $row->pengajar_nama }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-center text-sm text-gray-700 dark:text-gray-300 tabular-nums">
                            {{ $row->has_nilai && $row->nilai_tugas !== null ? number_format($row->nilai_tugas, 2) : '—' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-center text-sm text-gray-700 dark:text-gray-300 tabular-nums">
                            {{ $row->has_nilai && $row->nilai_uts !== null ? number_format($row->nilai_uts, 2) : '—' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-center text-sm text-gray-700 dark:text-gray-300 tabular-nums">
                            {{ $row->has_nilai && $row->nilai_uas !== null ? number_format($row->nilai_uas, 2) : '—' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-gray-100 tabular-nums">
                            {{ $row->has_nilai && $row->nilai_akhir !== null ? number_format($row->nilai_akhir, 2) : '—' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-center">
                            @if($row->has_nilai && $row->grade)
                                <span class="inline-flex items-center justify-center rounded-md px-2 py-0.5 text-xs font-bold ring-1 ring-inset {{ $gradeColor }}">
                                    {{ $row->grade }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ $row->has_nilai ? ($row->keterangan ?: '—') : 'Belum ada nilai' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada mata pelajaran atau nilai pada periode ini</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Rincian nilai akan tampil setelah jadwal dan komponen nilai diinput oleh pengajar.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
