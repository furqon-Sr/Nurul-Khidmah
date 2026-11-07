@extends('layouts.admin')
@section('title', 'Tambah Program Pendidikan')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.programs.index') }}" class="text-sm text-green-700 hover:underline flex items-center gap-1">
        &larr; Kembali ke Daftar Program
    </a>
    <h1 class="text-2xl font-bold text-gray-800 mt-2">Tambah Program Pendidikan</h1>
</div>

@if($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 max-w-2xl">
    <form action="{{ route('admin.programs.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-100 mb-1">Nama Program <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   placeholder="Contoh: Madrasah Tsanawiyah (MTs)"
                   class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
            <p class="text-xs text-gray-400 dark:text-slate-400 mt-1">Slug URL akan digenerate otomatis dari nama ini.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-100 mb-1">Deskripsi Singkat</label>
            <textarea name="description" rows="4" placeholder="Deskripsi program pendidikan..."
                      class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">{{ old('description') }}</textarea>
        </div>

        @include('partials.image-crop-field', [
            'name' => 'image',
            'label' => 'Gambar Program',
            'required' => false,
            'aspectRatio' => 4/3,
            'aspectLabel' => '4:3 (kartu program)',
            'hint' => 'Opsional. Pilih file lalu atur crop di modal sebelum menyimpan.',
        ])

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-100 mb-1">Urutan Tampil</label>
            <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                   class="w-32 rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
            <p class="text-xs text-gray-400 dark:text-slate-400 mt-1">Angka kecil tampil lebih dahulu.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-gray-50 dark:bg-slate-800 rounded-xl p-4 border border-gray-100 dark:border-slate-700">
            <label class="flex items-center justify-between gap-4 cursor-pointer">
                <div>
                    <p class="font-semibold text-gray-700 dark:text-slate-100 text-sm">Tampil di Website</p>
                    <p class="text-xs text-gray-400 dark:text-slate-400">Jika dimatikan, program tidak muncul di halaman publik.</p>
                </div>
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                       class="sr-only peer" id="toggle-active">
                <label for="toggle-active"
                       class="relative w-11 h-6 bg-gray-300 peer-checked:bg-primary-600 rounded-full cursor-pointer transition-colors after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5">
                </label>
            </label>

            <label class="flex items-center justify-between gap-4 cursor-pointer">
                <div>
                    <p class="font-semibold text-gray-700 dark:text-slate-100 text-sm">Pendaftaran Terbuka</p>
                    <p class="text-xs text-gray-400 dark:text-slate-400">Jika diaktifkan, tombol "Daftar Sekarang" akan muncul.</p>
                </div>
                <input type="checkbox" name="is_registration_open" value="1" {{ old('is_registration_open') ? 'checked' : '' }}
                       class="sr-only peer" id="toggle-reg">
                <label for="toggle-reg"
                       class="relative w-11 h-6 bg-gray-300 peer-checked:bg-blue-500 rounded-full cursor-pointer transition-colors after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5">
                </label>
            </label>

            <label class="flex items-center justify-between gap-4 cursor-pointer">
                <div>
                    <p class="font-semibold text-gray-700 dark:text-slate-100 text-sm">Segera Hadir / Perencanaan</p>
                    <p class="text-xs text-gray-400 dark:text-slate-400">Jika diaktifkan, statusnya adalah rencana pembangunan.</p>
                </div>
                <input type="checkbox" name="is_coming_soon" value="1" {{ old('is_coming_soon') ? 'checked' : '' }}
                       class="sr-only peer" id="toggle-soon">
                <label for="toggle-soon"
                       class="relative w-11 h-6 bg-gray-300 peer-checked:bg-amber-500 rounded-full cursor-pointer transition-colors after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5">
                </label>
            </label>
        </div>

        <div class="border-t border-gray-100 dark:border-slate-700 pt-5 flex justify-end">
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 hover:bg-green-800 text-white font-semibold rounded-lg shadow transition">
                Simpan Program
            </button>
        </div>
    </form>
</div>
@endsection
