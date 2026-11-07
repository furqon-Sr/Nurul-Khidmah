@extends('layouts.admin')

@section('title', 'Riwayat Presensi Santri')

@section('content')

@include('santri.partials.portal-filter-bar', [
    'actionRoute' => route('wali.attendance'),
    'headingId' => 'wali-attendance-heading',
])

<div class="rounded-xl border border-gray-200/90 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-4 sm:px-5 py-3.5 dark:border-gray-800/80">
        <div>
            <h2 id="wali-attendance-caption" class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                Riwayat Kehadiran Harian
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Rekap pertemuan KBM pada periode {{ isset($tahunAjaran) && $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran . ' · Semester ' . $tahunAjaran->semester : 'Semester Aktif' }}
            </p>
        </div>
        <span class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300 tabular-nums">
            {{ $absensiRecords->total() }} pertemuan
        </span>
    </div>

    <div tabindex="0" role="region" aria-labelledby="wali-attendance-caption" class="overflow-x-auto focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500">
        <table class="min-w-[680px] w-full border-collapse text-left">
            <thead class="border-b border-gray-100 bg-white dark:border-gray-800/80 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                    <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                    <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Pengajar</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800/80 dark:bg-gray-900">
                @forelse($absensiRecords as $rec)
                    @php
                        [$badgeClass, $dotClass] = match ($rec->status_kehadiran) {
                            'Hadir' => ['bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-500/10 dark:text-success-300 dark:ring-success-500/25', 'bg-success-500'],
                            'Izin' => ['bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/25', 'bg-blue-500'],
                            'Sakit' => ['bg-warning-50 text-warning-800 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-300 dark:ring-warning-400/25', 'bg-warning-500'],
                            'Terlambat' => ['bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/25', 'bg-orange-500'],
                            default => ['bg-error-50 text-error-700 ring-error-600/20 dark:bg-error-500/10 dark:text-error-300 dark:ring-error-500/25', 'bg-error-500'],
                        };
                    @endphp
                    <tr class="h-14 hover:bg-gray-50/75 dark:hover:bg-gray-800/40 transition-colors">
                        <th scope="row" class="px-5 py-3 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-200 tabular-nums">
                            {{ date('d M Y', strtotime($rec->tanggal)) }}
                        </th>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $rec->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            {{ $rec->jadwalPelajaran->pengajar->nama_lengkap ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                                <span class="size-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
                                {{ $rec->status_kehadiran }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ $rec->keterangan ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada catatan presensi pada periode ini</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data kehadiran harian akan tampil setelah pengajar mencatat absensi kelas.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($absensiRecords->hasPages())
        <div class="border-t border-gray-100 px-4 sm:px-5 py-3 dark:border-gray-800/80">
            {{ $absensiRecords->links() }}
        </div>
    @endif
</div>
@endsection
