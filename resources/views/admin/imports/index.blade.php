@extends('layouts.admin')

@section('title', 'Import Data')

@section('content')
@php
    $panel = 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
    $select = 'h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $hasFilter = $filters['status'] !== '' || $filters['type'] !== '';

    $pageConfig = [
        'type' => $activeType,
        'maxMb' => $maxFileMb,
        'actions' => [
            'santri' => route('admin.imports.santri'),
            'pengajar' => route('admin.imports.pengajar'),
        ],
        'headers' => collect($schemas)->map(fn ($s) => implode(',', array_keys($s['columns'])))->all(),
        'service' => $service,
        'statusUrl' => route('admin.imports.status'),
        'ids' => $batches->pluck('id')->all(),
        'hasActive' => $batches->contains(fn ($b) => $b->isActive()),
    ];
@endphp

<div x-data="importPage({{ \Illuminate\Support\Js::from($pageConfig) }})">

<x-page-heading title="Import Data" description="Unggah dan validasi data santri atau pengajar secara massal.">
    <x-slot:actions>
        {{-- Status layanan import (berdasarkan pemeriksaan worker nyata) --}}
        <div class="relative" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false" @focusin="tip = true" @focusout="tip = false">
            <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" :aria-describedby="'service-tip'">
                <span class="size-2.5 rounded-full"
                      :class="{ 'bg-success-500': service.state === 'active', 'bg-error-500': service.state === 'unavailable', 'bg-gray-400 animate-pulse': service.state === 'unknown' }"></span>
                <span x-text="service.label"></span>
            </button>
            <div id="service-tip" role="tooltip" x-show="tip" x-cloak x-transition.opacity
                 class="absolute right-0 z-30 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-lg dark:border-gray-700 dark:bg-gray-900">
                <p class="font-medium text-gray-800 dark:text-gray-200" x-text="service.hint"></p>
                <p class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-400" x-show="service.last_seen">Terakhir merespons <span x-text="service.last_seen"></span></p>
                <dl class="mt-3 space-y-1 border-t border-gray-200 pt-3 text-xs dark:border-gray-700">
                    <template x-for="(value, key) in service.detail" :key="key">
                        <div class="flex justify-between gap-4"><dt class="font-semibold text-gray-700 dark:text-gray-400" x-text="key"></dt><dd class="font-medium text-gray-900 dark:text-gray-100" x-text="value"></dd></div>
                    </template>
                </dl>
            </div>
        </div>
    </x-slot:actions>
</x-page-heading>

@if(session('success'))
    <div role="status" class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-success-200 bg-success-50 p-4 text-sm font-medium text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- Komponen import tunggal: tab tipe data → dropzone → validasi --}}
<section class="{{ $panel }} mb-8" aria-labelledby="import-title">
    <div class="flex flex-col gap-4 border-b border-gray-200 p-6 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
        <div class="tabs border-b-0" role="tablist" aria-label="Tipe data">
            @foreach($schemas as $key => $schema)
                <button type="button" role="tab" :aria-selected="type === '{{ $key }}'" @click="setType('{{ $key }}')" class="tab" :class="type === '{{ $key }}' ? 'tab-active' : ''">{{ $schema['label'] }}</button>
            @endforeach
        </div>

        <ol class="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300" aria-label="Tahapan import">
            @foreach(['Unggah', 'Validasi', 'Pratinjau', 'Import'] as $i => $step)
                <li class="flex items-center gap-2">
                    <span class="inline-flex size-6 items-center justify-center rounded-full text-xs font-bold {{ $i === 0 ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">{{ $i + 1 }}</span>
                    <span class="{{ $i === 0 ? 'text-gray-900 dark:text-white' : '' }}">{{ $step }}</span>
                    @if($i < 3)<span class="hidden h-px w-4 bg-gray-300 dark:bg-gray-700 sm:block"></span>@endif
                </li>
            @endforeach
        </ol>
    </div>

    <form method="POST" :action="actions[type]" enctype="multipart/form-data" class="p-6" @submit="submitting = true">
        @csrf
        <h2 id="import-title" class="text-base font-bold text-gray-900 dark:text-white">
            Unggah berkas <span x-text="type === 'santri' ? 'santri' : 'pengajar'"></span>
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Berkas divalidasi terlebih dahulu. Belum ada data yang disimpan sampai Anda mengonfirmasi pratinjau.</p>

        @error('file')
            <div role="alert" class="mt-4 rounded-lg border border-error-200 bg-error-50 p-3 text-sm font-medium text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">{{ $message }}</div>
        @enderror
        <div x-show="clientError" x-cloak role="alert" class="mt-4 rounded-lg border border-error-200 bg-error-50 p-3 text-sm font-medium text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200" x-text="clientError"></div>

        {{-- Dropzone --}}
        <div class="mt-6">
            <input x-ref="input" type="file" name="file" id="file" class="sr-only" accept=".xlsx,.xls,.csv" @change="pick($event.target.files[0])">

            <label for="file" x-show="!file" @dragover.prevent="drag = true" @dragleave.prevent="drag = false" @drop.prevent="drag = false; drop($event)"
                   class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 text-center transition focus-within:ring-2 focus-within:ring-primary-500/40"
                   :class="drag ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-gray-300 hover:border-primary-400 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50'">
                <span class="inline-flex size-12 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-300">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </span>
                <span class="mt-4 text-sm font-bold text-gray-900 dark:text-white">Tarik berkas ke sini atau <span class="text-primary-600 underline dark:text-primary-300">pilih dari komputer</span></span>
                <span class="mt-1 text-sm text-gray-600 dark:text-gray-400">Format .xlsx, .xls, .csv · maksimal {{ rtrim(rtrim(number_format($maxFileMb, 1), '0'), '.') }} MB · hingga {{ number_format($maxRows) }} baris</span>
            </label>

            <div x-show="file" x-cloak class="flex items-center gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-300">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white" x-text="file?.name"></p>
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-400" x-text="file ? formatSize(file.size) : ''"></p>
                </div>
                <button type="button" @click="$refs.input.click()" class="inline-flex h-9 items-center rounded-lg border border-gray-300 px-3 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">Ganti</button>
                <button type="button" @click="clear()" aria-label="Hapus berkas" class="inline-flex size-9 items-center justify-center rounded-lg text-gray-600 hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <a :href="templateUrls[type]" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-800 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh template
                </a>
            </div>
            <button type="submit" :disabled="!file || submitting"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-primary-600 px-5 text-sm font-bold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-500 dark:disabled:bg-gray-800">
                <span x-text="submitting ? 'Memvalidasi…' : 'Unggah & validasi'"></span>
            </button>
        </div>
    </form>

    {{-- Format kolom (accordion) --}}
    @foreach($schemas as $key => $schema)
        <div x-show="type === '{{ $key }}'" x-cloak x-data="{ open: false, copied: false }" class="border-t border-gray-200 dark:border-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                <button type="button" @click="open = !open" :aria-expanded="open" class="inline-flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white">
                    <svg class="size-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    Lihat format kolom
                </button>
                <p class="text-sm text-gray-600 dark:text-gray-400">Gunakan template agar format data sesuai.</p>
            </div>
            <div x-show="open" x-cloak x-collapse class="px-6 pb-6">
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                <th scope="col" class="px-4 py-3">Kolom</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Contoh isi</th>
                                <th scope="col" class="px-4 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($schema['columns'] as $col => $def)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-bold text-gray-900 dark:text-gray-100">{{ $col }}</td>
                                    <td class="px-4 py-3"><span class="chip {{ $def['required'] ? 'chip-error' : 'chip-neutral' }}">{{ $def['required'] ? 'Wajib' : 'Opsional' }}</span></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-800 dark:text-gray-200">{{ $def['example'] }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $def['note'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" @click="navigator.clipboard.writeText(headers['{{ $key }}']); copied = true; setTimeout(() => copied = false, 1800)"
                        class="mt-4 inline-flex h-9 items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    <span x-text="copied ? 'Header tersalin' : 'Salin header CSV'"></span>
                </button>
            </div>
        </div>
    @endforeach
</section>

{{-- Riwayat batch --}}
<section class="{{ $panel }} overflow-hidden" aria-labelledby="history-title">
    <div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 id="history-title" class="text-base font-bold text-gray-900 dark:text-white">Riwayat import</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                <template x-if="hasActive"><span>Ada batch aktif. Diperbarui otomatis<span x-show="updatedAt"> · <span x-text="updatedAt"></span></span>.</span></template>
                <template x-if="!hasActive"><span>Semua batch tercatat di sini.</span></template>
            </p>
        </div>
        <form method="GET" action="{{ route('admin.imports.index') }}" class="flex flex-wrap items-center gap-2">
            <label for="f-type" class="sr-only">Tipe data</label>
            <select id="f-type" name="type" onchange="this.form.submit()" class="{{ $select }}">
                <option value="">Semua tipe</option>
                <option value="santri" @selected($filters['type'] === 'santri')>Santri</option>
                <option value="pengajar" @selected($filters['type'] === 'pengajar')>Pengajar</option>
            </select>
            <label for="f-status" class="sr-only">Status</label>
            <select id="f-status" name="status" onchange="this.form.submit()" class="{{ $select }}">
                <option value="">Semua status</option>
                @foreach(['validated' => 'Menunggu konfirmasi', 'pending' => 'Menunggu', 'processing' => 'Memproses', 'completed' => 'Selesai', 'completed_with_errors' => 'Selesai dengan kesalahan', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if($hasFilter)
                <a href="{{ route('admin.imports.index') }}" class="inline-flex h-10 items-center rounded-lg px-3 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Reset</a>
            @endif
        </form>
    </div>

    <div tabindex="0" role="region" aria-labelledby="history-title" class="overflow-x-auto px-2 pb-2">
        <table class="table-builder min-w-full border-separate border-spacing-0">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-3 text-left">Berkas</th>
                    <th scope="col" class="px-4 py-3 text-left">Status</th>
                    <th scope="col" class="min-w-44 px-4 py-3 text-left">Progres</th>
                    <th scope="col" class="px-4 py-3 text-left">Hasil</th>
                    <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($batches as $batch)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="max-w-56 truncate text-sm font-bold text-gray-900 dark:text-white" title="{{ $batch->file_name }}">{{ $batch->file_name }}</p>
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-400">{{ $batch->type_label }} · {{ $batch->created_at->format('d M Y H:i') }}@if($batch->creator) · {{ $batch->creator->name }}@endif</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="chip" :class="live[{{ $batch->id }}]?.chip ?? '{{ $batch->status_chip_class }}'" x-text="live[{{ $batch->id }}]?.status_label ?? @js($batch->status_label)">{{ $batch->status_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($batch->isAwaitingConfirmation() || $batch->status === 'cancelled')
                                <span class="text-sm text-gray-600 dark:text-gray-400">—</span>
                            @else
                                <div class="flex items-center gap-3">
                                    <div class="progress-track flex-1"><div class="progress-fill {{ $batch->status === 'failed' ? 'bg-error-500' : 'bg-primary-500' }}" :style="'width:' + (live[{{ $batch->id }}]?.percent ?? {{ $batch->progress_percent }}) + '%'"></div></div>
                                    <span class="whitespace-nowrap text-xs font-bold text-gray-900 dark:text-gray-100"><span x-text="live[{{ $batch->id }}]?.processed ?? {{ $batch->processed_rows }}">{{ $batch->processed_rows }}</span>/<span x-text="live[{{ $batch->id }}]?.total ?? {{ $batch->total_rows }}">{{ $batch->total_rows }}</span></span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                @if($batch->isAwaitingConfirmation())
                                    <span class="chip chip-success">{{ $batch->valid_rows + $batch->duplicate_rows }} layak</span>
                                @else
                                    <span class="chip chip-success">{{ $batch->processed_rows }} berhasil</span>
                                @endif
                                @if($batch->invalid_rows > 0)<span class="chip chip-error">{{ $batch->invalid_rows }} gagal</span>@endif
                                @if($batch->skipped_rows > 0)<span class="chip chip-neutral">{{ $batch->skipped_rows }} dilewati</span>@endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            @if($batch->invalid_rows > 0)
                                <a href="{{ route('admin.imports.errors', $batch) }}" title="Unduh laporan kesalahan" aria-label="Unduh laporan kesalahan" class="icon-btn mr-1.5 bg-warning-500 text-gray-900">
                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                            @endif
                            <x-icon-button type="view" :href="route('admin.imports.show', $batch)" label="Lihat detail" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-700 dark:text-gray-300">
                            {{ $hasFilter ? 'Tidak ada batch yang cocok dengan filter.' : 'Belum ada riwayat import. Unggah berkas pertama Anda di atas.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">{{ $batches->links() }}</div>
    @endif
</section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('importPage', (config) => ({
            type: config.type,
            actions: config.actions,
            headers: config.headers,
            templateUrls: { santri: @js(route('admin.imports.template', 'santri')), pengajar: @js(route('admin.imports.template', 'pengajar')) },
            service: config.service,
            hasActive: config.hasActive,
            live: {},
            updatedAt: '',
            file: null,
            drag: false,
            submitting: false,
            clientError: '',
            timer: null,

            init() { this.schedule(); },

            setType(t) { this.type = t; },

            // ── Dropzone ──
            drop(e) { this.pick(e.dataTransfer.files[0], true); },
            pick(f, fromDrop = false) {
                this.clientError = '';
                if (!f) return;
                const ext = f.name.split('.').pop().toLowerCase();
                if (!['xlsx', 'xls', 'csv'].includes(ext)) { this.reject('Format berkas harus .xlsx, .xls, atau .csv.'); return; }
                if (f.size > config.maxMb * 1024 * 1024) { this.reject('Ukuran berkas maksimal ' + config.maxMb + ' MB.'); return; }
                if (fromDrop) { const dt = new DataTransfer(); dt.items.add(f); this.$refs.input.files = dt.files; }
                this.file = f;
            },
            reject(msg) { this.clientError = msg; this.file = null; this.$refs.input.value = ''; },
            clear() { this.file = null; this.clientError = ''; this.$refs.input.value = ''; },
            formatSize(b) { return b < 1048576 ? (b / 1024).toFixed(1) + ' KB' : (b / 1048576).toFixed(2) + ' MB'; },

            // ── Auto-refresh: cepat saat ada batch aktif, santai saat idle ──
            schedule() {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.refresh(), this.hasActive || this.service.state === 'unknown' ? 4000 : 20000);
            },
            async refresh() {
                if (document.hidden) { this.schedule(); return; }
                try {
                    const qs = new URLSearchParams(); config.ids.forEach((id) => qs.append('ids[]', id));
                    const res = await fetch(config.statusUrl + '?' + qs, { headers: { Accept: 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        this.service = data.service;
                        data.batches.forEach((b) => { this.live[b.id] = b; });
                        this.hasActive = data.batches.some((b) => b.active);
                        this.updatedAt = data.updated_at;
                    }
                } catch (e) { /* abaikan; coba lagi pada siklus berikutnya */ }
                this.schedule();
            },
        }));
    });
</script>
@endpush
