@extends('layouts.admin')

@section('title', 'Biaya Pendidikan')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Biaya Pendidikan</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                Kelola informasi biaya pendidikan sepenuhnya melalui dokumen berkas (<strong>Foto / JPG / PNG / WEBP</strong>, <strong>PDF</strong>, atau <strong>CSV</strong>) untuk ditampilkan di halaman frontend.
            </p>
        </div>
        <a href="{{ route('education') }}" target="_blank" rel="noopener"
           class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 transition shadow-xs shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Lihat Halaman Pendidikan
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-200">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Upload File Biaya Pendidikan (Foto / JPG / PDF / CSV) --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-5">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Unggah Dokumen Biaya Pendidikan</h2>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                Format yang didukung: <strong>Foto/Gambar</strong> (JPG, JPEG, PNG, WEBP), <strong>PDF</strong> (tampil sebagai penampil dokumen), atau <strong>CSV</strong> (otomatis ditampilkan menjadi tabel rapi). Maksimal 5 MB.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.fees.upload-document') }}" enctype="multipart/form-data"
              class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            @csrf

            <div class="md:col-span-4">
                <label for="target_program" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Unit / Target Program
                </label>
                <select id="target_program" name="target_program" required
                        class="h-11 w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-sm text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                    <option value="global">Umum (Semua Program Pendidikan)</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->slug }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-5">
                <label for="fee_document" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Pilih File (Foto/JPG, PDF, atau CSV)
                </label>
                <input id="fee_document" type="file" name="fee_document"
                       accept=".jpg,.jpeg,.png,.webp,.pdf,.csv,.txt" required
                       class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:h-11 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/40 dark:file:text-primary-300 cursor-pointer border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-950">
            </div>

            <div class="md:col-span-3">
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-primary-600 px-5 text-sm font-bold text-white hover:bg-primary-700 transition shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Unggah File Biaya
                </button>
            </div>
        </form>
    </div>

    {{-- Pratinjau Langsung Dokumen Biaya yang Akan Tampil di Frontend --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                Pratinjau Dokumen Aktif di Frontend ({{ count($uploadedDocuments) }})
            </h2>
        </div>

        @forelse($uploadedDocuments as $doc)
            <div class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                    <span class="inline-flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-200">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        Target: {{ $doc['target_label'] }}
                    </span>

                    <form method="POST" action="{{ route('admin.fees.destroy-document', $doc['section_key']) }}"
                          onsubmit="return confirm('Hapus dokumen biaya untuk {{ $doc['target_label'] }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 dark:border-red-900/60 bg-white dark:bg-gray-900 px-3 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer">
                            Hapus Dokumen
                        </button>
                    </form>
                </div>

                @include('partials.fee-document-preview', ['preview' => $doc['preview']])
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-10 text-center space-y-2">
                <p class="text-base font-semibold text-gray-700 dark:text-gray-300">Belum ada file biaya pendidikan yang diunggah.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Unggah berkas Foto (JPG/PNG/WEBP), PDF, atau CSV melalui formulir di atas untuk menampilkannya di halaman Pendidikan frontend.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
