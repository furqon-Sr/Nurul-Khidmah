@extends('layouts.admin')

@section('title', 'Riwayat Presensi Santri')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Riwayat Kehadiran / Presensi Saya</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Daftar presensi harian per mata pelajaran pada semester aktif.</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Mata Pelajaran</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Pengajar</th>
                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase">Status Kehadiran</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($absensiRecords as $rec)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ date('d M Y', strtotime($rec->tanggal)) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-teal-700 dark:text-teal-400">
                        {{ $rec->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        {{ $rec->jadwalPelajaran->pengajar->nama_lengkap ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full
                            {{ $rec->status_kehadiran == 'Hadir' ? 'bg-emerald-100 text-emerald-800' : ($rec->status_kehadiran == 'Izin' ? 'bg-blue-100 text-blue-800' : ($rec->status_kehadiran == 'Sakit' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) }}">
                            {{ $rec->status_kehadiran }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                        {{ $rec->keterangan ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 text-sm">
                        Belum ada catatan presensi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($absensiRecords->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $absensiRecords->links() }}
    </div>
    @endif
</div>
@endsection
