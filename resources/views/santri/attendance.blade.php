@extends('layouts.admin')

@section('title', 'Riwayat Presensi Ananda')

@section('content')
@php
    $queryParams = array_filter([
        'santri_id' => auth()->user()->isPengurusAkademik() && isset($santri) ? $santri->id : null,
        'tahun_ajaran_id' => $tahunAjaran?->id,
        'tingkat' => auth()->user()->isPengurusAkademik() ? request('tingkat') : null,
        'filter_kelas_id' => auth()->user()->isPengurusAkademik() ? request('filter_kelas_id') : null,
        'search' => auth()->user()->isPengurusAkademik() ? request('search') : null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

@include('santri.partials.portal-filter-bar', ['actionRoute' => route('wali.attendance')])

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 id="wali-attendance-caption" class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Riwayat Kehadiran / Presensi Ananda</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">
            Ananda <strong>{{ $santri->nama_lengkap }}</strong> (NIS: {{ $santri->nis }}) — {{ isset($tahunAjaran) && $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran . ' (' . $tahunAjaran->semester . ')' : 'Semester Aktif' }}.
        </p>
    </div>
    <a href="{{ route('wali.dashboard', $queryParams) }}" class="min-h-11 inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 text-gray-800 dark:text-gray-200 text-sm font-semibold rounded-xl transition w-fit">
        ← Kembali ke Dashboard Wali
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div tabindex="0" role="region" aria-labelledby="wali-attendance-caption" class="overflow-auto max-w-full max-h-[75vh]">
        <table class="min-w-full border-separate border-spacing-0">
            <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 sticky left-0 z-20">Tanggal</th>
                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Mata Pelajaran</th>
                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Pengajar</th>
                    <th scope="col" class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Status Kehadiran</th>
                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">Catatan Ustaz / Disiplin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensiRecords as $rec)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <th scope="row" class="px-5 py-4 whitespace-nowrap text-left text-sm font-semibold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky left-0 z-[1]">
                        {{ date('d M Y', strtotime($rec->tanggal)) }}
                    </th>
                    <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-teal-700 dark:text-teal-400 border-b border-gray-200 dark:border-gray-700">
                        {{ $rec->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-' }}
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        {{ $rec->jadwalPelajaran->pengajar->nama_lengkap ?? '-' }}
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap text-center border-b border-gray-200 dark:border-gray-700">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full
                            {{ match($rec->status_kehadiran) {
                                'Hadir' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                'Izin' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                'Sakit' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                'Terlambat' => 'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300',
                                'Bolos' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
                                default => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                            } }}">
                            {{ $rec->status_kehadiran }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        {{ $rec->keterangan ?: '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 text-sm">
                        Belum ada catatan presensi pada periode semester ini.
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
