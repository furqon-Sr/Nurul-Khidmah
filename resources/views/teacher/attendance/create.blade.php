@extends('layouts.admin')

@section('title', 'Input Presensi Santri')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Input Presensi Santri</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">
                {{ $schedule->mataPelajaran->nama_mapel }} — {{ $schedule->kelas->nama_kelas }} (Tanggal: {{ date('d M Y', strtotime($tanggal)) }})
            </p>
        </div>
        <a href="{{ route('teacher.attendance.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium">
            ← Kembali
        </a>
    </div>

    <form action="{{ route('teacher.attendance.store') }}" method="POST">
        @csrf
        <input type="hidden" name="jadwal_pelajaran_id" value="{{ $schedule->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/40 flex flex-wrap justify-between items-center text-sm gap-2">
                <span class="font-semibold text-emerald-900 dark:text-emerald-300">Total Santri: {{ $santris->count() }} Orang</span>
                <span class="text-xs text-emerald-700 dark:text-emerald-400">Pengajar: {{ $schedule->pengajar->nama_lengkap ?? '-' }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">NIS & Nama Santri</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status Kehadiran</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($santris as $santri)
                        @php
                            $rec = $existingAbsensi->get($santri->id);
                            $currentStatus = $rec ? $rec->status_kehadiran : 'Hadir';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $santri->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $santri->nis }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center gap-3">
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="absensi[{{ $santri->id }}][status]" value="Hadir" {{ $currentStatus === 'Hadir' ? 'checked' : '' }}
                                               class="text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Hadir</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="absensi[{{ $santri->id }}][status]" value="Izin" {{ $currentStatus === 'Izin' ? 'checked' : '' }}
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">Izin</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="absensi[{{ $santri->id }}][status]" value="Sakit" {{ $currentStatus === 'Sakit' ? 'checked' : '' }}
                                               class="text-amber-600 focus:ring-amber-500">
                                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">Sakit</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="absensi[{{ $santri->id }}][status]" value="Alpa" {{ $currentStatus === 'Alpa' ? 'checked' : '' }}
                                               class="text-rose-600 focus:ring-rose-500">
                                        <span class="text-xs font-semibold text-rose-700 dark:text-rose-400">Alpa</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="text" name="absensi[{{ $santri->id }}][keterangan]" value="{{ $rec->keterangan ?? '' }}" placeholder="Catatan opsional..."
                                       class="w-full px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs focus:ring-2 focus:ring-emerald-500">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500 text-sm">
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
            <a href="{{ route('teacher.attendance.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg transition text-sm font-medium shadow-sm">
                Simpan Presensi
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
