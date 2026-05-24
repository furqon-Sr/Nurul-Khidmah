<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
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

        // Upload foto profil jika ada
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        User::create($data);

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

        $user->update(['role' => $validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Peran untuk '.$user->name.' telah diperbarui.');
    }

    /**
     * Hapus akun pengguna admin.
     * Proteksi: tidak bisa hapus diri sendiri; tidak bisa hapus satu-satunya Super Admin.
     */
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
        // Hapus file avatar dari storage jika ada
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna "'.$name.'" berhasil dihapus dari sistem.');
    }

    /**
     * Reset password pengguna admin oleh Super Admin.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => $this->passwordRules(),
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password untuk "'.$user->name.'" berhasil direset.');
    }

    /**
     * Upload / ganti foto profil pengguna admin.
     */
    public function updateAvatar(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Hapus avatar lama
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Foto profil "'.$user->name.'" berhasil diperbarui.');
    }
}
