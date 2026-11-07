@extends('layouts.admin')

@section('title', 'Detail Import')

@section('content')
@php
    $panel = 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
    $awaiting = $batch->isAwaitingConfirmation();
    $summary = $batch->report['summary'] ?? ['total' => $batch->total_rows, 'valid' => $batch->valid_rows, 'exists' => $batch->duplicate_rows, 'invalid' => $batch->invalid_rows, 'warnings' => 0];
    $unknownColumns = $batch->report['unknown_columns'] ?? [];

    // Tahap aktif: 3 = pratinjau (menunggu konfirmasi); 4 = import berjalan / selesai
    $stepNow = $awaiting ? 3 : 4;
    $stepsDone = $awaiting ? 2 : ($batch->isActive() ? 3 : 4);
    $steps = ['Unggah', 'Validasi', 'Pratinjau', 'Import'];

    $filters = ['all' => 'Semua', 'invalid' => 'Kesalahan', 'warning' => 'Peringatan', 'exists' => 'Sudah ada'];
@endphp

<x-page-heading :title="$awaiting ? 'Pratinjau import' : 'Detail import'" :description="$batch->file_name.' · Data '.$batch->type_label">
    <x-slot:actions>
        <a href="{{ route('admin.imports.index') }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </x-slot:actions>
</x-page-heading>

@if(session('success'))
    <div role="status" class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 text-sm font-medium text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div role="alert" class="mb-6 rounded-xl border border-error-200 bg-error-50 p-4 text-sm font-medium text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
        @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
    </div>
@endif

{{-- Tahapan --}}
<nav aria-label="Tahapan import" class="mb-8">
    <ol class="flex items-center gap-2 sm:gap-4">
        @foreach($steps as $i => $label)
            @php($n = $i + 1)
            @php($done = $n <= $stepsDone)
            <li class="flex items-center gap-2 sm:gap-4 {{ $n < 4 ? 'flex-1' : '' }}" @if($n === $stepNow) aria-current="step" @endif>
                <span class="flex items-center gap-3">
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold {{ $done ? 'border-primary-500 bg-primary-500 text-white' : ($n === $stepNow ? 'border-primary-500 bg-white text-primary-600 dark:bg-gray-900' : 'border-gray-300 bg-white text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400') }}">
                        @if($done)<svg class="size-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@else{{ $n }}@endif
                    </span>
                    <span class="text-sm font-semibold {{ $done || $n === $stepNow ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400' }}">{{ $label }}</span>
                </span>
                @if($n < 4)<span class="hidden h-0.5 flex-1 rounded sm:block {{ $done ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700' }}"></span>@endif
            </li>
        @endforeach
    </ol>
</nav>

{{-- Ringkasan --}}
<section aria-label="Ringkasan validasi" class="mb-8 grid grid-cols-2 gap-6 lg:grid-cols-4">
    <x-stat-card label="Total baris" :value="number_format($summary['total'])" hint="Baris data pada berkas" />
    <x-stat-card label="Siap diimpor" :value="number_format($summary['valid'])" hint="Data baru, lolos validasi" />
    <x-stat-card label="Sudah terdaftar" :value="number_format($summary['exists'])" hint="NIS / nomor identitas sudah ada" />
    <x-stat-card label="Bermasalah" :value="number_format($summary['invalid'])" :hint="$summary['invalid'] ? 'Tidak akan diimpor' : 'Tidak ada kesalahan'" />
</section>

@if($unknownColumns)
    <p class="mb-6 text-sm text-gray-700 dark:text-gray-300">Kolom berikut tidak dikenali dan diabaikan: <span class="font-mono text-xs font-bold">{{ implode(', ', $unknownColumns) }}</span></p>
@endif

@if($awaiting)
    {{-- Konfirmasi import --}}
    <form method="POST" action="{{ route('admin.imports.confirm', $batch) }}" class="{{ $panel }} mb-8 p-6"
          x-data="{ mode: '{{ old('mode', 'skip') }}', valid: {{ $summary['valid'] }}, exists: {{ $summary['exists'] }}, get count() { return this.mode === 'update' ? this.valid + this.exists : this.valid; } }">
        @csrf
        <h2 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi import</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Baris bermasalah otomatis dilewati dan dicatat pada laporan kesalahan.</p>

        @if($summary['exists'] > 0)
            <fieldset class="mt-6">
                <legend class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $summary['exists'] }} data sudah terdaftar. Bagaimana menanganinya?</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach(['skip' => ['Lewati data yang sudah ada', 'Data lama tetap seperti semula. Disarankan.'], 'update' => ['Perbarui data yang sudah ada', 'Data lama ditimpa dengan isi berkas.']] as $value => [$title, $desc])
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition" :class="mode === '{{ $value }}' ? 'border-primary-500 bg-primary-50 ring-1 ring-primary-500 dark:bg-primary-500/10' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700'">
                            <input type="radio" name="mode" value="{{ $value }}" x-model="mode" class="mt-1 size-4 border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span><span class="block text-sm font-bold text-gray-900 dark:text-white">{{ $title }}</span><span class="mt-1 block text-xs text-gray-700 dark:text-gray-300">{{ $desc }}</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @else
            <input type="hidden" name="mode" value="skip">
        @endif

        <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white" x-text="count"></strong> baris akan diimpor ke antrean.</p>
            <div class="flex flex-wrap items-center gap-2">
                @if($summary['invalid'] > 0)
                    <a href="{{ route('admin.imports.errors', $batch) }}" class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">Unduh laporan kesalahan</a>
                @endif
                <button type="submit" form="cancel-form" class="inline-flex h-10 items-center rounded-lg px-4 text-sm font-bold text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Batalkan</button>
                <button type="submit" :disabled="count === 0" class="inline-flex h-10 items-center rounded-lg bg-primary-600 px-5 text-sm font-bold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-500 dark:disabled:bg-gray-800">
                    Import <span class="ml-1" x-text="count"></span> baris
                </button>
            </div>
        </div>
    </form>
    <form id="cancel-form" method="POST" action="{{ route('admin.imports.cancel', $batch) }}" onsubmit="return confirm('Batalkan import ini? Berkas akan dihapus dan tidak ada data yang diubah.')">@csrf</form>
@else
    {{-- Hasil pemrosesan --}}
    <section class="{{ $panel }} mb-8 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Hasil pemrosesan</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $batch->created_at->format('d M Y H:i') }}@if($batch->creator) · oleh {{ $batch->creator->name }}@endif · diperbarui {{ $batch->updated_at->diffForHumans() }}</p>
            </div>
            <span class="chip {{ $batch->status_chip_class }}">{{ $batch->status_label }}</span>
        </div>
        <div class="mt-6 flex items-center gap-3">
            <div class="progress-track flex-1"><div class="progress-fill {{ $batch->status === 'failed' ? 'bg-error-500' : 'bg-primary-500' }}" style="width: {{ $batch->progress_percent }}%"></div></div>
            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $batch->processed_rows }}/{{ $batch->total_rows }}</span>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <span class="chip chip-success">{{ $batch->processed_rows }} berhasil</span>
            @if($batch->invalid_rows)<span class="chip chip-error">{{ $batch->invalid_rows }} gagal validasi</span>@endif
            @if($batch->skipped_rows)<span class="chip chip-neutral">{{ $batch->skipped_rows }} dilewati (sudah ada)</span>@endif
        </div>
        @if(!empty($batch->errors))
            <div class="mt-6 rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
                <p class="font-bold">Pesan kesalahan sistem</p>
                @foreach($batch->errors as $msg)<p class="mt-1 break-words">{{ $msg }}</p>@endforeach
            </div>
        @endif
        @if($summary['invalid'] > 0 || $summary['exists'] > 0)
            <a href="{{ route('admin.imports.errors', $batch) }}" class="mt-6 inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">Unduh laporan kesalahan</a>
        @endif
    </section>
@endif

{{-- Temuan validasi --}}
<section class="{{ $panel }} overflow-hidden" aria-labelledby="issues-title">
    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 id="issues-title" class="text-base font-bold text-gray-900 dark:text-white">Temuan validasi</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Menampilkan {{ min($issueTotal, 200) }} dari {{ $issueTotal }} temuan{{ $issueTotal > 200 ? '. Unduh laporan untuk daftar lengkap.' : '.' }}</p>
        </div>
        <div class="tabs border-b-0 gap-x-4">
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.imports.show', ['batch' => $batch, 'filter' => $key]) }}" class="tab {{ $filter === $key ? 'tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="overflow-x-auto px-2 pb-2">
        <table class="table-builder min-w-full border-separate border-spacing-0">
            <thead>
                <tr>
                    <th scope="col" class="w-20 px-4 py-3 text-left">Baris</th>
                    <th scope="col" class="px-4 py-3 text-left">Data</th>
                    <th scope="col" class="px-4 py-3 text-left">Status</th>
                    <th scope="col" class="px-4 py-3 text-left">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($issues as $issue)
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">{{ $issue['n'] }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $issue['name'] ?: '—' }}</p>
                            @if($issue['key'])<p class="text-xs font-medium text-gray-700 dark:text-gray-400">{{ $issue['key'] }}</p>@endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @if($issue['status'] === 'invalid')<span class="chip chip-error">Bermasalah</span>
                            @elseif($issue['status'] === 'exists')<span class="chip chip-info">Sudah ada</span>
                            @else<span class="chip chip-warning">Peringatan</span>@endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">
                            @foreach($issue['errors'] as $msg)<p class="font-medium text-error-700 dark:text-error-300">{{ $msg }}</p>@endforeach
                            @foreach($issue['warnings'] as $msg)<p>{{ $msg }}</p>@endforeach
                            @if($issue['status'] === 'exists' && !$issue['errors'])<p>Data sudah terdaftar.</p>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-sm text-gray-700 dark:text-gray-300">
                        {{ $filter === 'all' ? 'Tidak ada temuan. Seluruh baris lolos validasi.' : 'Tidak ada temuan pada kategori ini.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
