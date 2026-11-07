@extends('layouts.admin')

@section('title', 'Portal Wali Santri')

@section('content')

@include('santri.partials.portal-filter-bar', [
    'actionRoute' => route('wali.dashboard'),
    'headingId' => 'wali-dashboard-heading',
])

@if(!$santri)
    <div class="rounded-xl border border-gray-200/90 bg-white p-8 text-center shadow-2xs dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            <svg class="size-5" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
        </div>
        <h2 class="mt-3 text-base font-bold text-gray-900 dark:text-white">Data Santri Tidak Ditemukan</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
            Tidak ada santri yang sesuai dengan filter pencarian saat ini, atau akun belum terhubung dengan data santri aktif.
        </p>
    </div>
@else
    @php
        $isMonitoringStaff = !auth()->user()->isSantri();
        $queryParams = array_filter([
            'santri_id' => $isMonitoringStaff ? $santri->id : null,
            'tahun_ajaran_id' => $tahunAjaran?->id,
            'tingkat' => $isMonitoringStaff ? request('tingkat') : null,
            'filter_kelas_id' => $isMonitoringStaff ? request('filter_kelas_id') : null,
            'search' => $isMonitoringStaff ? request('search') : null,
        ], fn ($v) => $v !== null && $v !== '');

        $displayKelas = $kelasPeriode ?? $santri->kelas;
        $shortPeriod = isset($tahunAjaran) && $tahunAjaran
            ? ($tahunAjaran->nama_tahun_ajaran . ' · ' . $tahunAjaran->semester)
            : 'Semester Aktif';

        $totalPertemuan = $totalPertemuan ?? ($totalHadir + $totalIzin + $totalSakit + $totalAlpa);
        $izinSakitTotal = $totalIzin + $totalSakit;
        $hadirPercent = $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100) : null;
        $hasValidGrades = ($gradedCount ?? 0) > 0;

        $todayName = $todayName ?? 'Hari Ini';
        $todaySchedules = $todaySchedules ?? collect();
        $defaultScheduleTab = ($todaySchedules->isNotEmpty() || $schedules->isEmpty()) ? 'today' : 'weekly';

        $dashCatatan = $catatanWaliKelas ?? null;
        $dashSikap = $nilaiSikap ?? null;
        $chartSubjects = $chartSubjects ?? [];
        $semesterTrend = $semesterTrend ?? [];
    @endphp

    {{-- 1. KPI Akademik Netral dengan Denominator Kehadiran & Validasi Nilai --}}
    <section aria-labelledby="wali-kpi-heading" class="mb-6">
        <div class="mb-2.5 flex flex-wrap items-center justify-between gap-2">
            <h2 id="wali-kpi-heading" class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                Ringkasan Akademik
            </h2>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                Periode {{ $shortPeriod }}{{ (isset($tahunAjaran) && $tahunAjaran && !$tahunAjaran->status_aktif) ? ' (Arsip)' : '' }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
            {{-- KPI 1: Kehadiran --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200/90 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kehadiran</span>
                    @if($hadirPercent !== null)
                        <span class="inline-flex items-center gap-1 rounded-md bg-success-50 px-2 py-0.5 text-[11px] font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-300 tabular-nums">
                            <span class="size-1.5 rounded-full bg-success-500" aria-hidden="true"></span>
                            {{ $hadirPercent }}%
                        </span>
                    @else
                        <span class="size-2 rounded-full bg-gray-300 dark:bg-gray-700" aria-hidden="true"></span>
                    @endif
                </div>
                <div class="mt-2.5">
                    @if($totalPertemuan > 0)
                        <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">
                            {{ $totalHadir }}
                            <span class="text-sm font-medium text-gray-400 dark:text-gray-500">/ {{ $totalPertemuan }}</span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $totalHadir }} dari {{ $totalPertemuan }} pertemuan
                        </p>
                    @else
                        <p class="text-2xl font-bold tracking-tight text-gray-400 dark:text-gray-500">—</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Belum ada pertemuan tercatat</p>
                    @endif
                </div>
            </div>

            {{-- KPI 2: Izin & Sakit (Digabung menjadi satu indikator) --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200/90 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Izin &amp; Sakit</span>
                    <span class="size-2 rounded-full {{ $izinSakitTotal > 0 ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-700' }}" aria-hidden="true"></span>
                </div>
                <div class="mt-2.5">
                    @if($totalPertemuan > 0)
                        <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">{{ $izinSakitTotal }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Izin {{ $totalIzin }} · Sakit {{ $totalSakit }} (dari {{ $totalPertemuan }} pertemuan)
                        </p>
                    @else
                        <p class="text-2xl font-bold tracking-tight text-gray-400 dark:text-gray-500">—</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Belum ada catatan izin/sakit</p>
                    @endif
                </div>
            </div>

            {{-- KPI 3: Alpa --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200/90 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Alpa</span>
                    @if($totalPertemuan > 0 && $totalAlpa > 0)
                        <span class="inline-flex items-center gap-1 rounded-md bg-error-50 px-2 py-0.5 text-[11px] font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-300">
                            <span class="size-1.5 rounded-full bg-error-500" aria-hidden="true"></span>
                            Perhatian
                        </span>
                    @else
                        <span class="size-2 rounded-full {{ $totalPertemuan > 0 ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-700' }}" aria-hidden="true"></span>
                    @endif
                </div>
                <div class="mt-2.5">
                    @if($totalPertemuan > 0)
                        <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">{{ $totalAlpa }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $totalAlpa > 0 ? "{$totalAlpa} dari {$totalPertemuan} pertemuan tanpa keterangan" : 'Nihil tanpa keterangan' }}
                        </p>
                    @else
                        <p class="text-2xl font-bold tracking-tight text-gray-400 dark:text-gray-500">—</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Belum ada catatan presensi</p>
                    @endif
                </div>
            </div>

            {{-- KPI 4: Rata-Rata Nilai (Tampilkan Belum ada nilai / — jika belum ada nilai valid, bukan 0.00) --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200/90 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Rata-Rata Nilai</span>
                    <span class="size-2 rounded-full {{ $hasValidGrades ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-700' }}" aria-hidden="true"></span>
                </div>
                <div class="mt-2.5">
                    @if($hasValidGrades)
                        <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">{{ number_format($gpa, 2) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Dari {{ $gradedCount }}{{ ($totalMapelCount ?? 0) > 0 ? ' dari ' . $totalMapelCount : '' }} mata pelajaran
                        </p>
                    @else
                        <p class="text-lg sm:text-xl font-semibold tracking-tight text-gray-400 dark:text-gray-500" aria-label="Belum ada nilai">—</p>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">Belum ada nilai</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Jadwal Pelajaran (Dinaikkan tepat setelah Ringkasan Akademik, Tab Hari Ini & Mingguan, Card di Mobile) --}}
    <div x-data="{
            scheduleTab: @js($defaultScheduleTab),
            setTab(tab) {
                this.scheduleTab = tab;
                this.syncPanels();
            },
            syncPanels() {
                if (this.$refs.panelToday) {
                    if (this.scheduleTab === 'today') {
                        this.$refs.panelToday.removeAttribute('hidden');
                    } else {
                        this.$refs.panelToday.setAttribute('hidden', 'until-found');
                    }
                }
                if (this.$refs.panelWeekly) {
                    if (this.scheduleTab === 'weekly') {
                        this.$refs.panelWeekly.removeAttribute('hidden');
                    } else {
                        this.$refs.panelWeekly.setAttribute('hidden', 'until-found');
                    }
                }
            },
            onTabKeydown(e) {
                if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                    e.preventDefault();
                    const next = this.scheduleTab === 'today' ? 'weekly' : 'today';
                    this.setTab(next);
                    this.$nextTick(() => {
                        (next === 'today' ? this.$refs.tabToday : this.$refs.tabWeekly)?.focus();
                    });
                }
            }
        }"
        x-init="syncPanels()"
        @beforematch="if ($event.target === $refs.panelToday) { setTab('today'); } else if ($event.target === $refs.panelWeekly) { setTab('weekly'); }"
        class="mb-6 rounded-xl border border-gray-200/90 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 px-4 sm:px-5 py-3.5 dark:border-gray-800/80">
            <div>
                <h2 id="wali-schedule-caption" class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                    Jadwal Pelajaran
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Kelas {{ $displayKelas->nama_kelas ?? 'Belum Diplot' }} · {{ $shortPeriod }}
                </p>
            </div>

            {{-- Tab Switcher: Hari Ini & Mingguan --}}
            <div role="tablist"
                 aria-label="Tampilan Jadwal Pelajaran"
                 @keydown="onTabKeydown($event)"
                 class="inline-flex items-center rounded-lg bg-gray-100 p-1 dark:bg-gray-800 self-start sm:self-auto">
                <button x-ref="tabToday"
                        type="button"
                        role="tab"
                        id="schedule-tab-today"
                        aria-controls="schedule-panel-today"
                        :aria-selected="(scheduleTab === 'today').toString()"
                        :tabindex="scheduleTab === 'today' ? 0 : -1"
                        @click="setTab('today')"
                        :class="scheduleTab === 'today' ? 'bg-white text-gray-900 shadow-2xs dark:bg-gray-900 dark:text-white' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md px-3 text-xs font-semibold transition cursor-pointer">
                    <span>Hari Ini</span>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold tabular-nums"
                          :class="scheduleTab === 'today' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300' : 'bg-gray-200/80 text-gray-600 dark:bg-gray-700 dark:text-gray-300'">
                        {{ $todaySchedules->count() }}
                    </span>
                </button>

                <button x-ref="tabWeekly"
                        type="button"
                        role="tab"
                        id="schedule-tab-weekly"
                        aria-controls="schedule-panel-weekly"
                        :aria-selected="(scheduleTab === 'weekly').toString()"
                        :tabindex="scheduleTab === 'weekly' ? 0 : -1"
                        @click="setTab('weekly')"
                        :class="scheduleTab === 'weekly' ? 'bg-white text-gray-900 shadow-2xs dark:bg-gray-900 dark:text-white' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md px-3 text-xs font-semibold transition cursor-pointer">
                    <span>Mingguan</span>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold tabular-nums"
                          :class="scheduleTab === 'weekly' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300' : 'bg-gray-200/80 text-gray-600 dark:bg-gray-700 dark:text-gray-300'">
                        {{ $schedules->count() }}
                    </span>
                </button>
            </div>
        </div>

        {{-- Panel 1: Jadwal Hari Ini --}}
        <div x-ref="panelToday"
             id="schedule-panel-today"
             role="tabpanel"
             aria-labelledby="schedule-tab-today"
             @if($defaultScheduleTab !== 'today') hidden="until-found" @endif>
            <div>
                @if($todaySchedules->isNotEmpty())
                    <table class="block md:table w-full border-collapse text-left">
                        <thead class="hidden md:table-header-group border-b border-gray-100 bg-white dark:border-gray-800/80 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Hari</th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Jam</th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Pengajar</th>
                                <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Ruang</th>
                            </tr>
                        </thead>
                        <tbody class="block md:table-row-group p-3 md:p-0 space-y-2.5 md:space-y-0 md:divide-y md:divide-gray-100 dark:md:divide-gray-800/80">
                            @foreach($todaySchedules as $schedule)
                                <tr class="block md:table-row rounded-xl md:rounded-none border md:border-0 border-gray-100 bg-gray-50/50 md:bg-white p-3.5 md:p-0 hover:bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/40 dark:md:bg-gray-900 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="inline-block md:table-cell md:px-5 md:py-3.5 whitespace-nowrap align-middle">
                                        <span class="inline-flex items-center rounded-md bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                                            {{ $schedule->hari }}
                                        </span>
                                    </td>
                                    <td class="inline-block md:table-cell ml-1.5 md:ml-0 md:px-4 md:py-3.5 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 tabular-nums align-middle">
                                        {{ date('H:i', strtotime($schedule->waktu_mulai)) }} – {{ date('H:i', strtotime($schedule->waktu_selesai)) }} WIB
                                    </td>
                                    <td class="block md:table-cell mt-2 md:mt-0 md:px-4 md:py-3.5 text-sm font-semibold text-gray-900 dark:text-gray-100 align-middle">
                                        {{ $schedule->mataPelajaran->nama_mapel ?? '-' }}
                                    </td>
                                    <td class="block md:table-cell mt-1 md:mt-0 md:px-4 md:py-3.5 text-xs sm:text-sm text-gray-600 dark:text-gray-400 align-middle">
                                        <span class="md:hidden text-gray-400">Pengajar: </span>{{ $schedule->pengajar->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="block md:table-cell mt-0.5 md:mt-0 md:px-5 md:py-3.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400 align-middle">
                                        <span class="md:hidden text-gray-400">Ruang: </span>{{ $schedule->ruang ?: 'Kelas Utama' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $schedules->isEmpty() ? 'Belum ada jadwal pelajaran pada periode ini' : "Tidak ada jadwal pelajaran pada hari {$todayName}" }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $schedules->isEmpty() ? 'Jadwal KBM akan tampil otomatis setelah diplot oleh pengurus akademik.' : 'Silakan periksa daftar jadwal lengkap selama satu pekan.' }}
                        </p>
                        @if($schedules->isNotEmpty())
                            <button type="button"
                                    @click="setTab('weekly')"
                                    class="mt-3 inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                                Lihat Jadwal Mingguan ({{ $schedules->count() }} sesi)
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Panel 2: Jadwal Mingguan --}}
        <div x-ref="panelWeekly"
             id="schedule-panel-weekly"
             role="tabpanel"
             aria-labelledby="schedule-tab-weekly"
             @if($defaultScheduleTab !== 'weekly') hidden="until-found" @endif>
            <div>
                @if($schedules->isNotEmpty())
                    <table class="block md:table w-full border-collapse text-left">
                        <thead class="hidden md:table-header-group border-b border-gray-100 bg-white dark:border-gray-800/80 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Hari</th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Jam</th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Pengajar</th>
                                <th scope="col" class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Ruang</th>
                            </tr>
                        </thead>
                        <tbody class="block md:table-row-group p-3 md:p-0 space-y-2.5 md:space-y-0 md:divide-y md:divide-gray-100 dark:md:divide-gray-800/80">
                            @foreach($schedules as $schedule)
                                @php
                                    $isTodayRow = strtolower(trim((string) $schedule->hari)) === strtolower($todayName)
                                        || ($todayName === 'Minggu' && strtolower(trim((string) $schedule->hari)) === 'ahad');
                                @endphp
                                <tr class="block md:table-row rounded-xl md:rounded-none border md:border-0 border-gray-100 bg-gray-50/50 md:bg-white p-3.5 md:p-0 hover:bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/40 dark:md:bg-gray-900 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="inline-block md:table-cell md:px-5 md:py-3.5 whitespace-nowrap align-middle">
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold {{ $isTodayRow ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                            {{ $schedule->hari }}
                                        </span>
                                    </td>
                                    <td class="inline-block md:table-cell ml-1.5 md:ml-0 md:px-4 md:py-3.5 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 tabular-nums align-middle">
                                        {{ date('H:i', strtotime($schedule->waktu_mulai)) }} – {{ date('H:i', strtotime($schedule->waktu_selesai)) }} WIB
                                    </td>
                                    <td class="block md:table-cell mt-2 md:mt-0 md:px-4 md:py-3.5 text-sm font-semibold text-gray-900 dark:text-gray-100 align-middle">
                                        {{ $schedule->mataPelajaran->nama_mapel ?? '-' }}
                                    </td>
                                    <td class="block md:table-cell mt-1 md:mt-0 md:px-4 md:py-3.5 text-xs sm:text-sm text-gray-600 dark:text-gray-400 align-middle">
                                        <span class="md:hidden text-gray-400">Pengajar: </span>{{ $schedule->pengajar->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="block md:table-cell mt-0.5 md:mt-0 md:px-5 md:py-3.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400 align-middle">
                                        <span class="md:hidden text-gray-400">Ruang: </span>{{ $schedule->ruang ?: 'Kelas Utama' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada jadwal pelajaran terdaftar pada periode semester ini</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data jadwal akan muncul setelah pengurus akademik menetapkan jadwal kelas.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Visualisasi Tren & Capaian Akademik (Chart.js — Hanya ditampilkan jika data nilai tersedia) --}}
    @if(count($chartSubjects) > 0 || count($semesterTrend) >= 2)
        <div class="mb-6 grid grid-cols-1 {{ count($semesterTrend) >= 2 ? 'lg:grid-cols-12' : '' }} gap-4">
            @if(count($chartSubjects) > 0)
                <div class="{{ count($semesterTrend) >= 2 ? 'lg:col-span-7' : '' }} rounded-xl border border-gray-200/90 bg-white p-4 sm:p-5 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Capaian Nilai Mata Pelajaran</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Distribusi nilai akhir pada periode {{ $shortPeriod }}</p>
                        </div>
                        <a href="{{ route('wali.grades', $queryParams) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                            Lihat tabel nilai &rarr;
                        </a>
                    </div>
                    <div class="h-60">
                        <canvas id="santriSubjectScoresChart"
                                role="img"
                                aria-label="Grafik batang capaian nilai mata pelajaran {{ $santri->nama_lengkap }} pada periode {{ $shortPeriod }}"></canvas>
                    </div>
                </div>
            @endif

            @if(count($semesterTrend) >= 2)
                <div class="{{ count($chartSubjects) > 0 ? 'lg:col-span-5' : '' }} rounded-xl border border-gray-200/90 bg-white p-4 sm:p-5 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Tren Rata-Rata Antar Semester</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Perkembangan rata-rata nilai akademik lintas periode</p>
                    </div>
                    <div class="h-60">
                        <canvas id="santriSemesterTrendChart"
                                role="img"
                                aria-label="Grafik garis tren rata-rata nilai {{ $santri->nama_lengkap }} antar semester"></canvas>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- 4. Evaluasi Kepesantrenan & Catatan Wali Kelas (Netral & Ringkas) --}}
    @if($dashSikap || $dashCatatan)
        <div class="mb-6 rounded-xl border border-gray-200/90 bg-white p-4 sm:p-5 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Catatan Wali Kelas &amp; Evaluasi Kepesantrenan
                </h2>
                @if($dashSikap)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-500/15 dark:text-primary-300">
                        Predikat Sikap: {{ $dashSikap }}
                    </span>
                @endif
            </div>
            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                "{{ $dashCatatan ?: 'Terus tingkatkan semangat belajar, ibadah berjamaah, dan akhlaqul karimah di pondok.' }}"
            </p>
        </div>
    @endif

    @if(count($chartSubjects) > 0 || count($semesterTrend) >= 2)
        @push('scripts')
            @include('partials.charts-bootstrap')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (!window.NK) return;

                    @if(count($chartSubjects) > 0)
                        const subjectData = @js($chartSubjects);
                        window.NK.mount('santriSubjectScoresChart', ({ defaults, axes, color }) => ({
                            type: 'bar',
                            data: {
                                labels: subjectData.map(d => d.label),
                                datasets: [{
                                    label: 'Nilai Akhir',
                                    data: subjectData.map(d => d.score),
                                    backgroundColor: color.primary,
                                    borderRadius: 6,
                                    maxBarThickness: 36,
                                }],
                            },
                            options: {
                                ...defaults,
                                plugins: {
                                    ...defaults.plugins,
                                    legend: { display: false },
                                    tooltip: {
                                        ...defaults.plugins.tooltip,
                                        callbacks: {
                                            label: (ctx) => {
                                                const item = subjectData[ctx.dataIndex];
                                                return `Nilai Akhir: ${Number(ctx.parsed.y).toFixed(2)} (Predikat ${item?.grade || '-'})`;
                                            },
                                        },
                                    },
                                },
                                scales: {
                                    x: axes.x,
                                    y: { ...axes.y, suggestedMax: 100 },
                                },
                            },
                        }));
                    @endif

                    @if(count($semesterTrend) >= 2)
                        const trendData = @js($semesterTrend);
                        window.NK.mount('santriSemesterTrendChart', ({ defaults, axes, color }) => ({
                            type: 'line',
                            data: {
                                labels: trendData.map(d => d.label),
                                datasets: [{
                                    label: 'Rata-Rata Semester',
                                    data: trendData.map(d => d.avg),
                                    borderColor: color.primary,
                                    backgroundColor: 'rgba(39, 163, 118, 0.12)',
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 4,
                                    pointBackgroundColor: color.primary,
                                }],
                            },
                            options: {
                                ...defaults,
                                plugins: {
                                    ...defaults.plugins,
                                    legend: { display: false },
                                },
                                scales: {
                                    x: axes.x,
                                    y: { ...axes.y, suggestedMin: 50, suggestedMax: 100 },
                                },
                            },
                        }));
                    @endif
                });
            </script>
        @endpush
    @endif
@endif

@endsection
