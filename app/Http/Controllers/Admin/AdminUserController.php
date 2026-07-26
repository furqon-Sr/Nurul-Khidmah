<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    use PasswordValidationRules;

    public function index(): View
    {
        $users = User::query()
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar', 'email_verified_at', 'created_at']);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => $this->passwordRules(),
            'role' => ['required', 'in:super_admin,admin'],
        ]);

        $data = [
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => $validated['password'],
            'role'              => $validated['role'],
            'email_verified_at' => now(),
        ];

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $newUser = User::create($data);

        AdminActivityLog::log('create', 'Membuat pengguna admin baru: '.$newUser->name.' ('.$newUser->email.')', 'User', $newUser->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna admin baru berhasil ditambahkan. Mereka dapat langsung masuk ke panel.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:super_admin,admin'],
        ]);

        if ($user->is(Auth::user()) && $user->isSuperAdmin() && $validated['role'] === User::ROLE_ADMIN) {
            $otherSuperCount = User::query()
                ->where('role', User::ROLE_SUPER_ADMIN)
                ->where('id', '!=', $user->id)
                ->count();
            if ($otherSuperCount < 1) {
                return redirect()->route('admin.users.index')
                    ->withErrors(['role' => 'Tidak dapat menurunkan peran Anda sendiri: harus ada minimal satu akun Super Admin lain.']);
            }
        }

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        AdminActivityLog::log('update', 'Mengubah role '.$user->name.' dari '.$oldRole.' menjadi '.$validated['role'], 'User', $user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Peran untuk '.$user->name.' telah diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(Auth::user())) {
            return redirect()->route('admin.users.index')
                ->withErrors(['delete' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($user->isSuperAdmin()) {
            $superCount = User::where('role', User::ROLE_SUPER_ADMIN)->count();
            if ($superCount <= 1) {
                return redirect()->route('admin.users.index')
                    ->withErrors(['delete' => 'Tidak dapat menghapus '.$user->name.': harus ada minimal satu akun Super Admin aktif.']);
            }
        }

        $name = $user->name;
        $userId = $user->id;

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();

        AdminActivityLog::log('delete', 'Menghapus pengguna admin: '.$name, 'User', $userId);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna "'.$name.'" berhasil dihapus dari sistem.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => $this->passwordRules(),
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AdminActivityLog::log('update', 'Mereset password untuk: '.$user->name, 'User', $user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password untuk "'.$user->name.'" berhasil direset.');
    }

    public function updateAvatar(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        AdminActivityLog::log('update', 'Mengubah foto profil: '.$user->name, 'User', $user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Foto profil "'.$user->name.'" berhasil diperbarui.');
    }

    public function toggleVerification(User $user): RedirectResponse
    {
        if ($user->is(Auth::user())) {
            return redirect()->route('admin.users.index')
                ->withErrors(['role' => 'Anda tidak dapat mengubah status verifikasi akun Anda sendiri.']);
        }

        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
            AdminActivityLog::log('update', 'Membatalkan verifikasi akun admin: '.$user->name.' ('.$user->email.')', 'User', $user->id);
            $msg = 'Verifikasi akun "'.$user->name.'" berhasil dibatalkan. Akun ini tidak aktif.';
        } else {
            $user->update(['email_verified_at' => now()]);
            AdminActivityLog::log('update', 'Memverifikasi akun admin: '.$user->name.' ('.$user->email.')', 'User', $user->id);
            $msg = 'Akun "'.$user->name.'" berhasil diverifikasi dan sekarang aktif.';
        }

        return redirect()->route('admin.users.index')->with('success', $msg);
    }
}
