<?php

namespace Database\Seeders;

use App\Models\EducationalUnit;
use App\Models\Major;
use Illuminate\Database\Seeder;

class EducationalUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. MTs Nurul Khidmah
        $mts = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS'],
            [
                'nama_unit' => 'MTs Nurul Khidmah',
                'jenjang' => 'MTs',
                'is_active' => true,
            ]
        );

        // 2. MA Nurul Khidmah
        $ma = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MA'],
            [
                'nama_unit' => 'MA Nurul Khidmah',
                'jenjang' => 'MA',
                'is_active' => true,
            ]
        );

        // 3. SMK Otomotif Nurul Khidmah
        $smkOto = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'SMK-OTO'],
            [
                'nama_unit' => 'SMK Otomotif Nurul Khidmah',
                'jenjang' => 'SMK',
                'is_active' => true,
            ]
        );

        // Jurusan Teknik Otomotif
        Major::firstOrCreate(
            ['educational_unit_id' => $smkOto->id, 'kode_jurusan' => 'TO'],
            ['nama_jurusan' => 'Teknik Otomotif']
        );

        // 4. SMK DKV Nurul Khidmah
        $smkDkv = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'SMK-DKV'],
            [
                'nama_unit' => 'SMK DKV Nurul Khidmah',
                'jenjang' => 'SMK',
                'is_active' => true,
            ]
        );

        // Jurusan DKV
        Major::firstOrCreate(
            ['educational_unit_id' => $smkDkv->id, 'kode_jurusan' => 'DKV'],
            ['nama_jurusan' => 'Desain Komunikasi Visual (DKV)']
        );
    }
}
