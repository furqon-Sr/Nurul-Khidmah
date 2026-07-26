@extends('layouts.admin')

@section('title', 'Input Nilai Santri')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Form Pengolahan Nilai Santri</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">
                {{ $schedule->mataPelajaran->nama_mapel }} — {{ $schedule->kelas->nama_kelas }} (Formula: Tugas 30% + UTS 30% + UAS 40%)
            </p>
        </div>
        <a href="{{ route('teacher.grades.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium">
            ← Kembali
        </a>
    </div>

    <form action="{{ route('teacher.grades.update', $schedule->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/40 flex flex-wrap justify-between items-center text-sm gap-2">
                <span class="font-semibold text-emerald-900 dark:text-emerald-300">Total Santri: {{ $santris->count() }} Orang</span>
                <span class="text-xs text-emerald-700 dark:text-emerald-400">Tahun Ajaran: {{ $schedule->tahunAjaran->nama_tahun_ajaran ?? '-' }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">NIS & Nama Santri</th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-28">Tugas (30%)</th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-28">UTS (30%)</th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-28">UAS (40%)</th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-28">Nilai Akhir</th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-20">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($santris as $santri)
                        @php
                            $rec = $existingNilai->get($santri->id);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $santri->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $santri->nis }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <input type="number" step="0.01" min="0" max="100" name="nilai[{{ $santri->id }}][tugas]" value="{{ old("nilai.{$santri->id}.tugas", $rec->nilai_tugas ?? '') }}" placeholder="0"
                                       class="w-24 text-center px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <input type="number" step="0.01" min="0" max="100" name="nilai[{{ $santri->id }}][uts]" value="{{ old("nilai.{$santri->id}.uts", $rec->nilai_uts ?? '') }}" placeholder="0"
                                       class="w-24 text-center px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <input type="number" step="0.01" min="0" max="100" name="nilai[{{ $santri->id }}][uas]" value="{{ old("nilai.{$santri->id}.uas", $rec->nilai_uas ?? '') }}" placeholder="0"
                                       class="w-24 text-center px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span class="font-bold text-gray-900 dark:text-gray-100 text-base">
                                    {{ $rec ? number_format($rec->nilai_akhir, 2) : '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($rec && $rec->grade)
                                    <span class="px-2.5 py-1 text-xs font-extrabold rounded {{ $rec->grade == 'A' ? 'bg-emerald-100 text-emerald-800' : ($rec->grade == 'B' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $rec->grade }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">
                                Tidak ada santri terdaftar di kelas ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($santris->isNotEmpty())
        <div class="flex justify-end gap-3">
            <a href="{{ route('teacher.grades.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg transition text-sm font-medium shadow-sm">
                Simpan & Hitung Nilai
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
