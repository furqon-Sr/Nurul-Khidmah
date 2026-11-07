@extends('layouts.admin')

@section('title', 'Dashboard Eksekutif')

@section('content')
@php
    $sumGrades = array_sum($gradeDistribution);
    $rapotDone = $expectedTotalEntries > 0 && $gradeCompletionPercentage >= 100;
    $ringPct = max(0, min(100, (float) $gradeCompletionPercentage));
    $ringCircumference = 2 * M_PI * 52;
    $ppdbHasData = $ppdbTotals['pendaftar'] > 0;
    $acceptRate = $ppdbHasData ? round($ppdbTotals['diterima'] / $ppdbTotals['pendaftar'] * 100) : 0;
    $incomplete = collect($incompleteSchedules ?? []);

    $card = 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
@endphp

<x-page-heading title="Dashboard Eksekutif" description="Ringkasan kondisi akademik dan penerimaan santri baru.">
    <x-slot:actions>
        <a href="{{ route('pimpinan.export-pdf') }}" target="_blank"
           class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary-600 px-4 text-sm font-bold text-white transition hover:bg-primary-700">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Unduh ringkasan PDF
        </a>
    </x-slot:actions>
</x-page-heading>

{{-- 1 · Statistik utama (netral, satu aksen) --}}
<section aria-label="Statistik utama" class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <x-stat-card label="Santri aktif" :value="$totalSantriAktif" :hint="$totalSantriLulus.' lulus/alumni · '.$totalSantriMutasi.' mutasi'">
        <x-slot:icon><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Pengajar aktif" :value="$totalPengajar" :hint="$totalKelas.' kelas pada periode ini'">
        <x-slot:icon><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0121 17.5M12 14L5.84 10.578A12.083 12.083 0 003 17.5M12 14v7"/></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="PPDB diterima" :value="$ppdbTotals['diterima']" :suffix="'/ '.$ppdbTotals['pendaftar']" :hint="$ppdbHasData ? $acceptRate.'% dari seluruh pendaftar diterima' : 'Belum ada pendaftar'">
        <x-slot:icon><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Rata-rata kehadiran" :value="$attendancePercentage.'%'" hint="Presensi semester aktif">
        <x-slot:icon><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Rata-rata nilai" :value="$sumGrades > 0 ? number_format($averageGrade, 2) : '—'" :hint="$sumGrades > 0 ? 'Capaian akademik semester aktif' : 'Belum ada nilai yang diinput'">
        <x-slot:icon><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Pendaftar menunggu" :value="$ppdbTotals['pending']" :hint="$ppdbTotals['pending'] > 0 ? 'Perlu ditinjau oleh panitia' : 'Tidak ada antrean'">
        <x-slot:icon><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
    </x-stat-card>
</section>

{{-- 2 · Analitik PPDB: tren (line) + status (doughnut) --}}
<section aria-label="Analitik PPDB" class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="{{ $card }} p-6 lg:col-span-2">
        <div class="mb-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Tren pendaftaran PPDB</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pendaftar dan santri diterima pada setiap tahun ajaran.</p>
        </div>
        @if ($ppdbTrends->isNotEmpty())
            <div class="h-72"><canvas id="ppdbTrendChart" role="img" aria-label="Grafik garis tren pendaftar dan santri diterima per tahun ajaran"></canvas></div>
        @else
            @include('pimpinan.partials.empty-state', ['text' => 'Belum ada riwayat pendaftar PPDB tercatat.'])
        @endif
    </div>

    <div class="{{ $card }} p-6">
        <div class="mb-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Status PPDB</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $ppdbTotals['pendaftar'] }} total pendaftar</p>
        </div>
        @if ($ppdbHasData)
            <div class="h-64"><canvas id="ppdbStatusChart" role="img" aria-label="Grafik donat status pendaftar: {{ $ppdbTotals['diterima'] }} diterima, {{ $ppdbTotals['pending'] }} menunggu, {{ $ppdbTotals['ditolak'] }} ditolak"></canvas></div>
        @else
            @include('pimpinan.partials.empty-state', ['text' => 'Belum ada pendaftar untuk ditampilkan.'])
        @endif
    </div>
</section>

{{-- 3 · Rapor & nilai: progress ring + distribusi nilai --}}
<section aria-label="Rapor dan nilai" class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="{{ $card }} p-6">
        <div class="mb-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Ketuntasan pengisian rapor</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Semester aktif, seluruh kelas.</p>
        </div>

        <div class="flex flex-wrap items-center gap-6">
            <div class="relative size-32 shrink-0" role="img" aria-label="Ketuntasan rapor {{ $gradeCompletionPercentage }} persen">
                <svg viewBox="0 0 128 128" class="size-full -rotate-90">
                    <circle cx="64" cy="64" r="52" fill="none" stroke-width="12" class="stroke-gray-200 dark:stroke-gray-700"/>
                    <circle cx="64" cy="64" r="52" fill="none" stroke-width="12" stroke-linecap="round"
                            class="{{ $rapotDone ? 'stroke-primary-500' : 'stroke-primary-500' }} transition-all duration-700"
                            stroke-dasharray="{{ round($ringCircumference, 2) }}"
                            stroke-dashoffset="{{ round($ringCircumference * (1 - $ringPct / 100), 2) }}"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-extrabold leading-none text-gray-900 dark:text-white">{{ $gradeCompletionPercentage }}%</span>
                </div>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $filledTotalEntries }} <span class="font-medium text-gray-600 dark:text-gray-400">dari {{ $expectedTotalEntries }} entri</span></p>
                <div class="mt-2">
                    @if ($rapotDone)
                        <span class="chip chip-success">Siap cetak rapor</span>
                    @else
                        <span class="chip chip-warning">Dalam pengisian</span>
                    @endif
                </div>
            </div>
        </div>

        @if ($incomplete->isNotEmpty())
            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800">
                <p class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Belum lengkap <span class="font-medium text-gray-600 dark:text-gray-400">({{ $incomplete->count() }} jadwal)</span></p>
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($incomplete->take(5) as $inc)
                        <li class="flex items-center justify-between gap-4 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $inc['mapel'] }}</p>
                                <p class="truncate text-xs text-gray-700 dark:text-gray-400">{{ $inc['kelas'] }} · {{ $inc['pengajar'] }}</p>
                            </div>
                            <span class="shrink-0 text-sm font-bold text-gray-900 dark:text-gray-100">{{ $inc['filled'] }}/{{ $inc['expected'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="{{ $card }} p-6 lg:col-span-2">
        <div class="mb-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Distribusi nilai</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Jumlah nilai per predikat pada semester aktif.</p>
        </div>
        @if ($sumGrades > 0)
            <div class="h-64"><canvas id="gradeChart" role="img" aria-label="Grafik batang jumlah nilai per predikat A sampai E"></canvas></div>
            <p class="mt-4 text-xs text-gray-700 dark:text-gray-400">A ≥ 85 · B 75–84 · C 65–74 · D 50–64 · E &lt; 50</p>
        @else
            @include('pimpinan.partials.empty-state', ['text' => 'Belum ada nilai yang tersedia pada semester ini.'])
        @endif
    </div>
</section>

{{-- 4 · Rekap per kelas --}}
<section aria-labelledby="executive-class-table-heading" class="{{ $card }} overflow-hidden">
    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
        <h2 id="executive-class-table-heading" class="text-base font-bold text-gray-900 dark:text-white">Rekap per kelas</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Wali kelas, jumlah santri, kehadiran, nilai rata-rata, dan progres pengisian rapor.</p>
    </div>

    <div tabindex="0" role="region" aria-labelledby="executive-class-table-heading" class="max-h-[70vh] overflow-x-auto p-2">
        <table class="table-builder min-w-full border-separate border-spacing-0">
            <thead class="sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left">Kelas</th>
                    <th scope="col" class="px-4 py-3 text-left">Wali kelas</th>
                    <th scope="col" class="px-4 py-3 text-right">Santri</th>
                    <th scope="col" class="px-4 py-3 text-right">Kehadiran</th>
                    <th scope="col" class="px-4 py-3 text-right">Rata-rata nilai</th>
                    <th scope="col" class="min-w-56 px-4 py-3 text-left">Ketuntasan rapor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($classSummaries as $c)
                    <tr>
                        <th scope="row" class="whitespace-nowrap px-4 py-4 text-left text-sm font-bold text-gray-900 dark:text-white">
                            {{ $c['nama_kelas'] }}
                            <span class="block text-xs font-medium text-gray-700 dark:text-gray-400">{{ $c['jumlah_mapel'] }} mata pelajaran</span>
                        </th>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $c['wali_kelas'] }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $c['jumlah_santri'] }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $c['attendance_pct'] }}%</td>
                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $c['avg_grade'] > 0 ? number_format($c['avg_grade'], 2) : '—' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="progress-track flex-1"><div class="progress-fill bg-primary-500" style="width: {{ min($c['completion_pct'], 100) }}%"></div></div>
                                <span class="w-12 text-right text-sm font-bold text-gray-900 dark:text-gray-100">{{ $c['completion_pct'] }}%</span>
                                <span class="w-12 text-xs font-medium text-gray-700 dark:text-gray-400">{{ $c['filled_grades'] }}/{{ $c['expected_grades'] }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-700 dark:text-gray-400">Belum ada data rombongan belajar terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
    @include('partials.charts-bootstrap')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trend = @json($ppdbTrends->map(fn ($r) => ['periode' => $r->periode, 'pendaftar' => (int) $r->total_pendaftar, 'diterima' => (int) $r->diterima])->values());
            const ppdb = @json($ppdbTotals);
            const grades = @json($gradeDistribution);

            NK.mount('ppdbTrendChart', ({ defaults, axes, color }) => ({
                type: 'line',
                data: {
                    labels: trend.map((r) => r.periode),
                    datasets: [
                        { label: 'Pendaftar', data: trend.map((r) => r.pendaftar), borderColor: color.neutral, backgroundColor: color.neutral, pointBackgroundColor: color.neutral, borderWidth: 2, tension: 0.35, pointRadius: 4 },
                        { label: 'Diterima', data: trend.map((r) => r.diterima), borderColor: color.primary, backgroundColor: color.primary, pointBackgroundColor: color.primary, borderWidth: 2.5, tension: 0.35, pointRadius: 4 },
                    ],
                },
                options: {
                    ...defaults,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { ...defaults.plugins, tooltip: { ...defaults.plugins.tooltip, displayColors: true, boxWidth: 8, boxHeight: 8, usePointStyle: true } },
                    scales: axes,
                },
            }));

            NK.mount('ppdbStatusChart', ({ defaults, color }) => ({
                type: 'doughnut',
                data: {
                    labels: ['Diterima', 'Menunggu', 'Ditolak'],
                    datasets: [{ data: [ppdb.diterima, ppdb.pending, ppdb.ditolak], backgroundColor: [color.primary, color.warning, color.error], borderWidth: 0, spacing: 2, borderRadius: 4 }],
                },
                options: { ...defaults, cutout: '75%' },
                plugins: [NK.centerText(String(ppdb.pendaftar), 'pendaftar')],
            }));

            NK.mount('gradeChart', ({ defaults, axes, color }) => {
                const labels = ['A', 'B', 'C', 'D', 'E'];
                const axis = axes;
                return {
                    type: 'bar',
                    data: {
                        labels: labels.map((g) => 'Predikat ' + g),
                        datasets: [{ label: 'Jumlah nilai', data: labels.map((g) => grades[g] ?? 0), backgroundColor: labels.map((g) => (g === 'D' || g === 'E') ? color.error : color.primary), borderRadius: 6, borderSkipped: false, maxBarThickness: 24 }],
                    },
                    options: {
                        ...defaults,
                        indexAxis: 'y',
                        plugins: { ...defaults.plugins, legend: { display: false } },
                        scales: { x: { ...axis.y, beginAtZero: true }, y: { ...axis.x } },
                    },
                };
            });
        });
    </script>
@endpush
