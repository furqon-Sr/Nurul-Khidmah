@extends('layouts.admin')

@section('title', 'Pencatatan Absensi Santri')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Presensi / Absensi Santri</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Pilih jadwal mengajar untuk menginput atau memperbarui presensi santri.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-rose-50 dark:bg-rose-950/50 border-l-4 border-rose-500 text-rose-700 dark:text-rose-300 rounded shadow-sm text-sm">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($schedules as $item)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="px-2.5 py-1 text-xs font-bold rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    {{ $item->hari }} • {{ date('H:i', strtotime($item->waktu_mulai)) }} - {{ date('H:i', strtotime($item->waktu_selesai)) }}
                </span>
                <span class="text-xs font-semibold text-gray-600">{{ $item->kelas->nama_kelas ?? '-' }}</span>
            </div>

            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">
                {{ $item->mataPelajaran->nama_mapel ?? '-' }}
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                Pengajar: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $item->pengajar->nama_lengkap ?? '-' }}</span>
            </p>
        </div>

        <form action="{{ route('teacher.attendance.create') }}" method="GET" class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
            <div class="flex items-center gap-2">
                <input type="hidden" name="schedule_id" value="{{ $item->id }}">
                <input type="date" name="tanggal" value="{{ $item->latestMatchingDate() }}" max="{{ date('Y-m-d') }}" required
                       class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="flex-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition text-center shadow-sm">
                    Input Presensi
                </button>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                Hanya menerima tanggal hari <strong>{{ $item->hari }}</strong> (maks. hari ini).
            </p>
        </form>
    </div>
    @empty
    <div class="col-span-full bg-white dark:bg-gray-800 rounded-xl p-10 text-center border border-gray-200 dark:border-gray-700">
        <p class="text-gray-600 dark:text-gray-400 text-sm">Belum ada jadwal mengajar terdaftar.</p>
    </div>
    @endforelse
</div>
@endsection
