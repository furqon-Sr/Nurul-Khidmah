<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus berita lama agar bersih dan sesuai dengan daftar baru
        News::truncate();

        $data = [
            [
                'title' => 'Refleksi Keilmuan: Haul Akbar Mengenang Jasa Para Guru',
                'content' => '<p>Pondok Pesantren Nurul Khidmah menggelar Haul Akbar sebagai bentuk penghormatan atas jasa-jasa almarhum para pendiri dan guru. Kegiatan ini menjadi momen penguatan spiritual bagi seluruh santri dan masyarakat sekitar.</p>',
                'main_image_path' => 'news/haul_akbar.png',
                'category' => 'Kegiatan',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Rutinitas Manaqib: Mempererat Ukhuwah di Lamongan & Surabaya',
                'content' => '<p>Kegiatan Manaqib rutin yang dilaksanakan setiap bulan di Lamongan dan Surabaya menjadi sarana pengikat tali persaudaraan. Selain doa bersama, kegiatan ini menjadi ajang koordinasi alumni dan wali santri.</p>',
                'main_image_path' => 'news/manaqib.png',
                'category' => 'Kegiatan',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Peduli Sesama: Santunan Rutin untuk Anak Yatim',
                'content' => '<p>Yayasan Al Machmudi Bantani kembali menyalurkan santunan bagi anak yatim di sekitar lingkungan pesantren. Ini adalah bentuk komitmen pesantren dalam menjalankan fungsi pemberdayaan sosial.</p>',
                'main_image_path' => 'news/charity.png',
                'category' => 'Kegiatan',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Progres Pembangunan Gedung Diniyyah Baru',
                'content' => '<p>Pembangunan fasilitas gedung diniyyah tahap 2025 terus berjalan. Fasilitas ini diharapkan mampu meningkatkan kualitas pelayanan pendidikan bagi santri putra dan putri.</p>',
                'main_image_path' => 'news/construction.png',
                'category' => 'Pengumuman',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Pelatihan Kepemimpinan Santri: Menyiapkan Kader Umat',
                'content' => '<p>Pesantren mengadakan pelatihan kepemimpinan untuk melatih santri dalam berorganisasi dan mengelola kegiatan kemasyarakatan dengan manajemen yang lebih baik.</p>',
                'main_image_path' => 'news/leadership.png',
                'category' => 'Kegiatan',
                'published_at' => now(),
            ],
        ];

        foreach ($data as $item) {
            $item['slug'] = Str::slug($item['title']);
            News::create($item);
        }
    }
}
