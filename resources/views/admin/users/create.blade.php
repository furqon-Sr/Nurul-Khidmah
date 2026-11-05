@extends('layouts.admin')

@section('title', 'Tambah Pengguna Admin')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Tambah Pengguna Admin</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Buat akun baru untuk staf yang mengelola konten atau pengaturan (sesuai role).</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-600 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950 transition">
        ← Kembali ke daftar
    </a>
</div>

<div class="max-w-xl bg-white dark:bg-slate-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-slate-700 p-6">
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 dark:bg-red-900/40 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Foto Profil --}}
        <div class="flex flex-col items-center gap-3 pb-5 border-b border-gray-200 dark:border-slate-700">
            <div class="relative group/avatar cursor-pointer" onclick="document.getElementById('avatar-create-input').click()">
                <div id="avatar-preview-circle"
                     class="h-24 w-24 rounded-full bg-gradient-to-br from-emerald-300 to-emerald-600 flex items-center justify-center ring-4 ring-white dark:ring-slate-700 shadow-lg overflow-hidden">
                    <svg id="avatar-placeholder-icon" class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <img id="avatar-create-preview" src="" alt="" class="w-full h-full object-cover hidden">
                </div>
                {{-- Overlay kamera --}}
                <div class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <input type="file" name="avatar" id="avatar-create-input" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewCreateAvatar(this)">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-700 dark:text-slate-300">Foto Profil <span class="text-gray-400">(opsional)</span></p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">JPG, PNG, atau WebP · Maks 2MB · Klik foto untuk memilih</p>
            </div>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-800 dark:text-slate-200">Nama lengkap</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-800 dark:text-slate-200">Email (untuk login)</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-800 dark:text-slate-200">Kata sandi</label>
            <input type="password" name="password" id="password" required autocomplete="new-password"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
            <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">Gunakan kata sandi kuat. Pengguna dapat mengubahnya sendiri setelah masuk.</p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-800 dark:text-slate-200">Ulangi kata sandi</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-800 dark:text-slate-200">Role &amp; Hak Akses</label>
            <select name="role" id="role" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                <option value="pengurus_akademik" @selected(old('role', 'pengurus_akademik') === 'pengurus_akademik')>Staf TU / Pengurus Akademik — kelola SIAKAD, PPDB, santri, jadwal, rapor, &amp; CMS website</option>
                <option value="kepala_sekolah" @selected(old('role') === 'kepala_sekolah')>Kepala Sekolah / Madrasah — pantau absensi &amp; nilai guru, leger nilai, &amp; validasi rapor</option>
                <option value="tenaga_pengajar" @selected(old('role') === 'tenaga_pengajar')>Tenaga Pengajar (Guru / Ustaz) — input presensi, pengolahan nilai santri, &amp; wali kelas</option>
                <option value="pimpinan" @selected(old('role') === 'pimpinan')>Pimpinan Yayasan — dashboard eksekutif (read-only) &amp; track record santri PDF</option>
                <option value="santri" @selected(old('role') === 'santri')>Wali Santri — akses portal informasi nilai &amp; presensi ananda</option>
                <option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin — akses penuh seluruh modul sistem &amp; manajemen pengguna</option>
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-200 dark:border-slate-700">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-[#0D6B3A] hover:bg-emerald-800 rounded-lg shadow transition">
                Simpan pengguna
            </button>
        </div>
    </form>
</div>

<script>
    function previewCreateAvatar(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-create-preview');
            const placeholder = document.getElementById('avatar-placeholder-icon');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
</script>
@endsection
