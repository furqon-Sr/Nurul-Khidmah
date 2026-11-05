@extends('layouts.admin')

@section('title', 'Migrasi & Import Data Massal')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Migrasi &amp; Import Data Massal</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">
                Unggah data santri dan tenaga pengajar via berkas Excel/CSV. Proses pemindahan dijalankan secara asynchronous melalui background queue worker (chunked 500 baris).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Queue Driver: {{ config('queue.default', 'database') }}
            </span>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 dark:text-emerald-400 text-xs font-semibold hover:opacity-75">✕ Tutup</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm shadow-sm">
            <div class="font-semibold mb-1">Terjadi kesalahan pada input berkas:</div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 1: Import Santri -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-700 dark:text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Import Data Santri</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Format: Excel (.xlsx, .xls) atau CSV</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.imports.template', 'santri') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template CSV
                    </a>
                </div>

                <div class="mb-4 bg-gray-50 dark:bg-gray-900/60 p-3.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <div class="font-semibold text-gray-700 dark:text-gray-300">Format Kolom Header yang Didukung:</div>
                    <code class="block font-mono text-[11px] bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-700 text-emerald-700 dark:text-emerald-400 break-all">
                        nama_lengkap, nis, jenis_kelamin, unit, kelas, tempat_lahir, tanggal_lahir, alamat, nama_wali, no_hp_wali, tahun_masuk
                    </code>
                    <p class="text-[11px] text-gray-500 italic mt-1">
                        *Jika kolom <code>nis</code> dikosongkan, sistem akan otomatis men-generate NIS unik berdasarkan tahun dan unit (contoh: 26MTS001).
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.imports.santri') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Pilih Berkas Santri:</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required
                           class="block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 dark:file:bg-emerald-950 file:text-emerald-700 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-white dark:bg-gray-900">
                </div>
                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg transition text-sm font-semibold shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Unggah &amp; Proses Santri (Queued)
                </button>
            </form>
        </div>

        <!-- Card 2: Import Pengajar -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-700 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Import Data Pengajar</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Format: Excel (.xlsx, .xls) atau CSV</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.imports.template', 'pengajar') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template CSV
                    </a>
                </div>

                <div class="mb-4 bg-gray-50 dark:bg-gray-900/60 p-3.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <div class="font-semibold text-gray-700 dark:text-gray-300">Format Kolom Header yang Didukung:</div>
                    <code class="block font-mono text-[11px] bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-700 text-blue-700 dark:text-blue-400 break-all">
                        nama_lengkap, nomor_identitas, jenis_kelamin, alamat, nomor_telepon, status_pengajar
                    </code>
                    <p class="text-[11px] text-gray-500 italic mt-1">
                        *Jika <code>nomor_identitas</code> kosong, sistem akan otomatis membuat NIY baru (contoh: NIY-26-001).
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.imports.pengajar') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Pilih Berkas Pengajar:</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required
                           class="block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-950 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-900 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-white dark:bg-gray-900">
                </div>
                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition text-sm font-semibold shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Unggah &amp; Proses Pengajar (Queued)
                </button>
            </form>
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 flex flex-wrap justify-between items-center gap-2">
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm">Riwayat Batch Migrasi &amp; Import</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Daftar batch proses latar belakang dan status eksekusinya</p>
            </div>
            <button type="button" onclick="window.location.reload()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Segarkan Status
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Waktu &amp; ID</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nama Berkas</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tipe Data</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Progres / Baris</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Diunggah Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                <div>#{{ $batch->id }}</div>
                                <div class="text-[11px] text-gray-400">{{ $batch->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $batch->file_name }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                @if($batch->type === 'santri')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        Santri
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                        Pengajar
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center text-xs font-semibold">
                                <span class="text-gray-800 dark:text-gray-200 font-mono">{{ $batch->processed_rows }}</span>
                                <span class="text-gray-400 font-mono">/ {{ $batch->total_rows ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border shadow-sm {{ $batch->status_badge_class }}">
                                    @if($batch->status === 'processing')
                                        <svg class="animate-spin -ml-0.5 mr-1 h-3 w-3 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    @endif
                                    {{ $batch->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                {{ $batch->creator->name ?? 'Sistem' }}
                            </td>
                        </tr>
                        @if(!empty($batch->errors))
                            <tr class="bg-rose-50/50 dark:bg-rose-950/20">
                                <td colspan="6" class="px-5 py-2 text-xs text-rose-700 dark:text-rose-400">
                                    <div class="font-semibold">Catatan Kesalahan:</div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                                        @foreach((array) $batch->errors as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">
                                Belum ada riwayat batch migrasi data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
