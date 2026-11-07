@extends('layouts.admin')

@section('title', 'Pengolahan Nilai')

@section('content')
@php
    $panel = 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
    $control = 'h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';

    $statusMeta = [
        'not_started' => ['label' => 'Belum dimulai', 'chip' => 'chip-neutral', 'bar' => 'bg-gray-300', 'cta' => 'Mulai penilaian'],
        'in_progress' => ['label' => 'Dalam proses', 'chip' => 'chip-warning', 'bar' => 'bg-warning-400', 'cta' => 'Lanjutkan penilaian'],
        'done' => ['label' => 'Selesai', 'chip' => 'chip-success', 'bar' => 'bg-primary-500', 'cta' => 'Kelola nilai'],
        'no_students' => ['label' => 'Tidak ada santri', 'chip' => 'chip-muted', 'bar' => 'bg-gray-300', 'cta' => null],
    ];

    $classes = $items->pluck('kelas')->unique()->sort()->values();
    $overallPercent = $summary['expected_entries'] > 0 ? (int) round($summary['complete_entries'] / $summary['expected_entries'] * 100) : null;
    $todo = $items->where('pending', '>', 0)->sortByDesc('pending')->values();
    $noStudents = $items->where('status', 'no_students')->values();

    $filterConfig = ['items' => $items->map(fn ($i) => Arr::only($i, ['id', 'mapel', 'kelas', 'status']))->all()];
@endphp

<x-page-heading title="Pengolahan Nilai" description="Kelola penilaian dan evaluasi akademik santri.">
    <x-slot:actions>
        <form method="GET" action="{{ route('teacher.grades.index') }}">
            <label for="periode" class="sr-only">Periode akademik</label>
            <select id="periode" name="tahun_ajaran_id" onchange="this.form.submit()" class="{{ $control }}">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected($selectedYearId === $year->id)>{{ $year->nama_tahun_ajaran }} · {{ $year->semester }}{{ $activeYear?->id === $year->id ? ' (aktif)' : '' }}</option>
                @endforeach
                <option value="semua" @selected($selectedYearId === null)>Semua periode</option>
            </select>
        </form>
    </x-slot:actions>
</x-page-heading>

@if(session('success'))
    <div role="status" class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 text-sm font-medium text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">{{ session('success') }}</div>
@endif

@if($items->isEmpty())
    <div class="{{ $panel }} px-6 py-16 text-center">
        <span class="mx-auto inline-flex size-12 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400" aria-hidden="true">
            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </span>
        <p class="mt-4 text-sm font-bold text-gray-900 dark:text-white">Belum ada jadwal mengajar pada periode ini</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pilih periode lain, atau hubungi tata usaha bila jadwal belum diplotkan.</p>
    </div>
@else
    {{-- 1 · Ringkasan progres --}}
    <section aria-label="Ringkasan penilaian" class="{{ $panel }} mb-8">
        <dl class="grid grid-cols-2 divide-gray-200 dark:divide-gray-800 lg:grid-cols-4 lg:divide-x">
            <div class="p-6">
                <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Mata pelajaran</dt>
                <dd class="mt-2 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">{{ $summary['schedules'] }}</dd>
                <p class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $summary['schedules'] === 1 ? 'penugasan' : 'penugasan kelas' }} diampu</p>
            </div>
            <div class="p-6">
                <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Santri perlu dinilai</dt>
                <dd class="mt-2 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">{{ $summary['pending_students'] }}</dd>
                <p class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $summary['pending_students'] > 0 ? 'entri nilai belum terisi' : 'semua sudah terisi' }}</p>
            </div>
            <div class="p-6">
                <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Entri nilai terisi</dt>
                <dd class="mt-2 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">
                    @if($overallPercent !== null){{ $summary['complete_entries'] }}<span class="ml-1 text-sm font-semibold text-gray-600 dark:text-gray-400">/ {{ $summary['expected_entries'] }}</span>@else—@endif
                </dd>
                @if($overallPercent !== null)
                    <div class="progress-track mt-3 h-1.5"><div class="progress-fill bg-primary-500" style="width: {{ $overallPercent }}%"></div></div>
                @else
                    <p class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300">Belum ada santri terdaftar</p>
                @endif
            </div>
            <div class="p-6">
                <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Perlu tindak lanjut</dt>
                <dd class="mt-2 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">{{ $summary['needs_action'] }}</dd>
                <p class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300">
                    {{ $summary['needs_action'] > 0 ? 'mapel belum selesai dinilai' : 'tidak ada yang tertunda' }}@if($summary['without_students'] > 0) · {{ $summary['without_students'] }} tanpa santri @endif
                </p>
            </div>
        </dl>
    </section>

    {{-- 2 · Filter + daftar mata pelajaran --}}
    <div x-data="gradeList({{ \Illuminate\Support\Js::from($filterConfig) }})">
        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row">
                <div class="relative sm:max-w-xs sm:flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <label for="q" class="sr-only">Cari mata pelajaran atau kelas</label>
                    <input id="q" type="search" x-model.debounce.150ms="q" autocomplete="off" placeholder="Cari mapel atau kelas" class="{{ $control }} w-full pl-9">
                </div>
                <label for="kelas" class="sr-only">Filter kelas</label>
                <select id="kelas" x-model="kelas" class="{{ $control }}">
                    <option value="">Semua kelas</option>
                    @foreach($classes as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
                </select>
                <label for="status" class="sr-only">Filter status penilaian</label>
                <select id="status" x-model="status" class="{{ $control }}">
                    <option value="">Semua status</option>
                    @foreach($statusMeta as $key => $meta)<option value="{{ $key }}">{{ $meta['label'] }}</option>@endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300" aria-live="polite"><span class="font-bold text-gray-900 dark:text-white" x-text="visible.length"></span> dari {{ $items->count() }} mapel</p>
                <button type="button" x-show="q || kelas || status" x-cloak @click="q = ''; kelas = ''; status = ''" class="text-sm font-bold text-primary-700 hover:underline dark:text-primary-300">Reset</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($items as $item)
                @php($meta = $statusMeta[$item['status']])
                <article x-show="visible.includes({{ $item['id'] }})" class="{{ $panel }} flex flex-col p-5 transition hover:border-gray-300 dark:hover:border-gray-700">
                    <div class="flex items-start justify-between gap-3">
                        <span class="chip chip-neutral normal-case tracking-normal">{{ $item['kelas'] }}</span>
                        <span class="chip {{ $meta['chip'] }}">{{ $meta['label'] }}</span>
                    </div>

                    <h3 class="mt-3 text-base font-bold leading-snug text-gray-900 dark:text-white">{{ $item['mapel'] }}</h3>
                    <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $item['expected'] > 0 ? $item['expected'].' santri' : 'Tidak ada santri' }}@if($item['jadwal']) · {{ $item['jadwal'] }}@endif
                        @if($showTeacherName)<span class="block text-xs text-gray-600 dark:text-gray-400">{{ $item['pengajar'] }}</span>@endif
                    </p>

                    <div class="mt-4 flex-1">
                        @if($item['status'] === 'no_students')
                            <p class="rounded-lg bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-800/60 dark:text-gray-300">Tidak ada santri aktif yang perlu dinilai pada kelas ini.</p>
                        @else
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $item['complete'] }} dari {{ $item['expected'] }} santri selesai dinilai</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $item['percent'] }}%</span>
                            </div>
                            <div class="progress-track mt-2 h-1.5"><div class="progress-fill {{ $meta['bar'] }}" style="width: {{ $item['percent'] }}%"></div></div>
                        @endif
                    </div>

                    <div class="mt-5">
                        @if($meta['cta'])
                            <a href="{{ $item['url'] }}"
                               class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-bold transition {{ $item['status'] === 'done' ? 'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800' : 'bg-primary-600 text-white hover:bg-primary-700' }}">
                                {{ $meta['cta'] }}
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @elseif($canViewClasses)
                            <a href="{{ route('admin.classes.index') }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">Lihat kelas</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div x-show="!visible.length" x-cloak class="{{ $panel }} px-6 py-12 text-center">
            <p class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada mapel yang cocok</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ubah kata kunci atau filter yang dipakai.</p>
        </div>
    </div>

    {{-- 3 · Perlu ditindaklanjuti --}}
    <section aria-labelledby="todo-title" class="{{ $panel }} mt-8">
        <div class="border-b border-gray-200 p-6 dark:border-gray-800">
            <h2 id="todo-title" class="text-base font-bold text-gray-900 dark:text-white">Perlu ditindaklanjuti</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Penilaian yang belum selesai, diurutkan dari yang paling banyak tertunda.</p>
        </div>

        @if($todo->isEmpty() && $noStudents->isEmpty())
            <div class="flex items-center gap-3 p-6 text-sm font-medium text-gray-800 dark:text-gray-200">
                <span class="inline-flex size-8 items-center justify-center rounded-full bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300" aria-hidden="true"><svg class="size-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                Semua penilaian pada periode ini sudah selesai.
            </div>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($todo as $row)
                    <li class="flex flex-col gap-3 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $row['mapel'] }}</p>
                            <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $row['kelas'] }} · {{ $row['pending'] }} santri belum selesai dinilai</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="progress-track w-24"><div class="progress-fill {{ $statusMeta[$row['status']]['bar'] }}" style="width: {{ $row['percent'] }}%"></div></div>
                                <span class="w-10 text-right text-sm font-bold text-gray-900 dark:text-white">{{ $row['percent'] }}%</span>
                            </div>
                            <a href="{{ $row['url'] }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm font-bold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                                {{ $statusMeta[$row['status']]['cta'] }}
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </li>
                @endforeach

                @if($noStudents->isNotEmpty())
                    <li class="p-6">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $noStudents->count() }} penugasan belum memiliki santri aktif</p>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            {{ $noStudents->map(fn ($r) => $r['mapel'].' ('.$r['kelas'].')')->implode(', ') }}.
                            Periksa data kelas atau jadwal bila ini tidak sesuai. Penugasan ini tidak dihitung sebagai pekerjaan tertunda.
                        </p>
                    </li>
                @endif
            </ul>
        @endif
    </section>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gradeList', (config) => ({
            items: config.items,
            q: '',
            kelas: '',
            status: '',
            get visible() {
                const q = this.q.trim().toLowerCase();
                return this.items
                    .filter((i) => (!q || i.mapel.toLowerCase().includes(q) || i.kelas.toLowerCase().includes(q))
                        && (!this.kelas || i.kelas === this.kelas)
                        && (!this.status || i.status === this.status))
                    .map((i) => i.id);
            },
        }));
    });
</script>
@endpush
