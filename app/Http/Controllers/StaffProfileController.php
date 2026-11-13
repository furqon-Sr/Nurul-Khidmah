<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    /**
     * Tampilkan halaman ubah foto profil untuk seluruh jajaran yayasan (non-wali).
     */
    public function edit(): View
    {
        return view('staff.profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Simpan atau perbarui foto profil pengguna yang sedang login.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => ['required_without:avatar_cropped_data', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'avatar_cropped_data' => ['nullable', 'string'],
        ], [
            'avatar.required_without' => 'Silakan pilih foto profil terlebih dahulu.',
            'avatar.image' => 'Berkas yang diunggah harus berupa gambar.',
            'avatar.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $path = null;

        if ($request->filled('avatar_cropped_data')) {
            $path = $this->storeCroppedAvatar($request->input('avatar_cropped_data'));
        } elseif ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
        }

        if (! $path) {
            throw ValidationException::withMessages([
                'avatar' => 'Silakan pilih foto profil yang valid.',
            ]);
        }

        $this->deleteStoredAvatar($user->avatar);
        $user->update(['avatar' => $path]);

        AdminActivityLog::log('update', 'Mengubah foto profil sendiri: '.$user->name, 'User', $user->id);

        return redirect()->route('staff.profile.edit')
            ->with('success', 'Foto profil Anda berhasil diperbarui.');
    }

    /**
     * Hapus foto profil pengguna yang sedang login dan kembalikan ke inisial.
     */
    public function destroyAvatar(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->avatar) {
            $this->deleteStoredAvatar($user->avatar);
            $user->update(['avatar' => null]);

            AdminActivityLog::log('update', 'Menghapus foto profil sendiri: '.$user->name, 'User', $user->id);
        }

        return redirect()->route('staff.profile.edit')
            ->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Decode dan simpan data base64 hasil crop ke disk public.
     */
    private function storeCroppedAvatar(string $dataUri): string
    {
        if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/i', $dataUri, $matches)) {
            throw ValidationException::withMessages([
                'avatar' => 'Format hasil crop foto tidak valid. Gunakan JPG, PNG, atau WEBP.',
            ]);
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode($matches[2], true);

        if ($binary === false || strlen($binary) > 2 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'avatar' => 'Ukuran hasil crop foto melebihi batas 2 MB atau data rusak.',
            ]);
        }

        $filename = 'avatars/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }

    private function deleteStoredAvatar(?string $avatarPath): void
    {
        if ($avatarPath && ! Str::startsWith($avatarPath, ['http://', 'https://', 'data:'])) {
            Storage::disk('public')->delete($avatarPath);
        }
    }
}
