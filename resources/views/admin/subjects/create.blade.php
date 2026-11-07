@extends('layouts.admin')

@section('title', 'Tambah Mata Pelajaran')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Tambah Mata Pelajaran</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Tambahkan bidang studi baru ke kurikulum.</p>
        </div>
        <a href="{{ route('admin.subjects.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium">
            ← Kembali
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Mapel <span class="text-rose-500">*</span></label>
                <input type="text" name="kode_mapel" value="{{ old('kode_mapel') }}" placeholder="Misal: MP-001" required
                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm uppercase">
                @error('kode_mapel') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_mapel" value="{{ old('nama_mapel') }}" placeholder="Misal: Fiqih Ibadaat" required
                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                @error('nama_mapel') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3" placeholder="Deskripsi atau cakupan materi mapel..."
                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">{{ old('keterangan') }}</textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.subjects.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 text-sm font-medium">Batal</a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition text-sm font-medium shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
