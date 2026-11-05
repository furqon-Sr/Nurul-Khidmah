<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WaliPortalAuthController extends Controller
{
    /**
     * Tampilkan halaman khusus Login Portal Wali Santri (ramah pengguna awam & mobile-first).
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isSantri()
                ? redirect()->route('wali.dashboard')
                : redirect()->route('dashboard');
        }

        return view('auth.wali-login');
    }

    /**
     * Autentikasi Terisolasi Khusus Wali Santri (NIS 8 Digit ATAU Email + Password).
     * Akun Staf/Admin/Guru/Pimpinan DITOLAK MUTLAK pada pintu publik ini demi keamanan berlapis.
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Proteksi Honeypot Anti-Bot (tanpa membebani Wali Santri dengan CAPTCHA rumit)
        if ($request->filled('website_url')) {
            abort(422, 'Permintaan tidak valid.');
        }

        $validated = $request->validate([
            'identitas' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'identitas.required' => 'Silakan masukkan Nomor Induk Santri (NIS) atau Email Wali.',
            'password.required' => 'Silakan masukkan kata sandi akun Wali Santri.',
        ]);

        $identitas = trim($validated['identitas']);
        $throttleKey = 'wali-portal:' . Str::lower($identitas) . '|' . $request->ip();

        // 2. Proteksi Brute-Force Rate Limiting (Maksimal 5 percobaan per 60 detik)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'identitas' => "Terlalu banyak percobaan masuk. Silakan tunggu {$seconds} detik sebelum mencoba kembali.",
            ]);
        }

        // 3. Cari akun berdasarkan NIS Ananda (prioritas kemudahan Wali Santri) atau Email
        $user = null;
        $santriByNis = Santri::with('user')->where('nis', $identitas)->first();
        if ($santriByNis && $santriByNis->user) {
            $user = $santriByNis->user;
        } elseif (filter_var($identitas, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', Str::lower($identitas))->first();
        }

        // 4. Hard Role Isolation: tolak mutlak jika bukan role 'santri' (Wali Santri) sekalipun sandi benar
        if ($user && ! $user->isSantri()) {
            RateLimiter::hit($throttleKey, 60);
            AdminActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'login_failed',
                'target_type' => 'WaliPortal',
                'description' => "Percobaan login akun non-wali ({$user->role}) melalui pintu publik /portal-wali diblokir otomatis.",
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'identitas' => 'NIS/Email atau kata sandi tidak sesuai dengan data Portal Wali Santri.',
            ]);
        }

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'identitas' => 'NIS/Email atau kata sandi tidak sesuai. Pastikan NIS Ananda dan kata sandi sudah benar.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (isset($santriByNis) && $santriByNis) {
            session(['active_wali_santri_id' => $santriByNis->id]);
        }

        if ($user->must_change_password) {
            session()->flash('warning', 'Pemberitahuan Keamanan: Akun Anda masih menggunakan kata sandi sementara. Silakan segera ubah kata sandi di menu Pengaturan Akun.');
        }

        return redirect()->intended(route('wali.dashboard'));
    }
}
