@extends('layouts.admin')

@section('title', 'Pengguna Admin')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Pengguna & Role</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm mt-1 max-w-2xl">
            Kelola siapa saja yang boleh masuk ke panel admin. Gunakan <strong>Tambah pengguna</strong> untuk membuat akun baru;
            ubah role, reset password, atau foto profil langsung dari tabel di bawah.
        </p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-[#0D6B3A] hover:bg-emerald-800 rounded-lg shadow transition shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah pengguna admin
    </a>
</div>

{{-- Flash messages --}}
@if (session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-800 dark:bg-green-900/40 dark:border-green-700 dark:text-green-200 px-4 py-3 rounded-lg text-sm flex items-center gap-2" role="alert">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
@endif

@if ($errors->has('role'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-800 dark:bg-red-900/40 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded-lg text-sm" role="alert">
        {{ $errors->first('role') }}
    </div>
@endif

@if ($errors->has('password'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-800 dark:bg-red-900/40 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded-lg text-sm" role="alert">
        <strong>Reset Password Gagal:</strong> {{ $errors->first('password') }}
    </div>
@endif

@if ($errors->has('delete'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-800 dark:bg-red-900/40 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded-lg text-sm" role="alert">
        {{ $errors->first('delete') }}
    </div>
@endif

@if ($errors->has('avatar'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-800 dark:bg-red-900/40 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded-lg text-sm" role="alert">
        <strong>Upload Foto Gagal:</strong> {{ $errors->first('avatar') }}
    </div>
@endif

<div class="bg-white dark:bg-slate-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
            <thead class="bg-gray-50 dark:bg-slate-900">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Pengguna</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Email Terverifikasi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Terdaftar</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Ubah Role</th>
                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-900/60 transition-colors group">
                        {{-- Kolom Pengguna dengan foto profil --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                {{-- Avatar dengan tombol upload overlay --}}
                                <div class="relative shrink-0 group/avatar">
                                    @if($user->avatarUrl())
                                        <img src="{{ $user->avatarUrl() }}"
                                             alt="Foto {{ $user->name }}"
                                             class="h-12 w-12 rounded-full object-cover ring-2 ring-white dark:ring-slate-700 shadow">
                                    @else
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-700 text-white text-sm font-bold ring-2 ring-white dark:ring-slate-700 shadow">
                                            {{ $user->initials() }}
                                        </span>
                                    @endif

                                    {{-- Overlay kamera saat hover --}}
                                    <label for="avatar-input-{{ $user->id }}"
                                           class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity cursor-pointer"
                                           title="Ganti foto profil">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </label>

                                    {{-- Form upload tersembunyi --}}
                                    <form id="avatar-form-{{ $user->id }}"
                                          method="POST"
                                          action="{{ route('admin.users.update-avatar', $user) }}"
                                          enctype="multipart/form-data"
                                          class="hidden">
                                        @csrf
                                        <input type="file"
                                               id="avatar-input-{{ $user->id }}"
                                               name="avatar"
                                               accept="image/jpeg,image/png,image/webp"
                                               class="hidden"
                                               onchange="previewAndSubmit(this, {{ $user->id }})">
                                    </form>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ $user->name }}</span>
                                        @if($user->id === auth()->id())
                                            <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 px-1.5 py-0.5 rounded font-medium">Anda</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 truncate max-w-[180px]">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            @include('partials.admin-role-badge', ['user' => $user])
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if ($user->email_verified_at)
                                <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Belum
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                            {{ $user->created_at?->timezone(config('app.timezone'))->format('d M Y') }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="role"
                                        class="text-sm rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-1.5 pr-8">
                                    <option value="admin" @selected($user->role === \App\Models\User::ROLE_ADMIN)>Admin</option>
                                    <option value="super_admin" @selected($user->role === \App\Models\User::ROLE_SUPER_ADMIN)>Super Admin</option>
                                </select>
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-md transition">
                                    Simpan
                                </button>
                            </form>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Toggle Verification Button (tidak tampil untuk diri sendiri) --}}
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-verification', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->email_verified_at)
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded-md transition"
                                                    title="Nonaktifkan / Batalkan Verifikasi Akun"
                                                    onclick="return confirm('Nonaktifkan akun {{ addslashes($user->name) }}? Pengguna ini tidak akan bisa login.')">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 rounded-md transition"
                                                    title="Verifikasi Akun Admin">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                Verifikasi
                                            </button>
                                        @endif
                                    </form>
                                @endif

                                {{-- Reset Password Button --}}
                                <button type="button"
                                        onclick="openResetModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/40 rounded-md transition"
                                        title="Reset Password">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    Reset PW
                                </button>

                                {{-- Delete Button (tidak tampil untuk diri sendiri) --}}
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}? Aksi ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-md transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-slate-600 italic px-2">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Hint kamera --}}
<p class="mt-3 text-xs text-gray-400 dark:text-slate-500 text-center">
    💡 Arahkan kursor ke foto profil untuk menggantinya. Format: JPG, PNG, atau WebP, maks 2MB.
</p>

{{-- Modal Reset Password --}}
<div id="reset-password-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-gray-200 dark:border-slate-700 w-full max-w-md">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Reset Password
            </h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Atur kata sandi baru untuk <strong id="reset-user-name" class="text-gray-800 dark:text-slate-200"></strong>.</p>
        </div>
        <form id="reset-password-form" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-slate-300">Kata Sandi Baru</label>
                    <input type="password" name="password" id="new_password" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-slate-300">Ulangi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" id="new_password_confirmation" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" onclick="closeResetModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow transition">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Preview Foto --}}
<div id="avatar-preview-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-gray-200 dark:border-slate-700 w-full max-w-sm text-center overflow-hidden">
        <div class="px-6 pt-6 pb-4">
            <div class="flex justify-center mb-4">
                <img id="avatar-preview-img" src="" alt="Preview" class="w-32 h-32 rounded-full object-cover ring-4 ring-emerald-200 dark:ring-emerald-800 shadow-lg">
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-slate-100">Konfirmasi Foto Profil</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Upload foto ini sebagai foto profil?</p>
        </div>
        <div class="px-6 pb-6 flex gap-3 justify-center">
            <button type="button" onclick="cancelAvatarUpload()"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
                Batal
            </button>
            <button type="button" id="avatar-confirm-btn"
                    class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg shadow transition">
                Upload Foto
            </button>
        </div>
    </div>
</div>

<script>
    // ─── Reset Password Modal ───────────────────────────────────────────────
    function openResetModal(userId, userName) {
        document.getElementById('reset-user-name').textContent = userName;
        document.getElementById('reset-password-form').action = '/admin/users/' + userId + '/reset-password';
        document.getElementById('reset-password-modal').classList.remove('hidden');
        document.getElementById('new_password').value = '';
        document.getElementById('new_password_confirmation').value = '';
        setTimeout(() => document.getElementById('new_password').focus(), 100);
    }

    function closeResetModal() {
        document.getElementById('reset-password-modal').classList.add('hidden');
    }

    document.getElementById('reset-password-modal').addEventListener('click', function(e) {
        if (e.target === this) closeResetModal();
    });

    // ─── Avatar Upload ──────────────────────────────────────────────────────
    let pendingAvatarFormId = null;

    function previewAndSubmit(input, userId) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        // Validasi ukuran sisi client (maks 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview-img').src = e.target.result;
            document.getElementById('avatar-preview-modal').classList.remove('hidden');
            pendingAvatarFormId = 'avatar-form-' + userId;
        };
        reader.readAsDataURL(file);
    }

    function cancelAvatarUpload() {
        document.getElementById('avatar-preview-modal').classList.add('hidden');
        // Reset input file agar bisa pilih lagi
        if (pendingAvatarFormId) {
            const form = document.getElementById(pendingAvatarFormId);
            if (form) form.reset();
        }
        pendingAvatarFormId = null;
    }

    document.getElementById('avatar-confirm-btn').addEventListener('click', function() {
        if (pendingAvatarFormId) {
            const form = document.getElementById(pendingAvatarFormId);
            if (form) form.submit();
        }
    });

    document.getElementById('avatar-preview-modal').addEventListener('click', function(e) {
        if (e.target === this) cancelAvatarUpload();
    });

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeResetModal();
            cancelAvatarUpload();
        }
    });
</script>
@endsection
