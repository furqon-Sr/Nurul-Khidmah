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
        User::create([
            'name' => 'Super Administrator',
            'email' => 'super@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->call(FacilitySeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(AcademicMasterSeeder::class);
        $this->call(SantriRegistrationSeeder::class);
    }
}
