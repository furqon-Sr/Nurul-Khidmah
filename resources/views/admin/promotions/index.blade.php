@extends('layouts.admin')

@section('title', 'Kenaikan & Kelulusan')

@section('content')
@php
    $panel = 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
    $field = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $label = 'mb-2 block text-sm font-semibold text-gray-900 dark:text-gray-100';

    $promotionConfig = [
        'rows' => $santriRows,
        'canOverride' => $canOverride,
        'kelasAsal' => $asalKelas?->nama_kelas,
        'old' => [
            'selected' => array_map('intval', (array) old('santri_ids', [])),
            'jenis' => old('jenis_aksi', 'Naik Kelas'),
            'tujuan' => old('tujuan_kelas_id', ''),
            'override' => (bool) old('override', false),
            'reason' => old('override_reason', ''),
        ],
        'kelasTujuan' => $allClasses->where('id', '!=', $asalKelas?->id)->mapWithKeys(fn ($k) => [
            (string) $k->id => $k->nama_kelas.' · TA '.($k->tahunAjaran->nama_tahun_ajaran ?? '-'),
        ])->all(),
    ];
@endphp

<x-page-heading title="Kenaikan & Kelulusan Santri" description="Kelola kenaikan kelas dan kelulusan santri secara kolektif.">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.promotions.index') }}" class="flex items-center gap-2">
            <label for="tahun_ajaran_id" class="sr-only">Tahun ajaran sumber</label>
            <select id="tahun_ajaran_id" name="tahun_ajaran_id" onchange="this.form.submit()" class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected($yearId === $year->id)>TA {{ $year->nama_tahun_ajaran }} · {{ $year->semester }}{{ $activeYear?->id === $year->id ? ' (aktif)' : '' }}</option>
                @endforeach
                @if($yearId === null)
                    <option value="" selected>Semua tahun ajaran</option>
                @endif
            </select>
        </form>

        <div class="relative" x-data="{ help: false }" @click.outside="help = false" @keydown.escape.window="help = false">
            <button type="button" @click="help = !help" :aria-expanded="help" aria-label="Informasi bantuan"
                    class="inline-flex size-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </button>
            <div x-show="help" x-cloak x-transition.opacity role="dialog" aria-label="Informasi kenaikan dan kelulusan"
                 class="absolute right-0 z-30 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-lg dark:border-gray-700 dark:bg-gray-900">
                <p class="font-bold text-gray-900 dark:text-white">Yang tetap terjaga</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-gray-700 dark:text-gray-300">
                    <li>NIS dan akun Portal Wali tidak berubah.</li>
                    <li>Arsip rapor semester sebelumnya tetap dapat diunduh wali.</li>
                    <li>Setiap proses tercatat pada riwayat dan log aktivitas.</li>
                </ul>
                <p class="mt-3 font-bold text-gray-900 dark:text-white">Syarat nilai</p>
                <p class="mt-1 text-gray-700 dark:text-gray-300">Kenaikan dan kelulusan memerlukan nilai rapor. Santri tanpa nilai hanya dapat diproses lewat pengecualian resmi oleh Super Admin dengan alasan tercatat.</p>
            </div>
        </div>
    </x-slot:actions>
</x-page-heading>

@if(session('success'))
    <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 p-4 text-sm font-medium text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">
        <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
        <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        <ul class="space-y-1 font-medium">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($asalKelas)
<div x-data="promotion({{ \Illuminate\Support\Js::from($promotionConfig) }})" class="space-y-6">

    {{-- Stepper --}}
    <nav aria-label="Langkah kenaikan dan kelulusan">
        <ol class="flex items-center gap-2 sm:gap-4">
            <template x-for="(step, i) in steps" :key="step.n">
                <li class="flex items-center gap-2 sm:gap-4" :class="i < steps.length - 1 ? 'flex-1' : ''" :aria-current="step.n === currentStep ? 'step' : null">
                    <span class="flex items-center gap-3">
                        <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold transition"
                              :class="step.done ? 'border-primary-500 bg-primary-500 text-white' : (step.n === currentStep ? 'border-primary-500 bg-white text-primary-600 dark:bg-gray-900' : 'border-gray-300 bg-white text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400')">
                            <svg x-show="step.done" class="size-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="!step.done" x-text="step.n"></span>
                        </span>
                        <span class="text-sm font-semibold" :class="step.n === currentStep || step.done ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" x-text="step.label"></span>
                    </span>
                    <span x-show="i < steps.length - 1" class="hidden h-0.5 flex-1 rounded sm:block" :class="step.done ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700'"></span>
                </li>
            </template>
        </ol>
    </nav>

    {{-- Langkah 1 · Pilih kelas --}}
    <section class="{{ $panel }} p-6" aria-labelledby="step1-title">
        <div class="mb-6 flex items-start gap-3">
            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">1</span>
            <div>
                <h2 id="step1-title" class="text-base font-bold text-gray-900 dark:text-white">Pilih kelas asal</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Daftar santri dimuat otomatis setelah kelas dipilih.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.promotions.index') }}" class="max-w-xl">
            @if($yearId)<input type="hidden" name="tahun_ajaran_id" value="{{ $yearId }}">@endif
            <label for="asal_kelas_id" class="{{ $label }}">Kelas asal</label>
            <select id="asal_kelas_id" name="asal_kelas_id" @change="loading = true; $el.form.submit()" class="{{ $field }}">
                @forelse($sourceClasses as $c)
                    <option value="{{ $c->id }}" @selected($asalKelas->id === $c->id)>
                        {{ $c->nama_kelas }} · {{ $c->active_santris_count }} santri aktif
                    </option>
                @empty
                    <option value="">Belum ada data kelas</option>
                @endforelse
            </select>
        </form>
    </section>

    <form method="POST" action="{{ route('admin.promotions.store') }}" class="space-y-6" @submit="confirmOpen = false">
        @csrf
        <input type="hidden" name="asal_kelas_id" value="{{ $asalKelas->id }}">
        <input type="hidden" name="jenis_aksi" :value="jenis">
        <template x-for="id in selected" :key="id"><input type="hidden" name="santri_ids[]" :value="id"></template>
        <input type="hidden" name="override" value="1" x-bind:disabled="!overrideActive">

        {{-- Langkah 2 · Keputusan --}}
        <section class="{{ $panel }} p-6" aria-labelledby="step2-title">
            <div class="mb-6 flex items-start gap-3">
                <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">2</span>
                <div>
                    <h2 id="step2-title" class="text-base font-bold text-gray-900 dark:text-white">Tentukan keputusan</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Berlaku untuk seluruh santri yang dipilih pada langkah berikutnya.</p>
                </div>
            </div>

            <fieldset>
                <legend class="sr-only">Jenis keputusan</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach([
                        ['Naik Kelas', 'Naik kelas', 'Pindah ke kelas tingkat berikutnya.'],
                        ['Tinggal Kelas', 'Tinggal kelas', 'Mengulang tingkat yang sama.'],
                        ['Pindah Kelas', 'Pindah kelas', 'Pindah ke rombel paralel.'],
                        ['Lulus', 'Lulus', 'Tingkat akhir, menjadi alumni.'],
                    ] as [$value, $title, $desc])
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition"
                               :class="jenis === '{{ $value }}' ? 'border-primary-500 bg-primary-50 ring-1 ring-primary-500 dark:bg-primary-500/10' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'">
                            <input type="radio" value="{{ $value }}" x-model="jenis" class="mt-1 size-4 border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white">{{ $title }}</span>
                                <span class="mt-1 block text-xs text-gray-700 dark:text-gray-300">{{ $desc }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div x-show="jenis !== 'Lulus'">
                    <label for="tujuan_kelas_id" class="{{ $label }}">Kelas tujuan</label>
                    <select id="tujuan_kelas_id" name="tujuan_kelas_id" x-model="tujuan" :disabled="jenis === 'Lulus'" class="{{ $field }}">
                        <option value="">Pilih kelas tujuan</option>
                        @foreach($allClasses as $target)
                            @if($target->id !== $asalKelas->id)
                                <option value="{{ $target->id }}">{{ $target->nama_kelas }} · Tingkat {{ $target->tingkat_kelas }} · TA {{ $target->tahunAjaran->nama_tahun_ajaran ?? '-' }} {{ $target->tahunAjaran->semester ?? '' }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div x-show="jenis === 'Lulus'" x-cloak class="flex items-end">
                    <p class="text-sm text-gray-700 dark:text-gray-300">Status santri menjadi <strong class="text-gray-900 dark:text-white">Lulus</strong>. Akun wali dan NIS tetap aktif untuk mengunduh arsip rapor.</p>
                </div>
                <div>
                    <label for="catatan" class="{{ $label }}">Catatan SK <span class="font-medium text-gray-600 dark:text-gray-400">(opsional)</span></label>
                    <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}" maxlength="255" placeholder="Contoh: Rapat pleno kenaikan kelas genap" class="{{ $field }}">
                </div>
            </div>
        </section>

        {{-- Langkah 3 · Pilih santri --}}
        <section class="{{ $panel }} overflow-hidden" aria-labelledby="step3-title">
            <div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-3">
                    <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">3</span>
                    <div>
                        <h2 id="step3-title" class="text-base font-bold text-gray-900 dark:text-white">Pilih santri</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $asalKelas->nama_kelas }} · <span x-text="rows.length"></span> santri aktif ·
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="selected.length + ' dipilih'"></span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                        <label for="search" class="sr-only">Cari santri</label>
                        <input type="search" id="search" x-model.debounce.150ms="search" autocomplete="off" autocorrect="off" spellcheck="false" placeholder="Cari nama atau NIS"
                               class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 sm:w-64">
                    </div>
                    <button type="button" @click="selectReady()" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        Pilih yang siap
                    </button>
                    <button type="button" x-show="selected.length" x-cloak @click="selected = []" class="inline-flex h-10 items-center justify-center rounded-lg px-3 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                        Kosongkan
                    </button>
                </div>
            </div>

            {{-- Skeleton saat daftar dimuat --}}
            <div x-show="loading" x-cloak class="space-y-3 border-t border-gray-200 p-6 dark:border-gray-800" aria-hidden="true">
                @for($i = 0; $i < 5; $i++)
                    <div class="flex animate-pulse items-center gap-4">
                        <div class="size-5 rounded bg-gray-200 dark:bg-gray-700"></div>
                        <div class="h-4 w-48 rounded bg-gray-200 dark:bg-gray-700"></div>
                        <div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
                        <div class="ml-auto h-6 w-28 rounded-md bg-gray-200 dark:bg-gray-700"></div>
                    </div>
                @endfor
            </div>

            <div x-show="!loading" class="border-t border-gray-200 dark:border-gray-800">
                <div class="max-h-[60vh] overflow-auto p-2">
                    <table class="table-builder min-w-full border-separate border-spacing-0">
                        <thead class="sticky top-0 z-10">
                            <tr>
                                <th scope="col" class="w-14 px-0 py-0">
                                    <label class="flex cursor-pointer items-center justify-center p-4">
                                        <input type="checkbox" class="size-5 cursor-pointer rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                               :checked="allVisibleSelected" :indeterminate.prop="someVisibleSelected && !allVisibleSelected"
                                               @change="toggleAllVisible($event.target.checked)" aria-label="Pilih semua santri yang tampil">
                                    </label>
                                </th>
                                <th scope="col" class="px-4 py-3 text-left">Santri</th>
                                <th scope="col" class="px-4 py-3 text-left">Penilaian</th>
                                <th scope="col" class="px-4 py-3 text-left">Rata-rata</th>
                                <th scope="col" class="px-4 py-3 text-left">Kesiapan akademik</th>
                                <th scope="col" class="w-16 px-4 py-3 text-right"><span class="sr-only">Detail</span></th>
                            </tr>
                        </thead>

                        <template x-for="r in filtered" :key="r.id">
                            <tbody class="border-b border-gray-200 dark:border-gray-800" x-data="{ open: false }">
                                <tr :class="selected.includes(r.id) ? 'bg-primary-50/60 dark:bg-primary-500/5' : ''">
                                    <td class="w-14 p-0">
                                        <label class="flex items-center justify-center p-4" :class="rowDisabled(r) ? 'cursor-not-allowed' : 'cursor-pointer'">
                                            <input type="checkbox" :value="r.id" x-model.number="selected" :disabled="rowDisabled(r)"
                                                   class="size-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 disabled:cursor-not-allowed disabled:opacity-40"
                                                   :aria-label="'Pilih ' + r.name">
                                        </label>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="r.name"></p>
                                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400" x-text="'NIS ' + r.nis"></p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="r.expected ? (r.count + '/' + r.expected + ' mapel') : (r.count + ' mapel')"></p>
                                        <div class="progress-track mt-1.5 w-24"><div class="progress-fill" :class="r.status === 'siap' ? 'bg-primary-500' : (r.count ? 'bg-warning-400' : 'bg-gray-300')" :style="'width:' + progressPct(r) + '%'"></div></div>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        <template x-if="r.avg !== null"><span x-text="Number(r.avg).toFixed(2) + ' (' + r.grade + ')'"></span></template>
                                        <template x-if="r.avg === null"><span class="font-medium text-gray-600 dark:text-gray-400">—</span></template>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="chip" :class="{ 'chip-success': r.status === 'siap', 'chip-warning': r.status === 'review', 'chip-error': r.status === 'incomplete' }"
                                              x-text="r.status === 'siap' ? 'Siap' : (r.status === 'review' ? 'Perlu ditinjau' : 'Data belum lengkap')"></span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" @click="open = !open" :aria-expanded="open" :aria-label="'Detail ' + r.name"
                                                class="inline-flex size-8 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                                            <svg class="size-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr x-show="open" x-cloak>
                                    <td></td>
                                    <td colspan="5" class="px-4 pb-4 pt-0">
                                        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                                            <div><dt class="text-xs font-semibold text-gray-700 dark:text-gray-400">Akun portal wali</dt><dd class="mt-0.5 break-all font-medium text-gray-900 dark:text-gray-100" x-text="r.waliEmail ?? 'Belum ditautkan'"></dd></div>
                                            <div><dt class="text-xs font-semibold text-gray-700 dark:text-gray-400">Sikap</dt><dd class="mt-0.5 font-medium text-gray-900 dark:text-gray-100" x-text="r.sikap ?? '—'"></dd></div>
                                            <div><dt class="text-xs font-semibold text-gray-700 dark:text-gray-400">Hasil validasi</dt>
                                                <dd class="mt-0.5 font-medium text-gray-900 dark:text-gray-100">
                                                    <template x-if="!r.issues.length"><span>Seluruh syarat nilai terpenuhi.</span></template>
                                                    <template x-for="issue in r.issues" :key="issue"><span class="block" x-text="issue"></span></template>
                                                </dd>
                                            </div>
                                        </dl>
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                    </table>

                    <div x-show="!rows.length" class="px-6 py-12 text-center text-sm text-gray-700 dark:text-gray-300">
                        Tidak ada santri aktif pada kelas ini. Buat kelas tahun ajaran baru di menu <strong>Data Kelas</strong> bila ingin mempromosikan santri.
                    </div>
                    <div x-show="rows.length && !filtered.length" x-cloak class="px-6 py-12 text-center text-sm text-gray-700 dark:text-gray-300">
                        Tidak ada santri yang cocok dengan “<span x-text="search"></span>”.
                    </div>
                </div>
            </div>
        </section>

        {{-- Langkah 4 · Konfirmasi --}}
        <section class="{{ $panel }} p-6" aria-labelledby="step4-title">
            <div class="mb-6 flex items-start gap-3">
                <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">4</span>
                <div>
                    <h2 id="step4-title" class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Periksa ringkasan dan hasil validasi sebelum memproses.</p>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div><dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Keputusan</dt><dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white" x-text="jenis"></dd></div>
                <div><dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Kelas tujuan</dt><dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white" x-text="jenis === 'Lulus' ? 'Alumni' : (kelasTujuan[tujuan] ?? '—')"></dd></div>
                <div><dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Santri dipilih</dt><dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white" x-text="selected.length + ' dari ' + rows.length"></dd></div>
                <div><dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Hasil validasi</dt>
                    <dd class="mt-1 flex flex-wrap gap-1.5">
                        <span class="chip chip-success" x-text="count('siap') + ' siap'"></span>
                        <span class="chip chip-warning" x-show="count('review')" x-text="count('review') + ' ditinjau'"></span>
                        <span class="chip chip-error" x-show="count('incomplete')" x-text="count('incomplete') + ' belum lengkap'"></span>
                    </dd>
                </div>
            </dl>

            {{-- Pengecualian resmi --}}
            <div x-show="gated && blockedInClass > 0" x-cloak class="mt-6 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm font-bold text-gray-900 dark:text-white">
                    <span x-text="blockedInClass"></span> santri belum memiliki nilai dan tidak dapat diproses.
                </p>
                @if($canOverride)
                    <label class="mt-3 flex cursor-pointer items-start gap-3 text-sm">
                        <input type="checkbox" x-model="override" class="mt-0.5 size-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="font-medium text-gray-900 dark:text-gray-100">Ajukan pengecualian resmi. Tindakan ini tercatat atas nama Anda.</span>
                    </label>
                    <div x-show="override" x-cloak class="mt-3">
                        <label for="override_reason" class="{{ $label }}">Alasan pengecualian</label>
                        <textarea id="override_reason" name="override_reason" x-model="reason" rows="2" maxlength="255" placeholder="Minimal 10 karakter, mis. SK pimpinan nomor …"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                        <p class="mt-1 text-xs text-gray-700 dark:text-gray-400"><span x-text="reason.length"></span>/255 · wajib minimal 10 karakter.</p>
                    </div>
                @else
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">Lengkapi nilai rapor terlebih dahulu. Pengecualian hanya dapat diberikan oleh Super Admin dengan alasan tercatat.</p>
                @endif
            </div>

            <div x-show="count('review') > 0" x-cloak class="mt-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                <span class="font-bold text-warning-700 dark:text-warning-300" x-text="count('review')"></span> santri terpilih perlu ditinjau (nilai belum penuh atau rata-rata di bawah ambang). Proses tetap dapat dilanjutkan.
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700 dark:text-gray-300" x-text="readyHint"></p>
                <button type="button" @click="confirmOpen = true" :disabled="!ready"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-primary-600 px-6 text-sm font-bold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-500 dark:disabled:bg-gray-800 dark:disabled:text-gray-500">
                    Tinjau &amp; konfirmasi
                </button>
            </div>
        </section>

        {{-- Dialog konfirmasi --}}
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="confirm-title"
             @keydown.escape.window="confirmOpen = false">
            <div class="absolute inset-0 bg-gray-900/60" @click="confirmOpen = false" x-transition.opacity></div>
            <div class="relative w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900" x-trap.noscroll="confirmOpen" x-transition>
                <h2 id="confirm-title" class="text-lg font-bold text-gray-900 dark:text-white">Proses keputusan ini?</h2>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                    <strong class="text-gray-900 dark:text-white" x-text="selected.length + ' santri'"></strong> dari {{ $asalKelas->nama_kelas }} akan diproses sebagai
                    <strong class="text-gray-900 dark:text-white" x-text="jenis"></strong><span x-show="jenis !== 'Lulus'"> ke <strong class="text-gray-900 dark:text-white" x-text="kelasTujuan[tujuan]"></strong></span>.
                    Tindakan ini langsung mengubah data kelas dan status santri.
                </p>
                <p x-show="overrideActive" x-cloak class="mt-3 rounded-lg bg-gray-100 p-3 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                    Termasuk pengecualian resmi: <span x-text="reason"></span>
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="confirmOpen = false" class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Batal</button>
                    <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-primary-600 px-4 text-sm font-bold text-white hover:bg-primary-700">Ya, proses sekarang</button>
                </div>
            </div>
        </div>
    </form>
</div>
@else
    <div class="{{ $panel }} p-12 text-center text-sm text-gray-700 dark:text-gray-300">
        Belum ada data kelas. Buat kelas terlebih dahulu di menu <strong>Data Kelas</strong>.
    </div>
@endif

{{-- Riwayat --}}
<section class="{{ $panel }} mt-8 overflow-hidden" aria-labelledby="history-promo-heading">
    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
        <h2 id="history-promo-heading" class="text-base font-bold text-gray-900 dark:text-white">Riwayat terbaru</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">15 proses kenaikan dan kelulusan terakhir.</p>
    </div>
    <div tabindex="0" role="region" aria-labelledby="history-promo-heading" class="overflow-x-auto p-2">
        <table class="table-builder min-w-full border-separate border-spacing-0">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-3 text-left">Waktu</th>
                    <th scope="col" class="px-4 py-3 text-left">Santri</th>
                    <th scope="col" class="px-4 py-3 text-left">Keputusan</th>
                    <th scope="col" class="px-4 py-3 text-left">Perpindahan kelas</th>
                    <th scope="col" class="px-4 py-3 text-left">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($recentLogs as $log)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $log->santri->nama_lengkap ?? '-' }}</p>
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-400">NIS {{ $log->santri->nis ?? '-' }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="chip {{ $log->jenis_aksi === 'Lulus' ? 'chip-info' : ($log->jenis_aksi === 'Arsip Kelas' ? 'chip-neutral' : 'chip-success') }}">{{ $log->jenis_aksi }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                            {{ $log->kelasLama->nama_kelas ?? 'Awal' }} → <strong class="text-gray-900 dark:text-white">{{ $log->kelasBaru->nama_kelas ?? 'Alumni' }}</strong>
                        </td>
                        <td class="max-w-xs px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $log->catatan ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-700 dark:text-gray-300">Belum ada riwayat kenaikan kelas atau kelulusan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('promotion', (config) => ({
            rows: config.rows,
            canOverride: config.canOverride,
            kelasTujuan: config.kelasTujuan,
            selected: config.old.selected.filter((id) => config.rows.some((r) => r.id === id)),
            jenis: config.old.jenis,
            tujuan: String(config.old.tujuan ?? ''),
            override: config.old.override && config.canOverride,
            reason: config.old.reason || '',
            search: '',
            loading: false,
            confirmOpen: false,

            init() {
                // Keputusan / override berubah → bersihkan pilihan yang tidak lagi diizinkan.
                this.$watch('jenis', () => this.prune());
                this.$watch('override', () => this.prune());
            },

            get gated() { return ['Naik Kelas', 'Lulus'].includes(this.jenis); },
            get blockedInClass() { return this.gated ? this.rows.filter((r) => r.status === 'incomplete').length : 0; },
            get overrideActive() { return this.canOverride && this.override && this.gated && this.selected.some((id) => this.byId(id)?.status === 'incomplete'); },

            byId(id) { return this.rows.find((r) => r.id === id); },
            isBlocked(r) { return this.gated && r.status === 'incomplete'; },
            rowDisabled(r) { return this.isBlocked(r) && !(this.canOverride && this.override); },
            progressPct(r) { return r.expected ? Math.min(100, Math.round(r.count / r.expected * 100)) : (r.count ? 100 : 0); },

            get filtered() {
                const q = this.search.trim().toLowerCase();
                return q ? this.rows.filter((r) => r.name.toLowerCase().includes(q) || String(r.nis).toLowerCase().includes(q)) : this.rows;
            },
            get selectableVisible() { return this.filtered.filter((r) => !this.rowDisabled(r)); },
            get allVisibleSelected() { return this.selectableVisible.length > 0 && this.selectableVisible.every((r) => this.selected.includes(r.id)); },
            get someVisibleSelected() { return this.selectableVisible.some((r) => this.selected.includes(r.id)); },

            toggleAllVisible(checked) {
                const ids = this.selectableVisible.map((r) => r.id);
                this.selected = checked ? [...new Set([...this.selected, ...ids])] : this.selected.filter((id) => !ids.includes(id));
            },
            selectReady() {
                const ids = this.filtered.filter((r) => r.status === 'siap').map((r) => r.id);
                this.selected = [...new Set([...this.selected, ...ids])];
            },
            prune() {
                this.selected = this.selected.filter((id) => { const r = this.byId(id); return r && !this.rowDisabled(r); });
            },
            count(status) { return this.selected.filter((id) => this.byId(id)?.status === status).length; },

            get decisionReady() { return this.jenis === 'Lulus' || this.tujuan !== ''; },
            get reasonOk() { return !this.overrideActive || this.reason.trim().length >= 10; },
            get ready() { return this.decisionReady && this.selected.length > 0 && this.reasonOk; },
            get readyHint() {
                if (!this.decisionReady) return 'Pilih kelas tujuan pada langkah 2.';
                if (!this.selected.length) return 'Pilih minimal satu santri pada langkah 3.';
                if (!this.reasonOk) return 'Isi alasan pengecualian (minimal 10 karakter).';
                return 'Siap diproses. Anda akan diminta konfirmasi terakhir.';
            },

            get currentStep() { return this.steps.find((s) => !s.done)?.n ?? 4; },
            get steps() {
                return [
                    { n: 1, label: 'Pilih kelas', done: true },
                    { n: 2, label: 'Tentukan keputusan', done: this.decisionReady },
                    { n: 3, label: 'Pilih santri', done: this.decisionReady && this.selected.length > 0 },
                    { n: 4, label: 'Konfirmasi', done: false },
                ];
            },
        }));
    });
</script>
@endpush
