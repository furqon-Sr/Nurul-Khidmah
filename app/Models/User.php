<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'avatar', 'email_verified_at', 'must_change_password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /**
     * 6 Role Resmi Sistem SIAKAD & Website Pondok Pesantren Nurul Khidmah Lamongan
     */
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_PIMPINAN = 'pimpinan';

    public const ROLE_KEPALA_SEKOLAH = 'kepala_sekolah';

    public const ROLE_PENGURUS_AKADEMIK = 'pengurus_akademik';

    public const ROLE_TENAGA_PENGAJAR = 'tenaga_pengajar';

    public const ROLE_SANTRI = 'santri';

    // Alias kompatibilitas
    public const ROLE_ADMIN = 'pengurus_akademik';

    public const ROLE_WALI = 'santri';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the full URL of the user's avatar, or null if not set.
     */
    public function avatarUrl(): ?string
    {
        if ($this->avatar) {
            if (\Illuminate\Support\Str::startsWith($this->avatar, ['http://', 'https://', 'data:'])) {
                return $this->avatar;
            }
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
                return '/storage/' . ltrim($this->avatar, '/');
            }
        }
        return null;
    }

    public function isSuperAdmin(): bool
    {
        return ($this->role ?? self::ROLE_SANTRI) === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Akun staf internal pengelola (Super Admin / Pengurus Akademik / Kepala Sekolah).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_PENGURUS_AKADEMIK, self::ROLE_KEPALA_SEKOLAH, 'admin'], true);
    }

    public function isPimpinan(): bool
    {
        return $this->role === self::ROLE_PIMPINAN;
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === self::ROLE_KEPALA_SEKOLAH;
    }

    /**
     * Hak akses penuh SIAKAD, PPDB, dan Pengelolaan Website Yayasan bagi Staf TU / Pengurus Akademik.
     */
    public function isPengurusAkademik(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_PENGURUS_AKADEMIK, 'admin'], true);
    }

    public function isContentAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_PENGURUS_AKADEMIK, 'admin'], true);
    }

    /**
     * Hak akses kelola Website CMS & Identitas Yayasan (Staf TU / Pengurus Akademik & Super Admin).
     */
    public function canManageWebsite(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_PENGURUS_AKADEMIK, 'admin'], true);
    }

    /**
     * Hak akses kelola informasi Program & Biaya Pendidikan.
     */
    public function canManageProgramsAndFees(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_PENGURUS_AKADEMIK, 'admin'], true);
    }

    public function isTenagaPengajar(): bool
    {
        return $this->role === self::ROLE_TENAGA_PENGAJAR;
    }

    public function isSantri(): bool
    {
        return ($this->role ?? self::ROLE_SANTRI) === self::ROLE_SANTRI;
    }

    public function isWali(): bool
    {
        return $this->isSantri();
    }

    public function pengajar(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Pengajar::class, 'user_id');
    }

    /**
     * Relasi multi-anak: satu akun Wali Santri dapat menaungi banyak santri.
     */
    public function santris(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Santri::class, 'user_id');
    }

    /**
     * Kompatibilitas mundur dengan relasi tunggal santri.
     */
    public function santri(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Santri::class, 'user_id');
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin (Developer)',
            self::ROLE_PIMPINAN => 'Pimpinan Yayasan',
            self::ROLE_KEPALA_SEKOLAH => 'Kepala Sekolah / Madrasah',
            self::ROLE_PENGURUS_AKADEMIK => 'Staf TU / Pengurus Akademik',
            self::ROLE_TENAGA_PENGAJAR => 'Tenaga Pengajar (Guru)',
            self::ROLE_SANTRI => 'Wali Santri',
            default => 'Wali Santri',
        };
    }
}
