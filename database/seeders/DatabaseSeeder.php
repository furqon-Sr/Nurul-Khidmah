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
        User::firstOrCreate(
            ['email' => 'super@nurulkhidmah.org'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@nurulkhidmah.org'],
            [
                'name' => 'Bapak Mufti (Admin & TU)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'kepala@nurulkhidmah.org'],
            [
                'name' => 'Drs. H. Ahmad Masrur, M.Pd.I. (Kepala Madrasah)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KEPALA_SEKOLAH,
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
