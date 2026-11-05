<?php

namespace App\Policies;

use App\Models\JadwalPelajaran;
use App\Models\Nilai;
use App\Models\RaporSemester;
use App\Models\TahunAjaran;
use App\Models\User;

class NilaiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTenagaPengajar() || $user->isPimpinan() || $user->isKepalaSekolah();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Nilai $nilai): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTenagaPengajar();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Nilai $nilai): bool
    {
        return $this->canModify($user, $nilai);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Nilai $nilai): bool
    {
        return $this->canModify($user, $nilai);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Nilai $nilai): bool
    {
        return $this->canModify($user, $nilai);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Nilai $nilai): bool
    {
        return $this->canModify($user, $nilai);
    }

    /**
     * Logika Penguncian Nilai (Grade Locking & Immutability).
     */
    protected function canModify(User $user, Nilai $nilai): bool
    {
        $schedule = $nilai->relationLoaded('jadwalPelajaran')
            ? $nilai->jadwalPelajaran
            : ($nilai->jadwal_pelajaran_id ? JadwalPelajaran::with('tahunAjaran')->find($nilai->jadwal_pelajaran_id) : null);

        // a) Pastikan user yang login adalah guru yang mengajar di jadwal tersebut ATAU adalah super_admin
        $isSuperAdmin = $user->isSuperAdmin();
        $isPengajar = false;

        if ($user->isTenagaPengajar() && $schedule) {
            $pengajarId = $user->pengajar?->id;
            if ($pengajarId && (int) $schedule->pengajar_id === (int) $pengajarId) {
                $isPengajar = true;
            }
        }

        if (! $isSuperAdmin && ! $isPengajar) {
            return false;
        }

        // b) Lakukan pengecekan ke tabel rapor_semesters berdasarkan santri_id dan tahun_ajaran_id (dari relasi jadwal)
        $santriId = $nilai->santri_id;
        $tahunAjaranId = $schedule?->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;

        if ($santriId && $tahunAjaranId) {
            $rapor = null;
            if ($nilai->relationLoaded('santri') && $nilai->santri?->relationLoaded('raporSemesters')) {
                $rapor = $nilai->santri->raporSemesters->firstWhere('tahun_ajaran_id', $tahunAjaranId);
            } else {
                $rapor = RaporSemester::where('santri_id', $santriId)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->first();
            }

            // c) Jika status_publikasi bernilai approved atau published, maka KEMBALIKAN false (aksi ditolak)
            if ($rapor && in_array($rapor->status_publikasi, ['approved', 'published'], true)) {
                return false;
            }
        }

        return true;
    }
}
