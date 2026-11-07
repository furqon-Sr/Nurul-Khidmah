@extends('layouts.admin')

@section('title', 'Pusat Laporan & Rapor Kelas')

@section('content')
@php
    $classOptions = $filteredClasses->isNotEmpty() ? $filteredClasses : $classes;
    $hasActiveFilter = request()->filled('tingkat') || request()->filled('filter_kelas_id') || request()->filled('search') || request()->has('tahun_ajaran_id');
@endphp

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Pusat Laporan Kolektif &amp; Cetak Rapor PDF</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm mt-0.5">
            Cetak Rekap Presensi Kelas, Leger &amp; Nilai Spesifik Mata Pelajaran, serta Lembar Rapor Resmi ber-kop surat pesantren.
        </p>
    </div>
    @if(isset($activeYear) && $activeYear)
        <span class="px-2.5 py-1 text-2xs uppercase tracking-wide font-bold rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 w-fit">
            Periode Aktif: {{ $activeYear->nama_tahun_ajaran }} ({{ $activeYear->semester }})
        </span>
    @endif
</div>

@if(session('success'))
<div role="status" class="mb-6 p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded-lg shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

{{-- Bar Filterisasi Pencarian Santri & Rapor (Tahun Ajaran + Tingkat 7/8/9 + Kelas + Pencarian Cepat) --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-5 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
        <div>
            <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100">Filterisasi Cepat Kelas &amp; Pencarian Santri</h2>
            <p class="text-xs text-gray-600 dark:text-gray-400">Terhubung langsung ke daftar santri, pengisian catatan rapor, dan tombol cetak Rapor PDF di bawah.</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 w-fit">
            {{ $santris->count() }} Santri Ditemukan
        </span>
    </div>

    <form id="reports-filter-form" method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
        <div class="lg:col-span-3">
            <label for="filter_tahun_ajaran_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tahun Ajaran</label>
            <select id="filter_tahun_ajaran_id" name="tahun_ajaran_id"
                    class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs font-medium text-gray-900 dark:text-gray-100">
                <option value="">-- Semua Tahun Ajaran --</option>
                @foreach($academicYears as $ta)
                    <option value="{{ $ta->id }}" {{ optional($selectedYear)->id === $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama_tahun_ajaran }} - {{ $ta->semester }}{{ $ta->status_aktif ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="lg:col-span-2">
            <label for="filter_tingkat" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tingkat Kelas</label>
            <select id="filter_tingkat" name="tingkat"
                    class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs font-medium text-gray-900 dark:text-gray-100">
                <option value="">Semua Tingkat (7/8/9)</option>
                <option value="7" {{ $selectedTingkat === '7' ? 'selected' : '' }}>Tingkat 7 (Ula)</option>
                <option value="8" {{ $selectedTingkat === '8' ? 'selected' : '' }}>Tingkat 8 (Wustho)</option>
                <option value="9" {{ $selectedTingkat === '9' ? 'selected' : '' }}>Tingkat 9 (Ulya)</option>
            </select>
        </div>

        <div class="lg:col-span-3">
            <label for="filter_kelas_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
            <select id="filter_kelas_id" name="filter_kelas_id"
                    class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs font-medium text-gray-900 dark:text-gray-100">
                <option value="">-- Semua Kelas --</option>
                @foreach($classOptions as $c)
                    <option value="{{ $c->id }}" {{ optional($selectedFilterKelas)->id === $c->id ? 'selected' : '' }}>
                        {{ $c->nama_kelas }} ({{ $c->tahunAjaran->nama_tahun_ajaran ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2 lg:col-span-4">
            <label for="filter_search_santri" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pencarian Cepat (Nama / NIS)</label>
            <div class="flex gap-2">
                <input type="search" id="filter_search_santri" name="search" value="{{ $search }}"
                       placeholder="Ketik nama santri atau 8 digit NIS..."
                       autocorrect="off" autocapitalize="off" spellcheck="false" enterkeyhint="search"
                       class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs text-gray-900 dark:text-gray-100">
                <button type="submit"
                        class="min-h-11 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition shrink-0">
                    Cari
                </button>
                @if($hasActiveFilter)
                    <a href="{{ route('admin.reports.index') }}"
                       class="min-h-11 px-3 py-2 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold transition shrink-0">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- 3 Kartu Cetak Laporan Kolektif & Perorangan --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
    {{-- Card 1: Rekap Presensi Per Kelas --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col justify-between">
        <div>
            <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-300 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">1. Rekap Presensi Kelas</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">Cetak rekapitulasi kehadiran santri (Hadir, Izin, Sakit, Alpa, &amp; Catatan Disiplin) ber-kop surat resmi.</p>
        </div>

        <form action="{{ route('admin.reports.attendance-pdf') }}" method="GET" target="_blank">
            @if($selectedYear)
                <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear->id }}">
            @endif
            <div class="mb-3">
                <label for="attendance_kelas_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
                <select id="attendance_kelas_id" name="kelas_id" required
                        class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs">
                    @forelse($classOptions as $c)
                        <option value="{{ $c->id }}" {{ optional($selectedFilterKelas)->id === $c->id ? 'selected' : '' }}>
                            {{ $c->nama_kelas }} (Wali: {{ $c->waliKelas->nama_lengkap ?? '-' }})
                        </option>
                    @empty
                        <option value="" disabled>Belum ada kelas tersedia</option>
                    @endforelse
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button type="submit" {{ $classOptions->isEmpty() ? 'disabled' : '' }}
                        class="w-full min-h-11 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                        style="background-color: #047857 !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Pratinjau / Cetak
                </button>
                <button type="submit" formaction="{{ route('admin.reports.attendance.download') }}" {{ $classOptions->isEmpty() ? 'disabled' : '' }}
                        class="w-full min-h-11 px-3 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                        style="background-color: #2563eb !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download PDF
                </button>
            </div>
        </form>
    </div>

    {{-- Card 2: Rekap Nilai & Leger Mata Pelajaran Per Kelas --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col justify-between">
        <div>
            <div class="w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-300 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">2. Leger &amp; Nilai Spesifik Mapel</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Cetak Leger Nilai lengkap rincian tiap mata pelajaran + Jumlah &amp; Rata-Rata, atau cetak nilai spesifik 1 mapel.</p>
        </div>

        <form action="{{ route('admin.reports.grades-pdf') }}" method="GET" target="_blank">
            @if($selectedYear)
                <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear->id }}">
            @endif
            <div class="mb-2.5">
                <label for="grades_kelas_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
                <select id="grades_kelas_id" name="kelas_id" required
                        class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs">
                    @forelse($classOptions as $c)
                        <option value="{{ $c->id }}" {{ optional($selectedFilterKelas)->id === $c->id ? 'selected' : '' }}>
                            {{ $c->nama_kelas }} (Wali: {{ $c->waliKelas->nama_lengkap ?? '-' }})
                        </option>
                    @empty
                        <option value="" disabled>Belum ada kelas tersedia</option>
                    @endforelse
                </select>
            </div>
            <div class="mb-3">
                <label for="grades_mapel_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Cakupan Mata Pelajaran</label>
                <select id="grades_mapel_id" name="mata_pelajaran_id"
                        class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs">
                    <option value="">Semua Mapel (Leger Nilai Per Mapel + Rata-Rata)</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}">{{ $subj->kode_mapel }} — {{ $subj->nama_mapel }} (Tugas/UTS/UAS)</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button type="submit" {{ $classOptions->isEmpty() ? 'disabled' : '' }}
                        class="w-full min-h-11 px-3 py-2 bg-purple-700 hover:bg-purple-800 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                        style="background-color: #6d28d9 !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Pratinjau / Cetak
                </button>
                <button type="submit" formaction="{{ route('admin.reports.grades.download') }}" {{ $classOptions->isEmpty() ? 'disabled' : '' }}
                        class="w-full min-h-11 px-3 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                        style="background-color: #2563eb !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download PDF
                </button>
            </div>
        </form>
    </div>

    {{-- Card 3: Cetak Cepat Rapor Santri Perorangan --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col justify-between">
        <div>
            <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-300 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">3. Lembar Rapor Santri</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">Cetak Rapor Resmi lengkap nilai spesifik seluruh mapel, jumlah nilai, rata-rata, &amp; tanda tangan resmi.</p>
        </div>

        <form id="quick-santri-rapor-form" method="GET" action="{{ route('admin.reports.index') }}">
            <input type="hidden" id="quick_rapor_tahun_id" value="{{ $selectedYear?->id }}">
            <div class="mb-3">
                <label for="santri_select_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Santri (Sesuai Filter)</label>
                <select id="santri_select_id"
                        class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs">
                    @forelse($santris as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->nis }}) — {{ $s->kelas->nama_kelas ?? 'Alumni/Lulus' }}</option>
                    @empty
                        <option value="" disabled>Tidak ada santri pada filter ini</option>
                    @endforelse
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button type="button" id="btn-quick-rapor-preview" {{ $santris->isEmpty() ? 'disabled' : '' }}
                        class="w-full min-h-11 px-3 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                        style="background-color: #d97706 !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Pratinjau / Cetak
                </button>
                <button type="button" id="btn-quick-rapor-download" {{ $santris->isEmpty() ? 'disabled' : '' }}
                        class="w-full min-h-11 px-3 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                        style="background-color: #2563eb !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download PDF
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Daftar Santri Terfilter + Pengisian Nilai Sikap Wali Kelas + Tombol Cetak Rapor Langsung --}}
@if($classes->isNotEmpty() || $santris->isNotEmpty())
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 id="wali-eval-heading" class="text-base font-bold text-gray-900 dark:text-gray-100">
                Daftar Santri, Evaluasi Sikap Wali Kelas &amp; Tombol Cetak Rapor PDF
            </h2>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                Isi predikat sikap/kepesantrenan &amp; catatan evaluasi Wali Kelas, lalu klik <strong>Cetak Rapor PDF</strong> pada baris santri.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($selectedFilterKelas)
                <span class="px-2.5 py-1 rounded-md text-2xs uppercase tracking-wide font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 w-fit">
                    Kelas Terfilter: {{ $selectedFilterKelas->nama_kelas }}
                </span>
            @endif
            @if((auth()->user()->isKepalaSekolah() || auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin()) && $selectedEvalKelas)
                <form action="{{ route('admin.reports.approve-class', $selectedEvalKelas->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui (Approve) seluruh rapor kelas {{ $selectedEvalKelas->nama_kelas }}? Nilai dan presensi santri akan terkunci secara permanen.')">
                    @csrf
                    <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Approve Rapor Kelas Ini
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($selectedEvalKelas)
    <form action="{{ route('admin.reports.wali-notes', $selectedEvalKelas->id) }}" method="POST">
        @csrf
        <input type="hidden" name="allow_filtered" value="1">
        <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
        <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
        <input type="hidden" name="filter_kelas_id" value="{{ $selectedFilterKelas?->id }}">
        <input type="hidden" name="search" value="{{ $search }}">

        <div tabindex="0" role="region" aria-labelledby="wali-eval-heading" class="overflow-x-auto max-h-[68vh]">
            <table class="table-builder min-w-full border-separate border-spacing-0">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">NIS &amp; Nama Santri</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-bold text-gray-600 w-36 border-b border-gray-200 dark:border-gray-700">Kelas</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-bold text-gray-600 w-52 border-b border-gray-200 dark:border-gray-700">Predikat Sikap</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-bold text-gray-600 min-w-[260px] border-b border-gray-200 dark:border-gray-700">Catatan Wali Kelas di Rapor</th>
                        <th scope="col" class="sticky right-0 z-20 bg-gray-100 dark:bg-gray-900 px-4 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-200 min-w-[260px] shadow-[-3px_0_6px_-2px_rgba(0,0,0,0.1)] border-b border-gray-200 dark:border-gray-700">Aksi Rapor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($evalSantris as $s)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 santri-eval-row" data-search="{{ strtolower($s->nama_lengkap . ' ' . $s->nis) }}">
                        <th scope="row" class="sm:sticky sm:left-0 z-[1] bg-white dark:bg-gray-800 px-4 sm:px-5 py-3.5 whitespace-nowrap text-left font-normal border-b border-gray-100 dark:border-gray-700/60">
                            <div class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ $s->nama_lengkap }}</div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-gray-600 font-mono">{{ $s->nis }}</span>
                                @if($s->status_santri === 'Lulus')
                                    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Alumni/Lulus</span>
                                @endif
                            </div>
                        </th>
                        <td class="px-3 py-3.5 whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60 text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ $s->kelas->nama_kelas ?? '-' }}
                        </td>
                        <td class="px-3 py-3.5 border-b border-gray-100 dark:border-gray-700/60">
                            <label for="sikap_{{ $s->id }}" class="sr-only">Predikat Sikap {{ $s->nama_lengkap }}</label>
                            <select id="sikap_{{ $s->id }}" name="evaluasi[{{ $s->id }}][nilai_sikap]"
                                    class="w-full min-h-10 px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs font-medium">
                                <option value="">-- Pilih Predikat --</option>
                                @foreach(['Sangat Baik (A)', 'Baik (B)', 'Cukup (C)', 'Perlu Bimbingan (D)'] as $opt)
                                    <option value="{{ $opt }}" {{ $s->nilai_sikap === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-3.5 border-b border-gray-100 dark:border-gray-700/60">
                            <label for="catatan_{{ $s->id }}" class="sr-only">Catatan Wali Kelas {{ $s->nama_lengkap }}</label>
                            <input type="text" id="catatan_{{ $s->id }}" name="evaluasi[{{ $s->id }}][catatan_wali_kelas]" value="{{ $s->catatan_wali_kelas }}"
                                   placeholder="Contoh: Rajin jamaah & hafalan baik, tingkatkan murojaah..."
                                   class="w-full min-h-10 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-base sm:text-xs">
                        </td>
                        <td class="sticky right-0 z-10 bg-white dark:bg-gray-800 px-4 py-3.5 text-center whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60 shadow-[-3px_0_6px_-2px_rgba(0,0,0,0.06)]">
                            @php
                                $rSem = $s->raporSemesterForYear($selectedYear?->id);
                                $isApproved = $rSem && in_array($rSem->status_publikasi, ['approved', 'published'], true);
                            @endphp
                            <div class="inline-flex items-center justify-center gap-1.5">
                                @if($isApproved)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-sm" title="Rapor telah disetujui & data terkunci">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Approved
                                    </span>
                                @elseif(auth()->user()->isKepalaSekolah() || auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin())
                                    <form action="{{ route('admin.reports.approve-santri', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui rapor santri {{ $s->nama_lengkap }}? Nilai dan presensi akan terkunci.')">
                                        @csrf
                                        <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
                                        <button type="submit" class="inline-flex items-center gap-1 min-h-9 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition" style="background-color: #047857 !important; color: #ffffff !important;" title="Setujui (Approve) Rapor">
                                            ✓ Approve
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.reports.santri-rapor-pdf', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                   target="_blank"
                                   class="inline-flex items-center justify-center gap-1 min-h-9 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm transition"
                                   style="background-color: #d97706 !important; color: #ffffff !important;"
                                   title="Buka Pratinjau Cetak">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Pratinjau
                                </a>
                                <a href="{{ route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                   class="inline-flex items-center justify-center gap-1.5 min-h-9 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition"
                                   style="background-color: #2563eb !important; color: #ffffff !important;"
                                   title="Download File PDF Sejati">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-600 text-xs">
                            Tidak ditemukan data santri yang cocok dengan filter Tahun Ajaran / Tingkat / Kelas / kata kunci pencarian di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($evalSantris->isNotEmpty())
        <div class="p-4 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-xs text-gray-600 dark:text-gray-400">
                Simpan perubahan predikat sikap &amp; catatan sebelum mencetak Rapor PDF santri.
            </span>
            <button type="submit" class="min-h-11 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition w-full sm:w-auto">
                Simpan Nilai Sikap &amp; Catatan Wali Kelas
            </button>
        </div>
        @endif
    </form>
    @endif
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('reports-filter-form');
        ['filter_tahun_ajaran_id', 'filter_tingkat', 'filter_kelas_id'].forEach(function (selectId) {
            const el = document.getElementById(selectId);
            if (el && filterForm) {
                el.addEventListener('change', function () {
                    filterForm.submit();
                });
            }
        });

        const searchInput = document.getElementById('filter_search_santri');
        if (searchInput) {
            let searchTimeout = null;
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                const evalRows = document.querySelectorAll('.santri-eval-row');
                evalRows.forEach(row => {
                    const searchData = row.getAttribute('data-search') || '';
                    row.style.display = (!query || searchData.includes(query)) ? '' : 'none';
                });

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (filterForm) filterForm.submit();
                }, 600);
            });
        }

        const btnPreview = document.getElementById('btn-quick-rapor-preview');
        const btnDownload = document.getElementById('btn-quick-rapor-download');
        const selectEl = document.getElementById('santri_select_id');
        const tahunId = document.getElementById('quick_rapor_tahun_id')?.value;

        if (btnPreview) {
            btnPreview.addEventListener('click', function () {
                if (selectEl && selectEl.value) {
                    const baseUrl = `{{ url('/admin/reports/santri-rapor-pdf') }}/${encodeURIComponent(selectEl.value)}`;
                    const targetUrl = tahunId ? `${baseUrl}?tahun_ajaran_id=${encodeURIComponent(tahunId)}` : baseUrl;
                    window.open(targetUrl, '_blank');
                }
            });
        }

        if (btnDownload) {
            btnDownload.addEventListener('click', function () {
                if (selectEl && selectEl.value) {
                    const baseUrl = `{{ url('/admin/reports/santri-rapor-pdf') }}/${encodeURIComponent(selectEl.value)}/download`;
                    const targetUrl = tahunId ? `${baseUrl}?tahun_ajaran_id=${encodeURIComponent(tahunId)}` : baseUrl;
                    window.location.href = targetUrl;
                }
            });
        }
    });
</script>
@endsection
