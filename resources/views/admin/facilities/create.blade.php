@extends('layouts.admin')

@section('title', 'Tambah Fasilitas')

@section('content')
<div class="mb-6 flex items-center">
    <a href="{{ route('admin.facilities.index') }}" class="text-green-700 hover:text-green-900 mr-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Fasilitas Baru</h1>
        <p class="text-gray-600">Buat data fasilitas pondok pesantren.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
    <form action="{{ route('admin.facilities.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-6">
                <!-- Nama Fasilitas -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Nama Fasilitas <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 p-2 border bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="6" class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 p-2 border bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-6">
                @include('partials.image-crop-field', [
                    'name' => 'icon_or_image_path',
                    'label' => 'Gambar / Ikon Fasilitas',
                    'required' => true,
                    'aspectRatio' => 1,
                    'aspectLabel' => '1:1 (ikon persegi)',
                    'accept' => 'image/jpeg,image/png,image/jpg',
                ])
            </div>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.facilities.index') }}" class="px-5 py-2 border border-gray-300 dark:border-slate-700 rounded-md text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition font-medium">Batal</a>
            <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 transition font-medium shadow-sm">Simpan Fasilitas</button>
        </div>
    </form>
</div>
@endsection
