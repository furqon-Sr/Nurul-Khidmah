@extends('layouts.admin')

@section('title', 'Pengolahan Nilai Santri')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Pengolahan Nilai Santri</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">
            Pilih mata pelajaran & kelas untuk menginput Nilai Tugas (30%), UTS (30%), UAS (40%), dan Catatan Evaluasi/Sikap.
        </p>
    </div>
    @if(isset($activeYear) && $activeYear)
        <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 w-fit">
            Periode Aktif: {{ $activeYear->nama_tahun_ajaran }} ({{ $activeYear->semester }})
        </span>
    @endif
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($schedules as $item)
    @php
        $expectedCount = (int) ($activeSantriCounts[$item->kelas_id] ?? 0);
        $filledCount = min((int) ($item->nilais_count ?? 0), $expectedCount ?: (int) ($item->nilais_count ?? 0));
        $pct = $expectedCount > 0 ? round(($filledCount / $expectedCount) * 100) : 0;
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="px-2.5 py-1 text-xs font-bold rounded bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                    {{ $item->kelas->nama_kelas ?? '-' }}
                </span>
                <span class="text-xs font-semibold text-gray-500">{{ $item->tahunAjaran->nama_tahun_ajaran ?? '' }} ({{ $item->tahunAjaran->semester ?? '' }})</span>
            </div>

            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">
                {{ $item->mataPelajaran->nama_mapel ?? '-' }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                Pengajar: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $item->pengajar->nama_lengkap ?? '-' }}</span>
            </p>

            {{-- Indikator Ketuntasan Input Nilai --}}
            <div class="mb-4">
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                    <span class="text-gray-600 dark:text-gray-400">Ketuntasan Input Nilai</span>
                    <span class="{{ $pct >= 100 && $expectedCount > 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $filledCount }} / {{ $expectedCount }} Santri ({{ $pct }}%)
                    </span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                    <div class="{{ $pct >= 100 && $expectedCount > 0 ? 'bg-emerald-500' : 'bg-amber-500' }} h-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>

        <a href="{{ route('teacher.grades.edit', $item->id) }}" class="w-full px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-semibold transition text-center shadow-sm block">
            Input / Edit Nilai &amp; Evaluasi
        </a>
    </div>
    @empty
    <div class="col-span-full bg-white dark:bg-gray-800 rounded-xl p-10 text-center border border-gray-200 dark:border-gray-700">
        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada jadwal mengajar terdaftar pada periode aktif.</p>
    </div>
    @endforelse
</div>
@endsection
