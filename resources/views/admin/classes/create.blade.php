@extends('layouts.admin')

@section('title', 'Tambah Data Kelas')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Tambah Data Kelas</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Buat kelompok belajar baru.</p>
        </div>
        <a href="{{ route('admin.classes.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium">
            ← Kembali
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_kelas" value="{{ old('kode_kelas') }}" placeholder="Misal: KLS-7A" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm uppercase">
                    @error('kode_kelas') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kelas" value="{{ old('nama_kelas') }}" placeholder="Misal: Kelas 7 Ula A" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                    @error('nama_kelas') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="tingkat_kelas" value="{{ old('tingkat_kelas') }}" placeholder="Misal: 7 Ula / 8 Wustho" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                    @error('tingkat_kelas') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_ajaran_id" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}" {{ (old('tahun_ajaran_id') == $ta->id || ($taAktif && $taAktif->id == $ta->id)) ? 'selected' : '' }}>
                                {{ $ta->nama_tahun_ajaran }} ({{ $ta->semester }}) {{ $ta->status_aktif ? '- AKTIF' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Wali Kelas (Ustaz / Ustazah)</label>
                <select name="wali_kelas_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                    @foreach($pengajars as $p)
                        <option value="{{ $p->id }}" {{ old('wali_kelas_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_lengkap }} ({{ $p->nomor_identitas }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3" placeholder="Catatan tambahan..."
                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">{{ old('keterangan') }}</textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.classes.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 text-sm font-medium">Batal</a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg transition text-sm font-medium shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
