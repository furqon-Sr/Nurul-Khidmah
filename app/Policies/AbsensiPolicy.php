<?php

namespace App\Policies;

use App\Models\Absensi;
use App\Models\JadwalPelajaran;
use App\Models\RaporSemester;
use App\Models\TahunAjaran;
use App\Models\User;

class AbsensiPolicy
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
    public function view(User $user, Absensi $absensi): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isPengurusAkademik()
            || $user->isKepalaSekolah()
            || $user->isTenagaPengajar();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Absensi $absensi): bool
    {
        return $this->canModify($user, $absensi);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Absensi $absensi): bool
    {
        return $this->canModify($user, $absensi);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Absensi $absensi): bool
    {
        return $this->canModify($user, $absensi);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Absensi $absensi): bool
    {
        return $this->canModify($user, $absensi);
    }

    /**
     * Logika Penguncian Absensi (Attendance Locking & Immutability).
     */
    protected function canModify(User $user, Absensi $absensi): bool
    {
        $schedule = $absensi->relationLoaded('jadwalPelajaran')
            ? $absensi->jadwalPelajaran
            : ($absensi->jadwal_pelajaran_id ? JadwalPelajaran::with('tahunAjaran')->find($absensi->jadwal_pelajaran_id) : null);

        // a) Izinkan Super Admin, Pengurus Akademik (Staf TU), Kepala Sekolah (Supervisi), atau guru pengampu jadwal tersebut
        $hasAcademicAuthority = $user->isSuperAdmin()
            || $user->isPengurusAkademik()
            || $user->isKepalaSekolah();
        $isPengajar = false;

        if ($user->isTenagaPengajar() && $schedule) {
            $pengajarId = $user->pengajar?->id;
            if ($pengajarId && (int) $schedule->pengajar_id === (int) $pengajarId) {
                $isPengajar = true;
            }
        }

        if (! $hasAcademicAuthority && ! $isPengajar) {
            return false;
        }

        // b) Lakukan pengecekan ke tabel rapor_semesters berdasarkan santri_id dan tahun_ajaran_id (dari relasi jadwal)
        $santriId = $absensi->santri_id;
        $tahunAjaranId = $schedule?->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;

        if ($santriId && $tahunAjaranId) {
            $rapor = null;
            if ($absensi->relationLoaded('santri') && $absensi->santri?->relationLoaded('raporSemesters')) {
                $rapor = $absensi->santri->raporSemesters->firstWhere('tahun_ajaran_id', $tahunAjaranId);
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
