@extends('layouts.admin')

@section('title', 'Pusat Laporan Akademik')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Pusat Laporan & Cetak PDF SIAKAD</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Cetak Laporan Rekap Presensi Santri, Rekap Nilai per Kelas, dan Kartu Hasil Studi (KHS Rapor).</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Card 1: Rekap Presensi Per Kelas --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-between">
        <div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-300 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Rekap Presensi Per Kelas</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Cetak laporan rekapitulasi tingkat kehadiran santri (Hadir, Izin, Sakit, Alpa) per kelas.</p>
        </div>

        <form action="{{ route('admin.reports.attendance-pdf') }}" method="GET" target="_blank">
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
                <select name="kelas_id" required class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->nama_kelas }} (Wali: {{ $c->waliKelas->nama_lengkap ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                🖨️ Cetak Rekap Presensi PDF
            </button>
        </form>
    </div>

    {{-- Card 2: Rekap Nilai Per Kelas --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-between">
        <div>
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-300 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Rekap Transkrip Nilai Kelas</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Cetak laporan rekapitulasi seluruh nilai santri per kelas pada semester aktif.</p>
        </div>

        <form action="{{ route('admin.reports.grades-pdf') }}" method="GET" target="_blank">
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
                <select name="kelas_id" required class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->nama_kelas }} (Wali: {{ $c->waliKelas->nama_lengkap ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                🖨️ Cetak Rekap Nilai PDF
            </button>
        </form>
    </div>

    {{-- Card 3: Cetak Rapor Santri Perorangan --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-between">
        <div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-300 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Cetak Rapor Santri (KHS PDF)</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Cetak Rapor Hasil Belajar (KHS) perorangan santri lengkap dengan Kop Resmi Pesantren.</p>
        </div>

        <div>
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Santri</label>
                <select id="santri_select_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs">
                    @foreach($santris as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->nis }}) - {{ $s->kelas->nama_kelas ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <button onclick="printSantriRapor()" class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                🖨️ Cetak Rapor Santri (PDF)
            </button>
        </div>
    </div>
</div>

<script>
    function printSantriRapor() {
        const id = document.getElementById('santri_select_id').value;
        if (id) {
            window.open(`/admin/reports/santri-rapor-pdf/${id}`, '_blank');
        }
    }
</script>
@endsection
