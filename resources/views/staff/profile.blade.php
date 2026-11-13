@extends('layouts.admin')

@section('title', 'Foto & Profil Saya')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <x-page-heading
        title="Foto & Profil Saya"
        subtitle="Kelola foto profil akun Anda yang ditampilkan pada bilah atas dan direktori jajaran yayasan."
    />

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-200">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Kartu Identitas & Foto Profil Saat Ini --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="relative shrink-0 self-start sm:self-center">
                @if($user->avatarUrl())
                    <img id="current-avatar-preview"
                         src="{{ $user->avatarUrl() }}"
                         alt="{{ $user->name }}"
                         style="width: 5rem; height: 5rem;"
                         class="w-20 h-20 rounded-full object-cover ring-4 ring-gray-100 dark:ring-gray-800 shadow-sm">
                @else
                    <div style="width: 5rem; height: 5rem;"
                         class="flex w-20 h-20 items-center justify-center rounded-full bg-primary-600 text-2xl font-bold text-white ring-4 ring-gray-100 dark:ring-gray-800 shadow-sm">
                        {{ $user->initials() }}
                    </div>
                @endif
            </div>

            <div class="min-w-0 flex-1 space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                    @include('partials.admin-role-badge', ['user' => $user])
                </div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->email }}</p>
                @if($user->pengajar)
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        NIY: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $user->pengajar->nomor_identitas }}</span>
                    </p>
                @endif
            </div>

            @if($user->avatarUrl())
                <form method="POST" action="{{ route('staff.profile.avatar.destroy') }}"
                      onsubmit="return confirm('Hapus foto profil saat ini dan kembalikan ke inisial?');"
                      class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-red-200 bg-white px-3.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-900/60 dark:bg-gray-900 dark:text-red-400 dark:hover:bg-red-950/40 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus Foto
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Form Unggah & Crop Foto Profil Baru --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Ganti Foto Profil</h3>
        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
            Pilih gambar berformat JPG, PNG, atau WEBP (maksimal 2 MB). Anda dapat memotong dan menyesuaikan posisi foto (rasio 1:1) sebelum menyimpan.
        </p>

        <form method="POST" action="{{ route('staff.profile.avatar.update') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
            @csrf

            <div class="image-crop-wrapper space-y-4">
                <label for="staff-avatar-input"
                       class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/70 px-6 py-8 text-center hover:border-primary-500 hover:bg-primary-50/30 dark:border-gray-700 dark:bg-gray-800/40 dark:hover:border-primary-500 dark:hover:bg-primary-950/20 transition cursor-pointer">
                    <div class="flex w-11 h-11 items-center justify-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        Klik untuk memilih foto profil baru
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        JPG, JPEG, PNG, atau WEBP · Maks. 2 MB
                    </div>
                </label>

                <input type="file"
                       id="staff-avatar-input"
                       name="avatar"
                       accept="image/jpeg,image/png,image/webp"
                       data-crop-ratio="1"
                       data-crop-label="1:1 (Foto Profil)"
                       class="image-crop-input sr-only">

                {{-- Pratinjau Foto Profil Baru beserta Nama & Email --}}
                <div class="image-crop-status hidden rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-800/50 dark:bg-emerald-950/30 space-y-3">
                    <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">
                        Pratinjau foto profil baru siap disimpan
                    </p>
                    <div class="flex items-center gap-4 rounded-xl border border-emerald-200/80 bg-white p-3.5 shadow-xs dark:border-emerald-900/60 dark:bg-gray-900">
                        <img src=""
                             alt="Pratinjau foto baru {{ $user->name }}"
                             style="width: 4rem; height: 4rem;"
                             class="image-crop-result-preview w-16 h-16 shrink-0 rounded-full object-cover ring-2 ring-emerald-500/40">
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $user->name }}</span>
                                @include('partials.admin-role-badge', ['user' => $user])
                            </div>
                            <div class="truncate text-xs font-medium text-gray-600 dark:text-gray-300">{{ $user->email }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 transition">
                    Kembali
                </a>
                <button type="submit"
                        class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary-600 px-5 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Foto Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
