<?php

namespace App\Actions;

use App\Models\EducationalUnit;
use App\Models\Kelas;
use App\Models\RiwayatKelasSantri;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcceptSantriRegistration
{
    /**
     * Memproses penerimaan santri baru secara otomatis (One-Click Onboarding).
     *
     * (a) Ubah status pendaftar jadi accepted.
     * (b) Generate NIS otomatis berdasarkan tahun dan unit (misal: 26MTS001).
     * (c) Masukkan data ke tabel santris & catat riwayat kelas.
     * (d) Sambungkan atau buat akun Wali (sesuai logika Fase 1).
     */
    public function execute(SantriRegistration $registration): array
    {
        // 1. Ubah status pendaftar jadi accepted
        $registration->update(['status' => 'accepted']);

        // Idempotensi: jika pendaftar sudah memiliki santri_id
        if ($registration->santri_id) {
            $existingSantri = Santri::with('user')->find($registration->santri_id);
            if ($existingSantri) {
                $existingSantri->update(['status_santri' => 'Aktif']);

                return [
                    'santri' => $existingSantri,
                    'user' => $existingSantri->user,
                    'nis' => $existingSantri->nis,
                    'email' => $existingSantri->user?->email ?? $registration->guardian_email,
                    'password' => '(Akun Wali Santri Sudah Aktif)',
                    'is_new_user' => false,
                ];
            }
        }

        return DB::transaction(function () use ($registration) {
            $studentName = $registration->student_name ?? 'Santri';
            $guardianName = $registration->guardian_name ?? 'Wali Santri';
            $guardianPhone = trim($registration->guardian_phone ?? '');
            $email = strtolower(trim($registration->guardian_email ?? ''));

            // 1. Sambungkan atau buat akun Wali (sesuai logika Fase 1)
            $existingWaliUser = null;

            if ($email !== '') {
                $userByEmail = User::where('email', $email)->first();
                if ($userByEmail && $userByEmail->isSantri()) {
                    $existingWaliUser = $userByEmail;
                }
            }

            if (! $existingWaliUser && $guardianPhone !== '') {
                $santriWithSamePhone = Santri::where('no_hp_wali', $guardianPhone)
                    ->whereHas('user', fn ($uq) => $uq->where('role', User::ROLE_SANTRI))
                    ->with('user')
                    ->first();
                if ($santriWithSamePhone && $santriWithSamePhone->user) {
                    $existingWaliUser = $santriWithSamePhone->user;
                }
            }

            $isNewUser = false;
            $plainPassword = null;

            if ($existingWaliUser) {
                $user = $existingWaliUser;
            } else {
                $isNewUser = true;
                $plainPassword = 'wali123';

                if ($email === '' || User::where('email', $email)->exists()) {
                    $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $studentName)) ?: 'santri';
                    $email = 'wali.'.substr($cleanName, 0, 16).'.'.$registration->registration_number.'@wali.nurulkhidmah.org';
                }

                $user = User::create([
                    'name' => $guardianName.' (Wali Santri)',
                    'email' => $email,
                    'password' => Hash::make($plainPassword),
                    'role' => User::ROLE_SANTRI,
                    'must_change_password' => true,
                    'email_verified_at' => now(),
                ]);
            }

            if (! $user->email_verified_at) {
                $user->update(['email_verified_at' => now()]);
            }

            // 2. Tentukan Tahun Ajaran & Kelas Default
            $taAktif = TahunAjaran::getAktif();
            $kelasQuery = Kelas::query()->when($taAktif, fn ($q) => $q->where('tahun_ajaran_id', $taAktif->id));
            $matchedKelas = (clone $kelasQuery)
                ->where(function ($q) use ($registration) {
                    $q->where('nama_kelas', 'like', '%'.$registration->target_class.'%')
                        ->orWhere('tingkat_kelas', 'like', '%'.$registration->target_class.'%');
                })
                ->first();
            $defaultKelas = $matchedKelas ?: $kelasQuery->first() ?: Kelas::first();

            // 3. Tentukan Unit Pendidikan & Kode Unit untuk NIS
            $unit = $this->resolveEducationalUnit($registration, $defaultKelas);
            $unitCode = $unit ? $unit->kode_unit : 'MTS';

            // Bersihkan format kode unit: e.g. "SMK-OTO" -> "SMK", "MTs" -> "MTS"
            $cleanUnitCode = $this->formatUnitCodeForNis($unitCode);

            // Tentukan 2 digit tahun (misal: 2026/2027 -> "26", atau date('y'))
            $year2Digits = $this->resolveYear2Digits($registration);

            // Generate NIS otomatis berdasarkan tahun dan unit (misal: 26MTS001)
            $nis = $this->generateNis($year2Digits, $cleanUnitCode);

            // 4. Masukkan data ke tabel santris
            $jenisKelamin = $registration->gender === 'L' ? 'Laki-laki' : 'Perempuan';
            $educationalUnitId = $unit?->id ?? $defaultKelas?->educational_unit_id;

            $santri = $registration->santri_id ? Santri::find($registration->santri_id) : null;
            if (! $santri) {
                $santri = Santri::create([
                    'user_id' => $user->id,
                    'nis' => $nis,
                    'kelas_id' => $defaultKelas?->id,
                    'educational_unit_id' => $educationalUnitId,
                    'nama_lengkap' => $studentName,
                    'tanggal_lahir' => $registration->birth_date,
                    'jenis_kelamin' => $jenisKelamin,
                    'alamat' => $registration->residential_address,
                    'nama_wali' => $guardianName,
                    'no_hp_wali' => $registration->guardian_phone,
                    'tahun_masuk' => date('Y'),
                    'status_santri' => 'Aktif',
                    'foto' => $registration->student_photo,
                ]);
            } else {
                $santri->update([
                    'user_id' => $user->id,
                    'kelas_id' => $santri->kelas_id ?: $defaultKelas?->id,
                    'educational_unit_id' => $santri->educational_unit_id ?: $educationalUnitId,
                    'nama_lengkap' => $studentName,
                    'tanggal_lahir' => $registration->birth_date,
                    'jenis_kelamin' => $jenisKelamin,
                    'alamat' => $registration->residential_address,
                    'nama_wali' => $guardianName,
                    'no_hp_wali' => $registration->guardian_phone,
                    'status_santri' => 'Aktif',
                    'foto' => $registration->student_photo ?: $santri->foto,
                ]);
            }

            $registration->update(['santri_id' => $santri->id]);

            if ($defaultKelas) {
                RiwayatKelasSantri::firstOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'kelas_baru_id' => $defaultKelas->id,
                        'tahun_ajaran_id' => $defaultKelas->tahun_ajaran_id,
                    ],
                    [
                        'jenis_aksi' => 'Masuk PPDB',
                        'catatan' => 'Penempatan kelas awal penerimaan santri baru',
                        'created_by' => auth()->id(),
                    ]
                );
            }

            return [
                'santri' => $santri,
                'user' => $user,
                'nis' => $santri->nis,
                'email' => $user->email,
                'password' => $plainPassword ?? '(Akun Wali Santri Sudah Aktif)',
                'is_new_user' => $isNewUser,
            ];
        });
    }

    /**
     * Identifikasi EducationalUnit dari pendaftaran atau kelas default
     */
    protected function resolveEducationalUnit(SantriRegistration $registration, ?Kelas $defaultKelas): ?EducationalUnit
    {
        $target = strtolower(trim($registration->target_class ?? ''));

        if ($target !== '') {
            $units = EducationalUnit::all();
            foreach ($units as $u) {
                if (
                    str_contains($target, strtolower($u->kode_unit)) ||
                    str_contains($target, strtolower($u->jenjang ?? '')) ||
                    str_contains($target, strtolower($u->nama_unit ?? ''))
                ) {
                    return $u;
                }
            }
        }

        if ($defaultKelas && $defaultKelas->educational_unit_id) {
            return EducationalUnit::find($defaultKelas->educational_unit_id);
        }

        return EducationalUnit::first();
    }

    /**
     * Memformat kode unit untuk disematkan pada NIS (misal MTs -> MTS, MA -> MA, SMK-OTO -> SMK).
     */
    protected function formatUnitCodeForNis(?string $kodeUnit): string
    {
        if (! $kodeUnit) {
            return 'MTS';
        }

        $upper = strtoupper(trim($kodeUnit));

        if (str_contains($upper, 'MTS')) {
            return 'MTS';
        }
        if (str_contains($upper, 'MA')) {
            return 'MA';
        }
        if (str_contains($upper, 'SMK')) {
            return 'SMK';
        }

        $clean = preg_replace('/[^A-Z0-9]/', '', $upper);

        return $clean ?: 'MTS';
    }

    /**
     * Menentukan 2 digit tahun dari academic_year pendaftar atau tahun berjalan.
     */
    protected function resolveYear2Digits(SantriRegistration $registration): string
    {
        if ($registration->academic_year && preg_match('/\b(20\d{2})\b/', $registration->academic_year, $matches)) {
            return substr($matches[1], 2, 2);
        }

        return date('y');
    }

    /**
     * Menghasilkan NIS unik format [Tahun2Digit][KodeUnit][3DigitUrutan], contoh: 26MTS001.
     */
    protected function generateNis(string $year2Digits, string $unitCode): string
    {
        $prefix = $year2Digits.$unitCode;

        $existingNisList = Santri::where('nis', 'like', $prefix.'%')
            ->pluck('nis');

        $maxSeq = 0;
        foreach ($existingNisList as $item) {
            if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $item, $matches)) {
                $seqVal = (int) $matches[1];
                if ($seqVal > $maxSeq) {
                    $maxSeq = $seqVal;
                }
            }
        }

        $sequence = $maxSeq + 1;
        do {
            $nis = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Santri::where('nis', $nis)->exists());

        return $nis;
    }
}
