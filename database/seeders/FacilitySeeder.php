<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus fasilitas lama agar bersih dan sesuai dengan daftar baru
        Facility::truncate();

        $data = [
            [
                'title' => "Masjid Jami' Nurul Khidmah",
                'description' => "Pusat ibadah dan kajian utama santri.",
                'icon_or_image_path' => "facilities/masjid.png",
            ],
            [
                'title' => "Gedung Diniyyah",
                'description' => "Ruang kelas yang representatif untuk kegiatan belajar mengajar.",
                'icon_or_image_path' => "facilities/gedung_diniyyah.png",
            ],
            [
                'title' => "Asrama Santri",
                'description' => "Hunian santri yang nyaman dan terintegrasi.",
                'icon_or_image_path' => "facilities/asrama_santri.png",
            ],
            [
                'title' => "Area Olahraga Terpadu",
                'description' => "Sarana pengembangan fisik dan kesehatan santri.",
                'icon_or_image_path' => "facilities/area_olahraga.png",
            ],
            [
                'title' => "Perpustakaan & Ruang Baca",
                'description' => "Wadah literasi dan pengembangan wawasan santri.",
                'icon_or_image_path' => "facilities/perpustakaan.png",
            ],
        ];

        foreach ($data as $item) {
            Facility::create($item);
        }
    }
}
