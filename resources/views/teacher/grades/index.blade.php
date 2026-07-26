@extends('layouts.admin')

@section('title', 'Pengolahan Nilai Santri')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Pengolahan Nilai Santri</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Pilih mata pelajaran & kelas untuk menginput Nilai Tugas, UTS, dan UAS.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($schedules as $item)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="px-2.5 py-1 text-xs font-bold rounded bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                    {{ $item->kelas->nama_kelas ?? '-' }}
                </span>
                <span class="text-xs font-semibold text-gray-500">{{ $item->tahunAjaran->nama_tahun_ajaran ?? '' }}</span>
            </div>

            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">
                {{ $item->mataPelajaran->nama_mapel ?? '-' }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Pengajar: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $item->pengajar->nama_lengkap ?? '-' }}</span>
            </p>
        </div>

        <a href="{{ route('teacher.grades.edit', $item->id) }}" class="w-full px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-semibold transition text-center shadow-sm block">
            Input / Edit Nilai
        </a>
    </div>
    @empty
    <div class="col-span-full bg-white dark:bg-gray-800 rounded-xl p-10 text-center border border-gray-200 dark:border-gray-700">
        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada jadwal mengajar terdaftar.</p>
    </div>
    @endforelse
</div>
@endsection
