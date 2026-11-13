@extends('layouts.admin')

@section('title', 'Laporan & Rapor')

@section('content')
@php
    $classOptions = $filteredClasses->isNotEmpty() ? $filteredClasses : $classes;
    $reportTargetKelas = $selectedFilterKelas ?? ($classOptions->first() ?? null);
    $hasActiveFilter = request()->filled('tingkat')
        || request()->filled('filter_kelas_id')
        || request()->filled('search')
        || (request()->has('tahun_ajaran_id') && (int) request('tahun_ajaran_id') !== (int) ($activeYear?->id ?? 0));

    $canApprove = auth()->user()->isKepalaSekolah() || auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin();
    $isArchivePeriod = $selectedYear && ! $selectedYear->status_aktif;

    $statusBadgeMap = [
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700',
        'incomplete' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800/70',
        'ready_review' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800/70',
        'ready_approve' => 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800/70',
        'approved' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/70',
        'published' => 'bg-emerald-100/80 text-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700',
        'revision' => 'bg-orange-50 text-orange-800 dark:bg-orange-950/50 dark:text-orange-300 border border-orange-200 dark:border-orange-800/70',
    ];

    $santriJsDataset = [];
    foreach ($evalSantris as $s) {
        $m = $santriReportMeta[$s->id] ?? [];
        $santriJsDataset[$s->id] = [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'nis' => $s->nis,
            'kelas' => $m['kelas_nama'] ?? ($s->kelas->nama_kelas ?? '-'),
            'nilai_sikap' => $m['nilai_sikap'] ?? '',
            'catatan_wali_kelas' => $m['catatan_wali_kelas'] ?? '',
            'scheduled_mapel_count' => $m['scheduled_mapel_count'] ?? 0,
            'graded_mapel_count' => $m['graded_mapel_count'] ?? 0,
            'avg_score' => $m['avg_score'] ?? null,
            'hadir' => $m['hadir'] ?? 0,
            'izin' => $m['izin'] ?? 0,
            'sakit' => $m['sakit'] ?? 0,
            'alpa' => $m['alpa'] ?? 0,
            'missing_items' => $m['missing_items'] ?? [],
            'is_complete' => (bool) ($m['is_complete'] ?? false),
            'is_locked' => (bool) ($m['is_locked'] ?? false),
            'version' => $m['version'] ?? 1,
            'status_key' => $m['status_key'] ?? 'draft',
            'status_label' => $m['status_label'] ?? 'Draft',
            'approved_by_name' => $m['approved_by_name'] ?? null,
            'approved_at_label' => $m['approved_at_label'] ?? null,
            'published_by_name' => $m['published_by_name'] ?? null,
            'published_at_label' => $m['published_at_label'] ?? null,
            'revision_note' => $m['revision_note'] ?? null,
            'revision_by_name' => $m['revision_by_name'] ?? null,
            'revision_at_label' => $m['revision_at_label'] ?? null,
            'eval_url' => route('admin.reports.santri-evaluation', $s->id),
            'approve_url' => route('admin.reports.approve-santri', $s->id),
            'revision_url' => route('admin.reports.request-revision', $s->id),
            'preview_url' => route('admin.reports.santri-rapor-pdf', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])),
            'download_url' => route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])),
        ];
    }
@endphp

<div class="space-y-5 pb-20">
    {{-- ===================================================================
         HEADER RINGKAS & KONTEKS PERIODE
         =================================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                Laporan &amp; Rapor
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                Kelola laporan akademik dan penerbitan rapor.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            @if($selectedYear)
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <span class="size-2 rounded-full {{ $isArchivePeriod ? 'bg-amber-500' : 'bg-emerald-500' }}" aria-hidden="true"></span>
                    <span>{{ $selectedYear->nama_tahun_ajaran }} &middot; {{ $selectedYear->semester }}</span>
                    @if($isArchivePeriod)
                        <span class="ml-0.5 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-800 dark:bg-amber-950 dark:text-amber-300">Arsip</span>
                    @endif
                </span>
            @endif
            <span class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-gray-800/70 dark:text-gray-300">
                {{ $selectedFilterKelas ? $selectedFilterKelas->nama_kelas : 'Semua Kelas' }}
            </span>
        </div>
    </div>

    {{-- Feedback Banners --}}
    @if(session('success'))
        <div role="status" class="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-xs sm:text-sm text-emerald-900 dark:border-emerald-800/70 dark:bg-emerald-950/40 dark:text-emerald-200">
            <svg aria-hidden="true" class="size-4 mt-0.5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div role="alert" class="flex items-start gap-2.5 rounded-xl border border-error-200 bg-error-50/90 px-4 py-3 text-xs sm:text-sm text-error-800 dark:border-error-800/70 dark:bg-error-950/40 dark:text-error-200">
            <svg aria-hidden="true" class="size-4 mt-0.5 shrink-0 text-error-600 dark:text-error-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-error-200 bg-error-50/90 px-4 py-3 text-xs text-error-800 dark:border-error-800/70 dark:bg-error-950/40 dark:text-error-200">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===================================================================
         GLOBAL CONTEXTUAL FILTER TOOLBAR (Periode & Kelas Global)
         =================================================================== --}}
    <div class="rounded-xl border border-gray-200 bg-white p-3.5 dark:border-gray-800 dark:bg-gray-900">
        <form id="reports-context-form" method="GET" action="{{ route('admin.reports.index') }}"
              class="flex flex-col lg:flex-row lg:items-center gap-2.5">
            <input type="hidden" name="tab" id="context_active_tab" value="{{ $activeTab }}">
            @if($search !== '')
                <input type="hidden" name="search" id="context_preserved_search" value="{{ $search }}">
            @endif

            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 shrink-0 pr-1">
                <svg aria-hidden="true" class="size-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Konteks:</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 flex-1">
                <div>
                    <label for="filter_tahun_ajaran_id" class="sr-only">Tahun Ajaran &amp; Semester</label>
                    <select id="filter_tahun_ajaran_id" name="tahun_ajaran_id"
                            aria-label="Pilih Tahun Ajaran dan Semester"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-base sm:text-xs font-medium text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:bg-white dark:focus:bg-gray-900">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($academicYears as $ta)
                            <option value="{{ $ta->id }}" {{ optional($selectedYear)->id === $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama_tahun_ajaran }} &middot; {{ $ta->semester }}{{ $ta->status_aktif ? ' (Aktif)' : ' (Arsip)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_tingkat" class="sr-only">Tingkat Kelas</label>
                    <select id="filter_tingkat" name="tingkat"
                            aria-label="Filter Tingkat Kelas"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-base sm:text-xs font-medium text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:bg-white dark:focus:bg-gray-900">
                        <option value="">Semua Tingkat (7 / 8 / 9)</option>
                        <option value="7" {{ $selectedTingkat === '7' ? 'selected' : '' }}>Tingkat 7 (Ula)</option>
                        <option value="8" {{ $selectedTingkat === '8' ? 'selected' : '' }}>Tingkat 8 (Wustho)</option>
                        <option value="9" {{ $selectedTingkat === '9' ? 'selected' : '' }}>Tingkat 9 (Ulya)</option>
                    </select>
                </div>

                <div>
                    <label for="filter_kelas_id" class="sr-only">Kelas</label>
                    <select id="filter_kelas_id" name="filter_kelas_id"
                            aria-label="Pilih Kelas"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-base sm:text-xs font-medium text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:bg-white dark:focus:bg-gray-900">
                        <option value="">Semua Kelas ({{ $classOptions->count() }})</option>
                        @foreach($classOptions as $c)
                            <option value="{{ $c->id }}" {{ optional($selectedFilterKelas)->id === $c->id ? 'selected' : '' }}>
                                {{ $c->nama_kelas }}{{ $c->waliKelas ? ' — '.$c->waliKelas->nama_lengkap : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($hasActiveFilter)
                <a href="{{ route('admin.reports.index', ['tab' => $activeTab]) }}"
                   class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition shrink-0">
                    <svg aria-hidden="true" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Reset Filter</span>
                </a>
            @endif
        </form>
    </div>

    {{-- ===================================================================
         NAVIGATION TABS WORKSPACE (4 Fungsi Terpisah)
         =================================================================== --}}
    <div id="reports-workspace-tabs" class="space-y-5">
        <div role="tablist" aria-label="Ruang Kerja Laporan dan Rapor"
             class="flex items-center gap-1 overflow-x-auto border-b border-gray-200 dark:border-gray-800">
            @php
                $pendingApprovalCount = ($workflowSummary['ready_approve'] ?? 0) + ($workflowSummary['ready_review'] ?? 0);
                $tabsConfig = [
                    'laporan' => ['label' => 'Laporan Akademik', 'badge' => null],
                    'kelola' => ['label' => 'Kelola Rapor', 'badge' => $santris->count()],
                    'persetujuan' => ['label' => 'Persetujuan & Cetak', 'badge' => $pendingApprovalCount > 0 ? $pendingApprovalCount : null],
                    'riwayat' => ['label' => 'Riwayat', 'badge' => $archivedRapors->count() > 0 ? $archivedRapors->count() : null],
                ];
            @endphp
            @foreach($tabsConfig as $tabKey => $tabCfg)
                @php $isTabActive = $activeTab === $tabKey; @endphp
                <button type="button"
                        role="tab"
                        id="tab-btn-{{ $tabKey }}"
                        data-workspace-tab="{{ $tabKey }}"
                        aria-selected="{{ $isTabActive ? 'true' : 'false' }}"
                        aria-controls="tab-panel-{{ $tabKey }}"
                        tabindex="{{ $isTabActive ? '0' : '-1' }}"
                        class="-mb-px inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-3.5 py-2.5 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $isTabActive ? 'border-primary-600 text-primary-700 dark:border-primary-400 dark:text-primary-300' : 'border-transparent text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <span>{{ $tabCfg['label'] }}</span>
                    @if($tabCfg['badge'] !== null)
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-bold tabular-nums {{ $isTabActive ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                            {{ $tabCfg['badge'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ===============================================================
             TAB 1: LAPORAN AKADEMIK (Rekap Presensi, Leger Nilai, Rapor Santri)
             =============================================================== --}}
        <section role="tabpanel"
                 id="tab-panel-laporan"
                 aria-labelledby="tab-btn-laporan"
                 data-workspace-panel="laporan"
                 {!! $activeTab !== 'laporan' ? 'hidden="until-found"' : '' !!}>
            <div class="space-y-4">
                @if(! $selectedFilterKelas && $classOptions->count() > 1)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">
                        <span>
                            Konteks kelas saat ini <strong>Semua Kelas</strong>. Laporan per kelas di bawah menggunakan kelas acuan <strong>{{ $reportTargetKelas?->nama_kelas ?? '-' }}</strong> — pilih kelas spesifik pada filter di atas untuk mengganti cakupan.
                        </span>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Card A: Rekap Presensi --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex size-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    <svg aria-hidden="true" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    {{ $reportTargetKelas?->nama_kelas ?? 'Pilih Kelas' }}
                                </span>
                            </div>

                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Rekap Presensi</h2>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                                Ringkasan kehadiran santri per kelas (Hadir, Izin, Sakit, Alpa, dan catatan disiplin) ber-kop resmi.
                            </p>
                        </div>

                        <form action="{{ route('admin.reports.attendance-pdf') }}" method="GET" target="_blank" class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                            @if($selectedYear)
                                <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear->id }}">
                            @endif
                            @if($reportTargetKelas)
                                <input type="hidden" name="kelas_id" value="{{ $reportTargetKelas->id }}">
                            @endif

                            <div class="grid grid-cols-2 gap-2">
                                <button type="submit" {{ ! $reportTargetKelas ? 'disabled' : '' }}
                                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                                    <svg aria-hidden="true" class="size-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Pratinjau</span>
                                </button>
                                <button type="submit" formaction="{{ route('admin.reports.attendance.download') }}" {{ ! $reportTargetKelas ? 'disabled' : '' }}
                                        title="Unduh / Download PDF Rekap Presensi"
                                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 px-3 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                                    <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh PDF</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Card B: Leger Nilai --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex size-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    <svg aria-hidden="true" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                </div>
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    {{ $reportTargetKelas?->nama_kelas ?? 'Pilih Kelas' }}
                                </span>
                            </div>

                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Leger Nilai</h2>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                                Rekap nilai seluruh atau sebagian mata pelajaran beserta jumlah dan rata-rata capaian santri.
                            </p>
                        </div>

                        <form action="{{ route('admin.reports.grades-pdf') }}" method="GET" target="_blank" class="mt-4 pt-3.5 border-t border-gray-100 dark:border-gray-800 space-y-3">
                            @if($selectedYear)
                                <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear->id }}">
                            @endif
                            @if($reportTargetKelas)
                                <input type="hidden" name="kelas_id" value="{{ $reportTargetKelas->id }}">
                            @endif

                            <div>
                                <label for="grades_mapel_id" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                    Cakupan Mata Pelajaran
                                </label>
                                <select id="grades_mapel_id" name="mata_pelajaran_id"
                                        class="w-full h-10 px-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-base sm:text-xs font-medium text-gray-900 dark:text-gray-100">
                                    <option value="">Semua Mata Pelajaran (Leger Lengkap)</option>
                                    @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}">{{ $subj->kode_mapel }} &mdash; {{ $subj->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="submit" {{ ! $reportTargetKelas ? 'disabled' : '' }}
                                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                                    <svg aria-hidden="true" class="size-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Pratinjau</span>
                                </button>
                                <button type="submit" formaction="{{ route('admin.reports.grades.download') }}" {{ ! $reportTargetKelas ? 'disabled' : '' }}
                                        title="Unduh / Download PDF Leger Nilai"
                                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 px-3 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                                    <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh PDF</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Card C: Rapor Santri (Livewire Search Engine) --}}
                    <livewire:admin.rapor-santri-search
                        :tahun-ajaran-id="$selectedYear?->id"
                        :tingkat="$selectedTingkat"
                        :filter-kelas-id="$selectedFilterKelas?->id"
                        :has-year-filter="request()->filled('tahun_ajaran_id')"
                        :initial-search="$search"
                    />
                </div>
            </div>
        </section>

        {{-- ===============================================================
             TAB 2: KELOLA RAPOR (Tabel Evaluasi Compact + Side Drawer + Sticky Bar)
             =============================================================== --}}
        <section role="tabpanel"
                 id="tab-panel-kelola"
                 aria-labelledby="tab-btn-kelola"
                 data-workspace-panel="kelola"
                 {!! $activeTab !== 'kelola' ? 'hidden="until-found"' : '' !!}>
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                {{-- Toolbar Pencarian & Filter Status Santri Kontekstual --}}
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h2 id="kelola-rapor-heading" class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                            Evaluasi Sikap &amp; Catatan Wali Kelas
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Klik <strong>Edit Evaluasi</strong> untuk mengisi predikat sikap dan catatan melalui panel samping tanpa meninggalkan daftar santri.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <div class="relative w-full sm:w-64">
                            <label for="kelola-search-input" class="sr-only">Cari nama santri atau NIS</label>
                            <svg aria-hidden="true" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            <input type="search"
                                   id="kelola-search-input"
                                   value="{{ $search }}"
                                   placeholder="Cari nama atau NIS..."
                                   autocorrect="off" autocapitalize="off" spellcheck="false"
                                   class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-base sm:text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:bg-white dark:focus:bg-gray-950">
                        </div>

                        <label for="kelola-status-filter" class="sr-only">Filter Status Rapor</label>
                        <select id="kelola-status-filter"
                                aria-label="Filter berdasarkan status rapor"
                                class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-base sm:text-xs font-medium text-gray-700 dark:text-gray-200">
                            <option value="">Semua Status ({{ $evalSantris->count() }})</option>
                            <option value="draft">Draft ({{ $workflowSummary['draft'] }})</option>
                            <option value="incomplete">Belum Lengkap ({{ $workflowSummary['incomplete'] }})</option>
                            <option value="ready_review">Siap Ditinjau ({{ $workflowSummary['ready_review'] }})</option>
                            <option value="ready_approve">Siap Disetujui ({{ $workflowSummary['ready_approve'] }})</option>
                            <option value="revision">Perlu Revisi ({{ $workflowSummary['revision'] }})</option>
                            <option value="locked">Disetujui / Terbit ({{ $workflowSummary['approved'] + $workflowSummary['published'] }})</option>
                        </select>

                        @if($selectedEvalKelas && $workflowSummary['ready_review'] > 0)
                            <form action="{{ route('admin.reports.validate') }}" method="POST" class="shrink-0">
                                @csrf
                                <input type="hidden" name="kelas_id" value="{{ $selectedEvalKelas->id }}">
                                <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
                                <input type="hidden" name="tab" value="kelola">
                                <button type="submit"
                                        class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-primary-200 bg-primary-50 px-3 text-xs font-semibold text-primary-700 hover:bg-primary-100 dark:border-primary-800 dark:bg-primary-950/60 dark:text-primary-300 transition cursor-pointer">
                                    <span>Validasi Lengkap ({{ $workflowSummary['ready_review'] }})</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($selectedEvalKelas)
                    <form id="bulk-wali-eval-form" action="{{ route('admin.reports.wali-notes', $selectedEvalKelas->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="allow_filtered" value="1">
                        <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
                        <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
                        <input type="hidden" name="filter_kelas_id" value="{{ $selectedFilterKelas?->id }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="tab" value="kelola">
                        <input type="hidden" name="save_mode" id="bulk_save_mode" value="draft">

                        <div tabindex="0" role="region" aria-labelledby="kelola-rapor-heading" class="overflow-x-auto">
                            <table class="min-w-[760px] w-full border-collapse text-left">
                                <thead class="border-b border-gray-200 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Santri</th>
                                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-32">Kelas</th>
                                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-36">Nilai Mapel</th>
                                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Evaluasi Wali Kelas</th>
                                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-36">Status Rapor</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 w-44">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                                    @forelse($evalSantris as $s)
                                        @php
                                            $m = $santriReportMeta[$s->id] ?? [];
                                            $sKey = $m['status_key'] ?? 'draft';
                                            $isLocked = (bool) ($m['is_locked'] ?? false);
                                            $sikap = $m['nilai_sikap'] ?? null;
                                            $catatan = $m['catatan_wali_kelas'] ?? null;
                                            $badgeCls = $statusBadgeMap[$sKey] ?? $statusBadgeMap['draft'];
                                        @endphp
                                        <tr id="kelola-row-{{ $s->id }}"
                                            class="kelola-santri-row hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition-colors"
                                            data-santri-id="{{ $s->id }}"
                                            data-search="{{ strtolower($s->nama_lengkap . ' ' . $s->nis . ' ' . ($m['kelas_nama'] ?? '')) }}"
                                            data-status="{{ $sKey }}"
                                            data-locked="{{ $isLocked ? '1' : '0' }}">
                                            {{-- Hidden inputs untuk penyimpanan kolektif Sticky Action Bar --}}
                                            <td class="px-4 py-3 align-middle">
                                                <input type="hidden"
                                                       id="hidden_sikap_{{ $s->id }}"
                                                       name="evaluasi[{{ $s->id }}][nilai_sikap]"
                                                       data-orig="{{ $sikap ?? '' }}"
                                                       value="{{ $sikap ?? '' }}">
                                                <input type="hidden"
                                                       id="hidden_catatan_{{ $s->id }}"
                                                       name="evaluasi[{{ $s->id }}][catatan_wali_kelas]"
                                                       data-orig="{{ $catatan ?? '' }}"
                                                       value="{{ $catatan ?? '' }}">

                                                <div class="font-semibold text-sm text-gray-900 dark:text-gray-100 leading-snug">
                                                    {{ $s->nama_lengkap }}
                                                </div>
                                                <div class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                                    <span class="font-mono">{{ $s->nis }}</span>
                                                    @if($s->status_santri === 'Lulus')
                                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">Alumni</span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-3 py-3 align-middle whitespace-nowrap text-xs font-medium text-gray-700 dark:text-gray-300">
                                                {{ $m['kelas_nama'] ?? '-' }}
                                            </td>

                                            <td class="px-3 py-3 align-middle whitespace-nowrap">
                                                @php
                                                    $gCnt = $m['graded_mapel_count'] ?? 0;
                                                    $sCnt = $m['scheduled_mapel_count'] ?? 0;
                                                    $mapelOk = ($sCnt > 0 && $gCnt >= $sCnt) || ($sCnt === 0 && $gCnt > 0);
                                                @endphp
                                                <div class="inline-flex items-center gap-1.5 text-xs font-semibold tabular-nums {{ $mapelOk ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                                                    <span class="size-1.5 rounded-full {{ $mapelOk ? 'bg-emerald-500' : 'bg-amber-500' }}" aria-hidden="true"></span>
                                                    <span>{{ $gCnt }}/{{ $sCnt }} mapel</span>
                                                </div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 tabular-nums mt-0.5">
                                                    Rata-rata: {{ $m['avg_score'] !== null ? number_format($m['avg_score'], 2) : '—' }}
                                                </div>
                                            </td>

                                            <td class="px-3 py-3 align-middle max-w-xs">
                                                <div id="eval-summary-{{ $s->id }}" class="space-y-1">
                                                    @if(filled($sikap) || filled($catatan))
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            @if(filled($sikap))
                                                                <span data-role="sikap-badge" class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                                                    {{ $sikap }}
                                                                </span>
                                                            @else
                                                                <span data-role="sikap-badge" class="text-[11px] text-amber-700 dark:text-amber-300 font-medium">
                                                                    Predikat belum dipilih
                                                                </span>
                                                            @endif
                                                            <span data-role="dirty-pill" hidden class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                                                Belum disimpan
                                                            </span>
                                                        </div>
                                                        <p data-role="catatan-preview" class="text-xs text-gray-600 dark:text-gray-400 truncate max-w-[280px]">
                                                            {{ filled($catatan) ? $catatan : 'Catatan wali kelas belum diisi' }}
                                                        </p>
                                                    @else
                                                        <div class="flex items-center gap-1.5">
                                                            <span data-role="sikap-badge" class="text-xs text-gray-400 dark:text-gray-500 italic">Belum diisi</span>
                                                            <span data-role="dirty-pill" hidden class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                                                Belum disimpan
                                                            </span>
                                                        </div>
                                                        <p data-role="catatan-preview" class="text-xs text-gray-600 dark:text-gray-400 truncate max-w-[280px] hidden"></p>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-3 py-3 align-middle whitespace-nowrap">
                                                <span id="status-badge-{{ $s->id }}" class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $badgeCls }}">
                                                    <span>{{ $m['status_label'] ?? 'Draft' }}</span>
                                                    @if($isLocked || ($m['version'] ?? 1) > 1)
                                                        <span class="opacity-75">&middot; v{{ $m['version'] ?? 1 }}</span>
                                                    @endif
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 align-middle text-right whitespace-nowrap">
                                                <div class="inline-flex items-center justify-end gap-1.5">
                                                    @if($isLocked)
                                                        <a href="{{ route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                           class="inline-flex h-8 items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                                                            <span>Unduh PDF</span>
                                                        </a>
                                                    @else
                                                        <button type="button"
                                                                data-open-eval-drawer="{{ $s->id }}"
                                                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 text-xs font-semibold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                                                            <span>
                                                                @if($sKey === 'revision')
                                                                    Perbaiki Rapor
                                                                @elseif($sKey === 'incomplete')
                                                                    Lengkapi Data
                                                                @else
                                                                    Edit Evaluasi
                                                                @endif
                                                            </span>
                                                        </button>
                                                    @endif

                                                    {{-- Menu Aksi Sekunder Tiga Titik (Popover API) --}}
                                                    <button type="button"
                                                            popovertarget="kelola-menu-{{ $s->id }}"
                                                            aria-label="Aksi lainnya untuk {{ $s->nama_lengkap }}"
                                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition cursor-pointer">
                                                        <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                    </button>

                                                    <div id="kelola-menu-{{ $s->id }}"
                                                         popover="auto"
                                                         class="ppdb-action-panel rounded-xl border border-gray-200 bg-white text-left shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                        <a href="{{ route('admin.reports.santri-rapor-pdf', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                           target="_blank"
                                                           class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                                            <span>{{ $isLocked ? 'Pratinjau Rapor' : 'Pratinjau Draft' }}</span>
                                                        </a>
                                                        <a href="{{ route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                           class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                                            <span>Unduh PDF</span>
                                                        </a>
                                                        <button type="button"
                                                                data-open-detail-modal="{{ $s->id }}"
                                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 cursor-pointer">
                                                            <span>
                                                                @if($sKey === 'revision')
                                                                    Lihat Catatan Revisi
                                                                @elseif(! ($m['is_complete'] ?? false))
                                                                    Lihat Kekurangan
                                                                @else
                                                                    Lihat Detail Status
                                                                @endif
                                                            </span>
                                                        </button>
                                                        @if($isLocked && $canApprove)
                                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                                                            <button type="button"
                                                                    data-open-revision-modal="{{ $s->id }}"
                                                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-950/50 cursor-pointer">
                                                                <span>Buka Revisi Resmi</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-xs text-gray-500 dark:text-gray-400">
                                                Tidak ada data santri yang cocok dengan konteks periode dan kelas terpilih.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                @else
                    <div class="p-8 text-center text-xs text-gray-500 dark:text-gray-400">
                        Belum ada data kelas yang tersedia untuk dikelola.
                    </div>
                @endif
            </div>
        </section>

        {{-- ===============================================================
             TAB 3: PERSETUJUAN & CETAK (Workflow 4 Tahap + Validasi Pra-Approval)
             =============================================================== --}}
        <section role="tabpanel"
                 id="tab-panel-persetujuan"
                 aria-labelledby="tab-btn-persetujuan"
                 data-workspace-panel="persetujuan"
                 {!! $activeTab !== 'persetujuan' ? 'hidden="until-found"' : '' !!}>
            <div class="space-y-4">
                {{-- Alur Kerja 4 Tahap Rapor --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">1</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums">{{ $workflowSummary['draft'] + $workflowSummary['incomplete'] + $workflowSummary['revision'] }}</span>
                        </div>
                        <div class="mt-2 text-xs font-bold text-gray-900 dark:text-white">Draft &amp; Kelengkapan</div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Pengisian nilai, sikap, dan catatan wali</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">2</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums">{{ $workflowSummary['ready_review'] + $workflowSummary['ready_approve'] }}</span>
                        </div>
                        <div class="mt-2 text-xs font-bold text-gray-900 dark:text-white">Validasi</div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Lolos syarat wajib &amp; siap ditinjau</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700 dark:bg-primary-950 dark:text-primary-300">3</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums">{{ $workflowSummary['approved'] }}</span>
                        </div>
                        <div class="mt-2 text-xs font-bold text-gray-900 dark:text-white">Approve</div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Disetujui Kepala Sekolah &amp; nilai terkunci</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">4</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums">{{ $workflowSummary['published'] }}</span>
                        </div>
                        <div class="mt-2 text-xs font-bold text-gray-900 dark:text-white">Terbit</div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Dokumen resmi ber-nomor versi siap unduh</p>
                    </div>
                </div>

                {{-- Tabel Review, Persetujuan & Penerbitan Rapor --}}
                <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                        <div>
                            <h2 id="persetujuan-heading" class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                Pemeriksaan Syarat, Persetujuan &amp; Penerbitan Rapor
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Persetujuan hanya aktif apabila komponen nilai mata pelajaran, predikat sikap, dan catatan wali kelas telah lengkap.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <div class="relative w-full sm:w-56">
                                <label for="approval-search-input" class="sr-only">Cari santri di daftar persetujuan</label>
                                <svg aria-hidden="true" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                <input type="search" id="approval-search-input" value="{{ $search }}"
                                       placeholder="Cari nama atau NIS..."
                                       autocorrect="off" autocapitalize="off" spellcheck="false"
                                       class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800 text-base sm:text-xs text-gray-900 dark:text-gray-100">
                            </div>

                            @if($canApprove && $selectedEvalKelas)
                                @php $eligibleForBatch = $workflowSummary['ready_approve'] + $workflowSummary['ready_review']; @endphp
                                <button type="button"
                                        id="open-batch-approve-btn"
                                        {{ $eligibleForBatch === 0 ? 'disabled' : '' }}
                                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-45 px-3.5 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                                    <span>Setujui Rapor Siap Setuju ({{ $eligibleForBatch }})</span>
                                </button>

                                @if($workflowSummary['approved'] > 0)
                                    <form action="{{ route('admin.reports.publish') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="kelas_id" value="{{ $selectedEvalKelas->id }}">
                                        <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
                                        <button type="submit"
                                                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-3 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 transition cursor-pointer">
                                            <span>Terbitkan Semua Disetujui ({{ $workflowSummary['approved'] }})</span>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div tabindex="0" role="region" aria-labelledby="persetujuan-heading" class="overflow-x-auto">
                        <table class="min-w-[780px] w-full border-collapse text-left">
                            <thead class="border-b border-gray-200 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Santri &amp; Kelas</th>
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Syarat Wajib Rapor</th>
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-40">Status &amp; Versi</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 w-52">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                                @forelse($evalSantris as $s)
                                    @php
                                        $m = $santriReportMeta[$s->id] ?? [];
                                        $sKey = $m['status_key'] ?? 'draft';
                                        $isLocked = (bool) ($m['is_locked'] ?? false);
                                        $isComplete = (bool) ($m['is_complete'] ?? false);
                                        $gCnt = $m['graded_mapel_count'] ?? 0;
                                        $sCnt = $m['scheduled_mapel_count'] ?? 0;
                                        $nilaiOk = ($sCnt > 0 && $gCnt >= $sCnt) || ($sCnt === 0 && $gCnt > 0);
                                        $sikapOk = filled($m['nilai_sikap'] ?? null);
                                        $catatanOk = filled($m['catatan_wali_kelas'] ?? null);
                                        $badgeCls = $statusBadgeMap[$sKey] ?? $statusBadgeMap['draft'];
                                    @endphp
                                    <tr class="approval-santri-row hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition-colors"
                                        data-search="{{ strtolower($s->nama_lengkap . ' ' . $s->nis . ' ' . ($m['kelas_nama'] ?? '')) }}">
                                        <td class="px-4 py-3 align-middle">
                                            <div class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ $s->nama_lengkap }}</div>
                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-mono">{{ $s->nis }}</span> &middot; {{ $m['kelas_nama'] ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-3 py-3 align-middle">
                                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-medium {{ $nilaiOk ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                                    <span>{{ $nilaiOk ? '✓' : '•' }} Nilai ({{ $gCnt }}/{{ $sCnt }})</span>
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-medium {{ $sikapOk ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                                    <span>{{ $sikapOk ? '✓ Sikap' : '• Sikap kosong' }}</span>
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-medium {{ $catatanOk ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                                    <span>{{ $catatanOk ? '✓ Catatan' : '• Catatan kosong' }}</span>
                                                </span>
                                            </div>
                                            @if($sKey === 'revision' && filled($m['revision_note'] ?? null))
                                                <p class="mt-1 text-[11px] text-orange-700 dark:text-orange-300 truncate max-w-md">
                                                    Revisi: {{ $m['revision_note'] }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $badgeCls }}">
                                                <span>{{ $m['status_label'] ?? 'Draft' }}</span>
                                                <span class="opacity-75">&middot; v{{ $m['version'] ?? 1 }}.0</span>
                                            </span>
                                            @if($m['approved_at_label'] ?? null)
                                                <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                                    {{ $m['published_at_label'] ?? $m['approved_at_label'] }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-middle text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                {{-- Tindakan Utama Berdasarkan Status Rapor --}}
                                                @if($sKey === 'published')
                                                    <a href="{{ route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                       class="inline-flex h-8 items-center gap-1 rounded-lg bg-primary-600 hover:bg-primary-700 px-3 text-xs font-semibold text-white shadow-2xs transition">
                                                        <span>Unduh PDF</span>
                                                    </a>
                                                @elseif($sKey === 'approved')
                                                    @if($canApprove)
                                                        <form action="{{ route('admin.reports.publish') }}" method="POST" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="santri_id" value="{{ $s->id }}">
                                                            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
                                                            <button type="submit"
                                                                    class="inline-flex h-8 items-center gap-1 rounded-lg bg-primary-600 hover:bg-primary-700 px-3 text-xs font-semibold text-white shadow-2xs transition cursor-pointer">
                                                                <span>Terbitkan</span>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                           class="inline-flex h-8 items-center gap-1 rounded-lg bg-primary-600 hover:bg-primary-700 px-3 text-xs font-semibold text-white shadow-2xs transition">
                                                            <span>Unduh PDF</span>
                                                        </a>
                                                    @endif
                                                @elseif($isComplete && $sKey !== 'revision')
                                                    @if($canApprove)
                                                        <button type="button"
                                                                data-open-approve-modal="{{ $s->id }}"
                                                                class="inline-flex h-8 items-center gap-1 rounded-lg bg-primary-600 hover:bg-primary-700 px-3 text-xs font-semibold text-white shadow-2xs transition cursor-pointer">
                                                            <span>{{ $sKey === 'ready_approve' ? 'Setujui Rapor' : 'Tinjau Rapor' }}</span>
                                                        </button>
                                                    @elseif($sKey === 'ready_review')
                                                        <form action="{{ route('admin.reports.validate') }}" method="POST" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="santri_id" value="{{ $s->id }}">
                                                            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
                                                            <button type="submit"
                                                                    class="inline-flex h-8 items-center gap-1 rounded-lg border border-primary-300 bg-primary-50 px-3 text-xs font-semibold text-primary-700 hover:bg-primary-100 dark:border-primary-800 dark:bg-primary-950/60 dark:text-primary-300 transition cursor-pointer">
                                                                <span>Validasi Rapor</span>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 px-2">Menunggu Kepala Sekolah</span>
                                                    @endif
                                                @elseif($sKey === 'revision')
                                                    <button type="button"
                                                            data-open-eval-drawer="{{ $s->id }}"
                                                            class="inline-flex h-8 items-center gap-1 rounded-lg border border-orange-300 bg-orange-50 px-3 text-xs font-semibold text-orange-800 hover:bg-orange-100 dark:border-orange-800 dark:bg-orange-950/50 dark:text-orange-300 transition cursor-pointer">
                                                        <span>Perbaiki Rapor</span>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            data-open-eval-drawer="{{ $s->id }}"
                                                            class="inline-flex h-8 items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                                                        <span>{{ $sKey === 'incomplete' ? 'Lengkapi Data' : 'Edit Evaluasi' }}</span>
                                                    </button>
                                                @endif

                                                {{-- Menu Aksi Sekunder Tiga Titik --}}
                                                <button type="button"
                                                        popovertarget="approval-menu-{{ $s->id }}"
                                                        aria-label="Opsi rapor {{ $s->nama_lengkap }}"
                                                        class="inline-flex size-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition cursor-pointer">
                                                    <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                </button>

                                                <div id="approval-menu-{{ $s->id }}"
                                                     popover="auto"
                                                     class="ppdb-action-panel rounded-xl border border-gray-200 bg-white text-left shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                    <a href="{{ route('admin.reports.santri-rapor-pdf', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                       target="_blank"
                                                       class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                                        <span>{{ $isLocked ? 'Pratinjau' : 'Pratinjau Draft' }}</span>
                                                    </a>
                                                    <a href="{{ route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                       class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                                        <span>Unduh PDF</span>
                                                    </a>
                                                    <button type="button"
                                                            data-open-detail-modal="{{ $s->id }}"
                                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 cursor-pointer">
                                                        <span>
                                                            @if($sKey === 'revision')
                                                                Lihat Catatan Revisi
                                                            @elseif(! $isComplete)
                                                                Lihat Kekurangan
                                                            @else
                                                                Lihat Detail
                                                            @endif
                                                        </span>
                                                    </button>
                                                    @if($isLocked && $canApprove)
                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                                                        <button type="button"
                                                                data-open-revision-modal="{{ $s->id }}"
                                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-950/50 cursor-pointer">
                                                            <span>Buka Revisi Resmi</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-xs text-gray-500 dark:text-gray-400">
                                            Tidak ada data santri pada konteks periode dan kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===============================================================
             TAB 4: RIWAYAT (Arsip Dokumen Terbit & Log Persetujuan)
             =============================================================== --}}
        <section role="tabpanel"
                 id="tab-panel-riwayat"
                 aria-labelledby="tab-btn-riwayat"
                 data-workspace-panel="riwayat"
                 {!! $activeTab !== 'riwayat' ? 'hidden="until-found"' : '' !!}>
            <div class="space-y-5">
                {{-- A. Arsip Dokumen Rapor Ber-Nomor Versi --}}
                <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                        <h2 id="arsip-dokumen-heading" class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                            Arsip Dokumen Rapor &amp; Versi Penerbitan
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Rekam dokumen rapor yang telah disetujui, diterbitkan, atau memiliki riwayat revisi resmi pada periode terpilih.
                        </p>
                    </div>

                    <div tabindex="0" role="region" aria-labelledby="arsip-dokumen-heading" class="overflow-x-auto">
                        <table class="min-w-[700px] w-full border-collapse text-left">
                            <thead class="border-b border-gray-200 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Santri</th>
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</th>
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Versi &amp; Status</th>
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Rekam Persetujuan</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Dokumen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                                @forelse($archivedRapors as $s)
                                    @php
                                        $m = $santriReportMeta[$s->id] ?? [];
                                        $sKey = $m['status_key'] ?? 'approved';
                                        $badgeCls = $statusBadgeMap[$sKey] ?? $statusBadgeMap['approved'];
                                    @endphp
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                        <td class="px-4 py-3 text-xs">
                                            <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ $s->nama_lengkap }}</div>
                                            <div class="font-mono text-gray-500 dark:text-gray-400">{{ $s->nis }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ $m['kelas_nama'] ?? '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-xs whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $badgeCls }}">
                                                v{{ $m['version'] ?? 1 }}.0 &middot; {{ $m['status_label'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-xs text-gray-600 dark:text-gray-300">
                                            <div>Oleh: <strong>{{ $m['approved_by_name'] ?? '-' }}</strong></div>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ $m['published_at_label'] ? 'Terbit: '.$m['published_at_label'] : ($m['approved_at_label'] ? 'Disetujui: '.$m['approved_at_label'] : '-') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('admin.reports.santri-rapor-pdf', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                   target="_blank"
                                                   class="inline-flex h-8 items-center rounded-lg border border-gray-200 bg-white px-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                                                    Pratinjau
                                                </a>
                                                <a href="{{ route('admin.reports.santri-rapor.download', array_filter(['santri' => $s->id, 'tahun_ajaran_id' => $selectedYear?->id])) }}"
                                                   class="inline-flex h-8 items-center rounded-lg bg-primary-600 hover:bg-primary-700 px-2.5 text-xs font-semibold text-white transition">
                                                    Unduh PDF
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                                            Belum ada dokumen rapor yang disetujui atau diterbitkan pada konteks ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- B. Log Audit Persetujuan, Revisi & Cetak --}}
                <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                        <h2 id="audit-log-heading" class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                            Riwayat Aktivitas &amp; Persetujuan
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Jejak audit perubahan evaluasi, validasi, persetujuan, penerbitan, revisi resmi, dan ekspor laporan.
                        </p>
                    </div>

                    <div tabindex="0" role="region" aria-labelledby="audit-log-heading" class="overflow-x-auto">
                        <table class="min-w-[640px] w-full border-collapse text-left">
                            <thead class="border-b border-gray-200 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-40">Waktu</th>
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-40">Pengguna</th>
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Aksi</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                                @forelse($historyLogs as $log)
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                        <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap tabular-nums">
                                            {{ $log->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2.5 text-xs font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                            {{ $log->user_name }}
                                        </td>
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            <span class="inline-flex rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $log->actionColor() }}">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-xs text-gray-700 dark:text-gray-300">
                                            {{ $log->description }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                                            Belum ada riwayat aktivitas rapor yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- =======================================================================
     STICKY ACTION BAR (Tampil HANYA ketika terdapat perubahan belum disimpan)
     ======================================================================= --}}
<div id="sticky-unsaved-bar"
     hidden
     role="region"
     aria-label="Status perubahan evaluasi belum disimpan"
     class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 w-[min(44rem,calc(100vw-2rem))] rounded-xl border border-gray-300 bg-gray-900/95 px-4 py-2.5 text-white shadow-xl backdrop-blur dark:border-gray-700 dark:bg-gray-950/95">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 text-xs">
            <span class="size-2 rounded-full bg-amber-400 animate-pulse" aria-hidden="true"></span>
            <span id="sticky-dirty-count" class="font-semibold">0 perubahan belum disimpan</span>
            <span class="hidden sm:inline text-gray-400">&middot; Tersimpan sebagai draft lokal</span>
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <button type="button" id="sticky-discard-btn"
                    class="h-9 rounded-lg px-3 text-xs font-semibold text-gray-300 hover:bg-white/10 hover:text-white transition cursor-pointer">
                Reset
            </button>
            <button type="button" id="sticky-save-draft-btn"
                    class="h-9 rounded-lg bg-primary-600 hover:bg-primary-500 px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>

@include('reports.partials.workspace-overlays')

@once
    <script type="module">
        if (!("popover" in HTMLElement.prototype)) {
            import("https://unpkg.com/@oddbird/popover-polyfill@latest/dist/popover.min.js");
        }
    </script>
@endonce

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const santriData = @json($santriJsDataset);
        const santriOrder = Object.keys(santriData).map(Number);
        const supportsBeforeMatch = 'onbeforematch' in HTMLElement.prototype;
        const storageKey = `nk_rapor_draft_${@json($selectedYear?->id ?? 'all')}_${@json($selectedEvalKelas?->id ?? 'all')}`;

        // ── 1. Workspace Tabs + Find-in-Page Sync ──────────────────────────
        const tabsWrapper = document.getElementById('reports-workspace-tabs');
        const tabButtons = Array.from(document.querySelectorAll('[data-workspace-tab]'));
        const tabPanels = Array.from(document.querySelectorAll('[data-workspace-panel]'));
        const contextTabInput = document.getElementById('context_active_tab');
        const drawerTabInput = document.getElementById('drawer_active_tab');

        function activateWorkspaceTab(targetTab, updateHistory = true) {
            tabButtons.forEach((btn) => {
                const isMatch = btn.dataset.workspaceTab === targetTab;
                btn.setAttribute('aria-selected', isMatch ? 'true' : 'false');
                btn.setAttribute('tabindex', isMatch ? '0' : '-1');
                btn.classList.toggle('border-primary-600', isMatch);
                btn.classList.toggle('text-primary-700', isMatch);
                btn.classList.toggle('dark:border-primary-400', isMatch);
                btn.classList.toggle('dark:text-primary-300', isMatch);
                btn.classList.toggle('border-transparent', !isMatch);
                btn.classList.toggle('text-gray-600', !isMatch);
                btn.classList.toggle('dark:text-gray-400', !isMatch);
            });

            tabPanels.forEach((panel) => {
                if (panel.dataset.workspacePanel === targetTab) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.hidden = supportsBeforeMatch ? 'until-found' : true;
                }
            });

            if (contextTabInput) contextTabInput.value = targetTab;
            if (drawerTabInput) drawerTabInput.value = targetTab;

            if (updateHistory && window.history && window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', targetTab);
                window.history.replaceState({}, '', url.toString());
            }
        }

        // Fallback untuk browser tanpa hidden="until-found" (mis. Safari)
        if (!supportsBeforeMatch) {
            tabPanels.forEach((panel) => {
                if (panel.getAttribute('hidden') === 'until-found') {
                    panel.hidden = true;
                }
            });
        }

        tabButtons.forEach((btn, idx) => {
            btn.addEventListener('click', () => activateWorkspaceTab(btn.dataset.workspaceTab));
            btn.addEventListener('keydown', (e) => {
                let nextIdx = null;
                if (e.key === 'ArrowRight') nextIdx = (idx + 1) % tabButtons.length;
                if (e.key === 'ArrowLeft') nextIdx = (idx - 1 + tabButtons.length) % tabButtons.length;
                if (e.key === 'Home') nextIdx = 0;
                if (e.key === 'End') nextIdx = tabButtons.length - 1;
                if (nextIdx !== null) {
                    e.preventDefault();
                    tabButtons[nextIdx].focus();
                    activateWorkspaceTab(tabButtons[nextIdx].dataset.workspaceTab);
                }
            });
        });

        if (tabsWrapper && supportsBeforeMatch) {
            tabsWrapper.addEventListener('beforematch', (e) => {
                const matchedPanel = e.target.closest('[data-workspace-panel]');
                if (matchedPanel) {
                    activateWorkspaceTab(matchedPanel.dataset.workspacePanel);
                }
            });
        }

        // ── 2. Global Context Filter Auto-Submit ───────────────────────────
        const contextForm = document.getElementById('reports-context-form');
        ['filter_tahun_ajaran_id', 'filter_tingkat', 'filter_kelas_id'].forEach((id) => {
            const el = document.getElementById(id);
            if (el && contextForm) {
                el.addEventListener('change', () => contextForm.submit());
            }
        });

        // ── 3. Contextual Table Search & Status Filtering ──────────────────
        const kelolaSearch = document.getElementById('kelola-search-input');
        const kelolaStatusFilter = document.getElementById('kelola-status-filter');
        const approvalSearch = document.getElementById('approval-search-input');

        function filterKelolaRows() {
            const q = (kelolaSearch?.value || '').toLowerCase().trim();
            const st = kelolaStatusFilter?.value || '';
            document.querySelectorAll('.kelola-santri-row').forEach((row) => {
                const hay = row.dataset.search || '';
                const rowSt = row.dataset.status || '';
                const isLocked = row.dataset.locked === '1';
                const matchSearch = !q || hay.includes(q);
                const matchStatus = !st || (st === 'locked' ? isLocked : rowSt === st);
                row.hidden = !(matchSearch && matchStatus);
            });
        }

        kelolaSearch?.addEventListener('input', filterKelolaRows);
        kelolaStatusFilter?.addEventListener('change', filterKelolaRows);

        approvalSearch?.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.approval-santri-row').forEach((row) => {
                const hay = row.dataset.search || '';
                row.hidden = !(!q || hay.includes(q));
            });
        });

        // ── 5. Dirty State Tracking, Autosave Draft Lokal & Sticky Bar ─────
        const stickyBar = document.getElementById('sticky-unsaved-bar');
        const stickyCountEl = document.getElementById('sticky-dirty-count');
        const stickyDiscardBtn = document.getElementById('sticky-discard-btn');
        const stickySaveBtn = document.getElementById('sticky-save-draft-btn');
        const bulkForm = document.getElementById('bulk-wali-eval-form');
        let dirtyIds = new Set();
        let allowNavigationWithoutPrompt = false;

        function updateRowSummaryDom(santriId, sikap, catatan, isDirty) {
            const summaryWrap = document.getElementById(`eval-summary-${santriId}`);
            if (!summaryWrap) return;

            const badgeEl = summaryWrap.querySelector('[data-role="sikap-badge"]');
            const dirtyPill = summaryWrap.querySelector('[data-role="dirty-pill"]');
            const previewEl = summaryWrap.querySelector('[data-role="catatan-preview"]');

            if (badgeEl) {
                if (sikap) {
                    badgeEl.textContent = sikap;
                    badgeEl.className = 'inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-800 dark:bg-gray-800 dark:text-gray-200';
                } else if (catatan) {
                    badgeEl.textContent = 'Predikat belum dipilih';
                    badgeEl.className = 'text-[11px] text-amber-700 dark:text-amber-300 font-medium';
                } else {
                    badgeEl.textContent = 'Belum diisi';
                    badgeEl.className = 'text-xs text-gray-400 dark:text-gray-500 italic';
                }
            }

            if (previewEl) {
                if (sikap || catatan) {
                    previewEl.textContent = catatan || 'Catatan wali kelas belum diisi';
                    previewEl.classList.remove('hidden');
                } else {
                    previewEl.textContent = '';
                    previewEl.classList.add('hidden');
                }
            }

            if (dirtyPill) {
                dirtyPill.hidden = !isDirty;
            }
        }

        function refreshStickyBar() {
            const count = dirtyIds.size;
            if (stickyBar) {
                stickyBar.hidden = count === 0;
            }
            if (stickyCountEl) {
                stickyCountEl.textContent = `${count} perubahan belum disimpan`;
            }

            try {
                if (count === 0) {
                    sessionStorage.removeItem(storageKey);
                } else {
                    const draftPayload = {};
                    dirtyIds.forEach((id) => {
                        const sInput = document.getElementById(`hidden_sikap_${id}`);
                        const cInput = document.getElementById(`hidden_catatan_${id}`);
                        if (sInput && cInput) {
                            draftPayload[id] = { sikap: sInput.value, catatan: cInput.value };
                        }
                    });
                    sessionStorage.setItem(storageKey, JSON.stringify(draftPayload));
                }
            } catch (_) {}
        }

        function applyStagedChange(santriId, sikap, catatan) {
            const sInput = document.getElementById(`hidden_sikap_${santriId}`);
            const cInput = document.getElementById(`hidden_catatan_${santriId}`);
            if (!sInput || !cInput) return;

            sInput.value = sikap;
            cInput.value = catatan;

            const isModified = (sInput.value !== (sInput.dataset.orig || ''))
                || (cInput.value !== (cInput.dataset.orig || ''));

            if (isModified) {
                dirtyIds.add(Number(santriId));
            } else {
                dirtyIds.delete(Number(santriId));
            }

            if (santriData[santriId]) {
                santriData[santriId].nilai_sikap = sikap;
                santriData[santriId].catatan_wali_kelas = catatan;
            }

            updateRowSummaryDom(santriId, sikap, catatan, isModified);
            refreshStickyBar();
        }

        // Pulihkan autosave draft lokal sesi apabila pengguna sempat memuat ulang halaman
        try {
            const rawSaved = sessionStorage.getItem(storageKey);
            if (rawSaved) {
                const parsed = JSON.parse(rawSaved);
                Object.entries(parsed).forEach(([id, val]) => {
                    if (santriData[id] && !santriData[id].is_locked) {
                        applyStagedChange(Number(id), val.sikap || '', val.catatan || '');
                    }
                });
            }
        } catch (_) {}

        stickyDiscardBtn?.addEventListener('click', () => {
            Array.from(dirtyIds).forEach((id) => {
                const sInput = document.getElementById(`hidden_sikap_${id}`);
                const cInput = document.getElementById(`hidden_catatan_${id}`);
                if (sInput && cInput) {
                    applyStagedChange(id, sInput.dataset.orig || '', cInput.dataset.orig || '');
                }
            });
            if (currentDrawerSantriId && santriData[currentDrawerSantriId]) {
                populateDrawer(currentDrawerSantriId);
            }
        });

        stickySaveBtn?.addEventListener('click', () => {
            if (!bulkForm) return;
            allowNavigationWithoutPrompt = true;
            try { sessionStorage.removeItem(storageKey); } catch (_) {}
            bulkForm.submit();
        });

        window.addEventListener('beforeunload', (e) => {
            if (!allowNavigationWithoutPrompt && dirtyIds.size > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // ── 6. Non-Modal Side Drawer Evaluasi Santri ───────────────────────
        const evalDrawer = document.getElementById('rapor-eval-drawer');
        const drawerForm = document.getElementById('drawer-eval-form');
        const drawerSikap = document.getElementById('drawer_nilai_sikap');
        const drawerCatatan = document.getElementById('drawer_catatan_wali');
        const drawerCharCount = document.getElementById('drawer-char-count');
        const drawerFeedback = document.getElementById('drawer-feedback');
        const drawerSaveDraftBtn = document.getElementById('drawer-save-draft-btn');
        const drawerSaveValidateBtn = document.getElementById('drawer-save-validate-btn');
        const drawerPrevBtn = document.getElementById('drawer-prev-btn');
        const drawerNextBtn = document.getElementById('drawer-next-btn');
        const drawerCloseBtn = document.getElementById('drawer-close-btn');
        let currentDrawerSantriId = null;

        function updateCharCounter() {
            if (drawerCharCount && drawerCatatan) {
                drawerCharCount.textContent = `${drawerCatatan.value.length} / 1000`;
            }
        }

        function populateDrawer(santriId) {
            const item = santriData[santriId];
            if (!item || !evalDrawer) return;
            currentDrawerSantriId = Number(santriId);

            document.getElementById('drawer-santri-subtitle').textContent = `${item.nama} · ${item.nis} · ${item.kelas}`;
            document.getElementById('drawer-status-badge').textContent = item.status_label;
            document.getElementById('drawer-ref-mapel').textContent = `${item.graded_mapel_count}/${item.scheduled_mapel_count}`;
            document.getElementById('drawer-ref-avg').textContent = item.avg_score !== null ? Number(item.avg_score).toFixed(2) : '—';
            document.getElementById('drawer-ref-absen').textContent = `${item.hadir}/${item.izin}/${item.sakit}/${item.alpa}`;

            const revAlert = document.getElementById('drawer-revision-alert');
            const revText = document.getElementById('drawer-revision-text');
            if (revAlert && revText) {
                if (item.status_key === 'revision' && item.revision_note) {
                    revText.textContent = item.revision_note;
                    revAlert.hidden = false;
                } else {
                    revAlert.hidden = true;
                }
            }

            const lockedAlert = document.getElementById('drawer-locked-alert');
            const unlockedActions = document.getElementById('drawer-Unlocked-actions');
            if (lockedAlert) lockedAlert.hidden = !item.is_locked;
            if (unlockedActions) unlockedActions.hidden = item.is_locked;

            const missingBox = document.getElementById('drawer-missing-box');
            const missingList = document.getElementById('drawer-missing-list');
            if (missingBox && missingList) {
                if (item.missing_items && item.missing_items.length > 0 && !item.is_locked) {
                    missingList.textContent = item.missing_items.join(', ');
                    missingBox.hidden = false;
                } else {
                    missingBox.hidden = true;
                }
            }

            if (drawerForm) drawerForm.action = item.eval_url;
            if (drawerSikap) {
                drawerSikap.value = item.nilai_sikap || '';
                drawerSikap.disabled = item.is_locked;
            }
            if (drawerCatatan) {
                drawerCatatan.value = item.catatan_wali_kelas || '';
                drawerCatatan.disabled = item.is_locked;
            }
            updateCharCounter();

            const prevLink = document.getElementById('drawer-preview-link');
            if (prevLink) prevLink.href = item.preview_url;

            if (drawerFeedback) drawerFeedback.hidden = true;

            const idx = santriOrder.indexOf(currentDrawerSantriId);
            if (drawerPrevBtn) drawerPrevBtn.disabled = idx <= 0;
            if (drawerNextBtn) drawerNextBtn.disabled = idx === -1 || idx >= santriOrder.length - 1;
        }

        function openEvalDrawer(santriId) {
            populateDrawer(santriId);
            if (!evalDrawer) return;
            if (typeof evalDrawer.showPopover === 'function') {
                if (!evalDrawer.matches(':popover-open')) {
                    evalDrawer.showPopover();
                }
            } else {
                evalDrawer.setAttribute('open', '');
            }
            drawerSikap?.focus();
        }

        function closeEvalDrawer() {
            if (!evalDrawer) return;
            if (typeof evalDrawer.hidePopover === 'function') {
                evalDrawer.hidePopover();
            } else {
                evalDrawer.removeAttribute('open');
            }
        }

        document.querySelectorAll('[data-open-eval-drawer]').forEach((btn) => {
            btn.addEventListener('click', () => openEvalDrawer(Number(btn.dataset.openEvalDrawer)));
        });

        drawerCloseBtn?.addEventListener('click', closeEvalDrawer);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && evalDrawer && (evalDrawer.matches?.(':popover-open') || evalDrawer.hasAttribute('open'))) {
                const anyModalOpen = document.querySelector('dialog:modal');
                if (!anyModalOpen) closeEvalDrawer();
            }
        });

        drawerPrevBtn?.addEventListener('click', () => {
            const idx = santriOrder.indexOf(currentDrawerSantriId);
            if (idx > 0) openEvalDrawer(santriOrder[idx - 1]);
        });

        drawerNextBtn?.addEventListener('click', () => {
            const idx = santriOrder.indexOf(currentDrawerSantriId);
            if (idx !== -1 && idx < santriOrder.length - 1) openEvalDrawer(santriOrder[idx + 1]);
        });

        // Sinkronisasi langsung perubahan di Drawer ke daftar & Sticky Action Bar
        function onDrawerFieldInput() {
            updateCharCounter();
            if (currentDrawerSantriId && !santriData[currentDrawerSantriId]?.is_locked) {
                applyStagedChange(
                    currentDrawerSantriId,
                    drawerSikap?.value || '',
                    drawerCatatan?.value || ''
                );
            }
        }

        drawerSikap?.addEventListener('change', onDrawerFieldInput);
        drawerCatatan?.addEventListener('input', onDrawerFieldInput);

        async function submitSingleDrawerEvaluation(mode) {
            if (!currentDrawerSantriId || !drawerForm) return;
            const item = santriData[currentDrawerSantriId];
            if (!item) return;

            document.getElementById('drawer_save_mode').value = mode;
            const formData = new FormData(drawerForm);

            drawerSaveDraftBtn.disabled = true;
            drawerSaveValidateBtn.disabled = true;

            try {
                const response = await fetch(item.eval_url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const result = await response.json();
                if (!response.ok || !result.ok) {
                    throw new Error(result.message || 'Gagal menyimpan evaluasi santri.');
                }

                const sInput = document.getElementById(`hidden_sikap_${currentDrawerSantriId}`);
                const cInput = document.getElementById(`hidden_catatan_${currentDrawerSantriId}`);
                if (sInput) {
                    sInput.dataset.orig = result.nilai_sikap || '';
                    sInput.value = result.nilai_sikap || '';
                }
                if (cInput) {
                    cInput.dataset.orig = result.catatan_wali_kelas || '';
                    cInput.value = result.catatan_wali_kelas || '';
                }

                dirtyIds.delete(currentDrawerSantriId);
                item.nilai_sikap = result.nilai_sikap || '';
                item.catatan_wali_kelas = result.catatan_wali_kelas || '';
                item.missing_items = result.missing_items || [];
                item.is_complete = item.missing_items.length === 0;
                if (result.validated) {
                    item.status_key = 'ready_approve';
                    item.status_label = 'Siap Disetujui';
                } else if (item.is_complete) {
                    item.status_key = 'ready_review';
                    item.status_label = 'Siap Ditinjau';
                } else if (item.nilai_sikap || item.catatan_wali_kelas) {
                    item.status_key = 'incomplete';
                    item.status_label = 'Belum Lengkap';
                }

                const rowBadge = document.getElementById(`status-badge-${currentDrawerSantriId}`);
                if (rowBadge) rowBadge.textContent = item.status_label;
                document.getElementById('drawer-status-badge').textContent = item.status_label;

                updateRowSummaryDom(currentDrawerSantriId, item.nilai_sikap, item.catatan_wali_kelas, false);
                refreshStickyBar();

                if (drawerFeedback) {
                    drawerFeedback.textContent = result.message;
                    drawerFeedback.className = result.warning
                        ? 'rounded-lg px-3 py-2 text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800'
                        : 'rounded-lg px-3 py-2 text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800';
                    drawerFeedback.hidden = false;
                }
            } catch (err) {
                if (drawerFeedback) {
                    drawerFeedback.textContent = err.message || 'Terjadi kesalahan jaringan saat menyimpan.';
                    drawerFeedback.className = 'rounded-lg px-3 py-2 text-xs font-medium bg-error-50 text-error-700 border border-error-200 dark:bg-error-950/50 dark:text-error-200';
                    drawerFeedback.hidden = false;
                }
            } finally {
                drawerSaveDraftBtn.disabled = false;
                drawerSaveValidateBtn.disabled = false;
            }
        }

        drawerSaveDraftBtn?.addEventListener('click', () => submitSingleDrawerEvaluation('draft'));
        drawerSaveValidateBtn?.addEventListener('click', () => submitSingleDrawerEvaluation('validate'));

        // ── 7. Blocking Modal Dialogs (Review Approval, Batch, Detail, Revision) ──
        document.querySelectorAll('dialog').forEach((dlg) => {
            dlg.addEventListener('close', () => {
                dlg.returnValue = '';
            });
        });

        document.querySelectorAll('[data-close-dialog]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const dlg = document.getElementById(btn.dataset.closeDialog);
                dlg?.close();
            });
        });

        const approveDialog = document.getElementById('approve-review-dialog');
        const approveForm = document.getElementById('approve-review-form');
        document.querySelectorAll('[data-open-approve-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const item = santriData[btn.dataset.openApproveModal];
                if (!item || !approveDialog || !approveForm) return;

                document.getElementById('review-modal-santri').textContent = `${item.nama} (${item.nis})`;
                document.getElementById('review-modal-kelas').textContent = `${item.kelas} · Versi v${item.version}.0`;
                document.getElementById('review-modal-nilai').textContent = `${item.graded_mapel_count}/${item.scheduled_mapel_count} mapel (Rata-rata: ${item.avg_score !== null ? Number(item.avg_score).toFixed(2) : '—'})`;
                document.getElementById('review-modal-sikap').textContent = item.nilai_sikap || '-';
                document.getElementById('review-modal-catatan').textContent = item.catatan_wali_kelas || '-';
                approveForm.action = item.approve_url;

                approveDialog.showModal();
            });
        });

        const batchApproveBtn = document.getElementById('open-batch-approve-btn');
        const batchApproveDialog = document.getElementById('batch-approve-dialog');
        batchApproveBtn?.addEventListener('click', () => batchApproveDialog?.showModal());

        const detailDialog = document.getElementById('detail-status-dialog');
        document.querySelectorAll('[data-open-detail-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const item = santriData[btn.dataset.openDetailModal];
                if (!item || !detailDialog) return;

                document.getElementById('detail-status-subtitle').textContent = `${item.nama} · ${item.nis} · ${item.kelas}`;
                const body = document.getElementById('detail-status-body');
                body.replaceChildren();

                const addLine = (label, val) => {
                    const row = document.createElement('div');
                    const strong = document.createElement('strong');
                    strong.textContent = `${label}: `;
                    row.appendChild(strong);
                    row.appendChild(document.createTextNode(val));
                    body.appendChild(row);
                };

                addLine('Status Rapor', `${item.status_label} (Versi v${item.version}.0)`);
                addLine('Capaian Mapel', `${item.graded_mapel_count}/${item.scheduled_mapel_count} mata pelajaran dinilai`);
                addLine('Predikat Sikap', item.nilai_sikap || 'Belum dipilih');

                if (item.missing_items && item.missing_items.length > 0 && !item.is_locked) {
                    addLine('Kekurangan Syarat', item.missing_items.join(', '));
                }
                if (item.approved_by_name) {
                    addLine('Disetujui Oleh', `${item.approved_by_name} (${item.approved_at_label || '-'})`);
                }
                if (item.published_at_label) {
                    addLine('Waktu Penerbitan', item.published_at_label);
                }
                if (item.revision_note) {
                    addLine('Catatan Revisi Resmi', `${item.revision_note}${item.revision_by_name ? ' — oleh ' + item.revision_by_name : ''}`);
                }

                detailDialog.showModal();
            });
        });

        const revisionDialog = document.getElementById('revision-request-dialog');
        const revisionForm = document.getElementById('revision-request-form');
        document.querySelectorAll('[data-open-revision-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const item = santriData[btn.dataset.openRevisionModal];
                if (!item || !revisionDialog || !revisionForm) return;

                document.getElementById('revision-request-subtitle').textContent =
                    `Buka revisi resmi untuk ${item.nama} (${item.nis}, saat ini v${item.version}.0). Setelah diperbaiki dan disetujui ulang, dokumen akan terbit sebagai Versi v${Number(item.version) + 1}.0.`;
                revisionForm.action = item.revision_url;
                document.getElementById('revision_note_input').value = '';
                revisionDialog.showModal();
            });
        });

        // Fallback posisi popover pada browser yang belum mendukung CSS Anchor Positioning
        if (!CSS.supports('position-area', 'block-end')) {
            document.querySelectorAll('.ppdb-action-panel[popover]').forEach((panel) => {
                panel.addEventListener('beforetoggle', (e) => {
                    if (e.newState !== 'open') return;
                    const trigger = document.querySelector(`[popovertarget="${panel.id}"]`);
                    if (!trigger) return;
                    const rect = trigger.getBoundingClientRect();
                    panel.style.top = `${Math.min(rect.bottom + 6, window.innerHeight - 180)}px`;
                    panel.style.left = `${Math.max(12, rect.right - 190)}px`;
                });
            });
        }
    });
</script>
@endsection
