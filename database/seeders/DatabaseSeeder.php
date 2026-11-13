<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Super Admin (Penyusun Sistem)
        User::updateOrCreate(
            ['email' => 'super@nurulkhidmah.org'],
            [
                'name' => 'Fahruri Anafi',
                'password' => $defaultPassword,
                'role' => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // 2. Pimpinan Yayasan & Pengasuh Pondok (2 Tokoh Kunci URS)
        User::updateOrCreate(
            ['email' => 'pimpinan@nurulkhidmah.org'],
            [
                'name' => 'KH. Imam Machmudi',
                'password' => $defaultPassword,
                'role' => User::ROLE_PIMPINAN,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'ketua@nurulkhidmah.org'],
            [
                'name' => 'Bapak Mas\'ud, S.Pd.',
                'password' => $defaultPassword,
                'role' => User::ROLE_PIMPINAN,
                'email_verified_at' => now(),
            ]
        );

        // 3. Kepala Sekolah / Kepala Madrasah (Otoritas Akademik & Persetujuan Rapor)
        User::updateOrCreate(
            ['email' => 'kepala@nurulkhidmah.org'],
            [
                'name' => 'Ibu Uswatun Hasanah SP, S.Pd.I.',
                'password' => $defaultPassword,
                'role' => User::ROLE_KEPALA_SEKOLAH,
                'email_verified_at' => now(),
            ]
        );

        // 4. Staf Tata Usaha / Administrasi Akademik (Operator Utama SIAKAD & PPDB)
        User::updateOrCreate(
            ['email' => 'admin@nurulkhidmah.org'],
            [
                'name' => 'Bapak Mufti',
                'password' => $defaultPassword,
                'role' => User::ROLE_PENGURUS_AKADEMIK,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'akademik@nurulkhidmah.org'],
            [
                'name' => 'Bapak Mufti (Staf TU)',
                'password' => $defaultPassword,
                'role' => User::ROLE_PENGURUS_AKADEMIK,
                'email_verified_at' => now(),
            ]
        );

        $this->call(FacilitySeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(EducationalUnitSeeder::class);
        $this->call(AcademicMasterSeeder::class);
        $this->call(SantriRegistrationSeeder::class);
    }
}
