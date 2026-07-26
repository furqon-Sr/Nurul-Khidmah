@extends('layouts.admin')

@section('title', 'Transkrip Nilai Santri')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Transkrip Nilai Hasil Belajar (KHS)</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Nilai Tugas, UTS, UAS, Nilai Akhir, dan Huruf Mutu Predikat.</p>
    </div>
    <a href="{{ route('santri.rapor-pdf') }}" target="_blank" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-amber-950 font-bold rounded-lg transition text-sm flex items-center gap-2 shadow-sm w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Cetak Rapor (KHS PDF)
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-4 bg-teal-50 dark:bg-teal-950/40 border-b border-teal-100 dark:border-teal-900/40 flex justify-between items-center text-sm">
        <span class="font-semibold text-teal-900 dark:text-teal-300">Rata-Rata Nilai Akhir: <strong class="text-base text-teal-950 dark:text-white">{{ number_format($gpa, 2) }}</strong></span>
        <span class="text-xs text-teal-700 dark:text-teal-400">Santri: {{ $santri->nama_lengkap }} ({{ $santri->nis }})</span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Mata Pelajaran</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Pengajar</th>
                    <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase">Tugas (30%)</th>
                    <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase">UTS (30%)</th>
                    <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase">UAS (40%)</th>
                    <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase">Nilai Akhir</th>
                    <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase">Grade</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($nilais as $nilai)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-gray-100 text-sm">
                        {{ $nilai->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        {{ $nilai->jadwalPelajaran->pengajar->nama_lengkap ?? '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ number_format($nilai->nilai_tugas, 2) }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ number_format($nilai->nilai_uts, 2) }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ number_format($nilai->nilai_uas, 2) }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($nilai->nilai_akhir, 2) }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-center">
                        <span class="px-2.5 py-1 text-xs font-extrabold rounded {{ $nilai->grade == 'A' ? 'bg-emerald-100 text-emerald-800' : ($nilai->grade == 'B' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ $nilai->grade ?? '-' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500 text-sm">
                        Belum ada nilai yang diinput oleh Pengajar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
