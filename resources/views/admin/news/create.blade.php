@extends('layouts.admin')

@section('title', 'Tambah Berita')

@section('content')
<div class="mb-6 flex items-center">
    <a href="{{ route('admin.news.index') }}" class="text-green-700 hover:text-green-900 mr-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Berita Baru</h1>
        <p class="text-gray-600">Buat artikel berita yang akan ditampilkan di website.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
    <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-6">
                <!-- Judul -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Judul Berita <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 p-2 border bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Konten -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Isi Berita <span class="text-red-500">*</span></label>
                    <textarea name="content" id="content" rows="12" required class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 p-2 border bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">{{ old('content') }}</textarea>
                    @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-6">
                @include('partials.image-crop-field', [
                    'name' => 'main_image_path',
                    'label' => 'Gambar Utama',
                    'required' => true,
                    'aspectRatio' => 16/9,
                    'aspectLabel' => '16:9 (banner berita)',
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'hint' => 'Gambar akan ditampilkan sebagai banner artikel. Pilih file lalu sesuaikan area crop di modal.',
                ])

                <!-- Kategori -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Kategori</label>
                    <select name="category" id="category" class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 p-2.5 border bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm">
                        <option value="">-- Tanpa Kategori --</option>
                        <option value="Pengumuman" {{ old('category') === 'Pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                        <option value="Kegiatan" {{ old('category') === 'Kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="Prestasi" {{ old('category') === 'Prestasi' ? 'selected' : '' }}>Prestasi</option>
                        <option value="Artikel" {{ old('category') === 'Artikel' ? 'selected' : '' }}>Artikel</option>
                        <option value="PPDB" {{ old('category') === 'PPDB' ? 'selected' : '' }}>PPDB</option>
                    </select>
                    @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tanggal Publikasi -->
                <div>
                    <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Tanggal Publikasi</label>
                    <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 p-2 border bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
                    <p class="text-xs text-gray-600 dark:text-slate-400 mt-1">Default mengikuti waktu saat ini. Anda dapat mengeditnya untuk menentukan waktu publikasi lain.</p>
                    @error('published_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.news.index') }}" class="px-5 py-2 border border-gray-300 dark:border-slate-700 rounded-md text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition font-medium">Batal</a>
            <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 transition font-medium shadow-sm">Simpan Berita</button>
        </div>
    </form>
</div>
@endsection
