<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Santri extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'santris';

    protected $fillable = [
        'user_id',
        'kelas_id',
        'educational_unit_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'previous_school',
        'boarding_status',
        'nama_wali',
        'guardian_occupation',
        'no_hp_wali',
        'tahun_masuk',
        'status_santri',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function educationalUnit(): BelongsTo
    {
        return $this->belongsTo(EducationalUnit::class, 'educational_unit_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function absensis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Absensi::class, 'santri_id');
    }

    public function nilais(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Nilai::class, 'santri_id');
    }

    public function registration(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SantriRegistration::class, 'santri_id');
    }

    public function riwayatKelas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RiwayatKelasSantri::class, 'santri_id');
    }

    public function raporSemesters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RaporSemester::class, 'santri_id');
    }

    /**
     * Ambil data rapor semester untuk tahun ajaran tertentu (default: tahun ajaran aktif atau kelas saat ini).
     */
    public function raporSemesterForYear(?int $tahunAjaranId = null): ?RaporSemester
    {
        $taId = $tahunAjaranId ?? TahunAjaran::getAktif()?->id ?? $this->kelas?->tahun_ajaran_id;

        if ($this->relationLoaded('raporSemesters')) {
            if (! $taId) {
                return $this->raporSemesters->sortByDesc('id')->first();
            }

            return $this->raporSemesters->firstWhere('tahun_ajaran_id', $taId);
        }

        if (! $taId) {
            return $this->raporSemesters()->latest('id')->first();
        }

        return $this->raporSemesters()->where('tahun_ajaran_id', $taId)->first();
    }

    /**
     * Accessor nilai sikap dari rapor semester aktif (kompatibilitas mundur).
     */
    public function getNilaiSikapAttribute(): ?string
    {
        return $this->raporSemesterForYear()?->nilai_sikap;
    }

    /**
     * Accessor catatan wali kelas dari rapor semester aktif (kompatibilitas mundur).
     */
    public function getCatatanWaliKelasAttribute(): ?string
    {
        return $this->raporSemesterForYear()?->catatan_wali_kelas;
    }

    public function genderLabel(): string
    {
        return in_array($this->jenis_kelamin, ['L', 'Laki-laki', 'Laki-Laki'], true)
            ? 'Laki-laki'
            : 'Perempuan';
    }

    public function fotoUrl(): ?string
    {
        $path = null;

        if ($this->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto)) {
            $path = $this->foto;
        } elseif ($this->relationLoaded('registration') && $this->registration) {
            $regPhoto = $this->registration->file_pasfoto ?: $this->registration->student_photo;
            if ($regPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($regPhoto)) {
                $path = $regPhoto;
            }
        } elseif ($this->registration) {
            $regPhoto = $this->registration->file_pasfoto ?: $this->registration->student_photo;
            if ($regPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($regPhoto)) {
                $path = $regPhoto;
            }
        }

        if ($path) {
            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', 'data:'])) {
                return $path;
            }
            return '/storage/' . ltrim($path, '/');
        }

        return null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->nama_lengkap ?? ''));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_strtoupper(mb_substr($w, 0, 1));
        }
        return $initials ?: 'S';
    }
}
