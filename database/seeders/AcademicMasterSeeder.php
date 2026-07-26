<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Academic Years
        $taActive = TahunAjaran::create([
            'nama_tahun_ajaran' => '2025/2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2025-07-15',
            'tanggal_selesai' => '2025-12-20',
            'status_aktif' => true,
            'keterangan' => 'Tahun Ajaran Aktif 2025/2026 Semester Ganjil',
        ]);

        TahunAjaran::create([
            'nama_tahun_ajaran' => '2025/2026',
            'semester' => 'Genap',
            'tanggal_mulai' => '2026-01-05',
            'tanggal_selesai' => '2026-06-25',
            'status_aktif' => false,
            'keterangan' => 'Tahun Ajaran 2025/2026 Semester Genap',
        ]);

        // 2. Create Subjects (Mata Pelajaran)
        $subjects = [
            ['kode' => 'MP-001', 'nama' => 'Fiqih Ibadaat', 'ket' => 'Pembahasan tata cara ibadah sesuai madzhab Syafi\'i'],
            ['kode' => 'MP-002', 'nama' => 'Aqidah Akhlaq', 'ket' => 'Penanaman tauhid dan akhlaqul karimah'],
            ['kode' => 'MP-003', 'nama' => 'Al-Qur\'an Hadits', 'ket' => 'Tahfizh, tajwid, dan pemahaman hadits-hadits pilihan'],
            ['kode' => 'MP-004', 'nama' => 'Bahasa Arab', 'ket' => 'Penguasaan mufradat, hiwar, dan qira\'ah'],
            ['kode' => 'MP-005', 'nama' => 'Nahwu (Matan Al-Jurumiyah)', 'ket' => 'Kaidah tata bahasa Arab tingkat dasar'],
            ['kode' => 'MP-006', 'nama' => 'Shorof (Al-Amtsilah At-Tashrifiyah)', 'ket' => 'Perubahan bentuk kata bahasa Arab'],
            ['kode' => 'MP-007', 'nama' => 'Tarikh Islam / Sejarah', 'ket' => 'Sejarah Nabi Muhammad SAW dan khulafaur rasyidin'],
        ];

        foreach ($subjects as $s) {
            MataPelajaran::firstOrCreate(
                ['kode_mapel' => $s['kode']],
                ['nama_mapel' => $s['nama'], 'keterangan' => $s['ket']]
            );
        }

        // 3. Create Users & Teachers (Pengajar)
        // User Pimpinan
        User::firstOrCreate(
            ['email' => 'pimpinan@nurulkhidmah.org'],
            [
                'name' => 'K.H. Ahmad Muzakki, M.Ag.',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PIMPINAN,
            ]
        );

        // User Pengurus Akademik
        User::firstOrCreate(
            ['email' => 'akademik@nurulkhidmah.org'],
            [
                'name' => 'Ustaz Abdullah, S.Pd.I',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENGURUS_AKADEMIK,
            ]
        );

        // Ustaz 1 (Pengajar & Wali Kelas 7A)
        $userUstaz1 = User::firstOrCreate(
            ['email' => 'ustaz.faiz@nurulkhidmah.org'],
            [
                'name' => 'Ustaz Muhammad Faiz, S.H.I',
                'password' => Hash::make('password'),
                'role' => User::ROLE_TENAGA_PENGAJAR,
            ]
        );

        $pengajar1 = Pengajar::firstOrCreate(
            ['nomor_identitas' => 'UST-2024-001'],
            [
                'user_id' => $userUstaz1->id,
                'nama_lengkap' => 'Ustaz Muhammad Faiz, S.H.I',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Dsn. Lopang, Kembangbahu, Lamongan',
                'nomor_telepon' => '081234567891',
                'status_pengajar' => 'Aktif',
            ]
        );

        // Ustazah 2 (Pengajar & Wali Kelas 8A)
        $userUstaz2 = User::firstOrCreate(
            ['email' => 'ustazah.nisa@nurulkhidmah.org'],
            [
                'name' => 'Ustazah Khairunnisa, S.Pd.',
                'password' => Hash::make('password'),
                'role' => User::ROLE_TENAGA_PENGAJAR,
            ]
        );

        $pengajar2 = Pengajar::firstOrCreate(
            ['nomor_identitas' => 'UST-2024-002'],
            [
                'user_id' => $userUstaz2->id,
                'nama_lengkap' => 'Ustazah Khairunnisa, S.Pd.',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Rayun, Lamongan',
                'nomor_telepon' => '081234567892',
                'status_pengajar' => 'Aktif',
            ]
        );

        // 4. Create Classes (Kelasses)
        $kelas7A = Kelas::firstOrCreate(
            ['kode_kelas' => 'KLS-7A'],
            [
                'nama_kelas' => 'Kelas 7 Ula A',
                'tingkat_kelas' => '7 Ula',
                'wali_kelas_id' => $pengajar1->id,
                'tahun_ajaran_id' => $taActive->id,
                'keterangan' => 'Kelas santri baru tingkat Ula A',
            ]
        );

        $kelas8A = Kelas::firstOrCreate(
            ['kode_kelas' => 'KLS-8A'],
            [
                'nama_kelas' => 'Kelas 8 Wustho A',
                'tingkat_kelas' => '8 Wustho',
                'wali_kelas_id' => $pengajar2->id,
                'tahun_ajaran_id' => $taActive->id,
                'keterangan' => 'Kelas santri tingkat Wustho A',
            ]
        );

        // 5. Create Santri Users & Santri Records
        $santriData = [
            [
                'nis' => 'SAN-2025-001',
                'email' => 'santri.badrus@nurulkhidmah.org',
                'nama' => 'Badrus Sholeh',
                'jk' => 'Laki-laki',
                'tmpt' => 'Lamongan',
                'tgl' => '2011-04-12',
                'wali' => 'H. Ahmad Subandi',
                'kelas_id' => $kelas7A->id,
            ],
            [
                'nis' => 'SAN-2025-002',
                'email' => 'santri.fatimah@nurulkhidmah.org',
                'nama' => 'Fatimah Az-Zahra',
                'jk' => 'Perempuan',
                'tmpt' => 'Surabaya',
                'tgl' => '2011-09-25',
                'wali' => 'Drs. Herman Prasetyo',
                'kelas_id' => $kelas7A->id,
            ],
            [
                'nis' => 'SAN-2025-003',
                'email' => 'santri.farhan@nurulkhidmah.org',
                'nama' => 'Muhammad Farhan',
                'jk' => 'Laki-laki',
                'tmpt' => 'Gresik',
                'tgl' => '2010-02-18',
                'wali' => 'Hj. Siti Rahmah',
                'kelas_id' => $kelas8A->id,
            ],
        ];

        foreach ($santriData as $s) {
            $userSantri = User::firstOrCreate(
                ['email' => $s['email']],
                [
                    'name' => $s['nama'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_SANTRI,
                ]
            );

            Santri::firstOrCreate(
                ['nis' => $s['nis']],
                [
                    'user_id' => $userSantri->id,
                    'kelas_id' => $s['kelas_id'],
                    'nama_lengkap' => $s['nama'],
                    'tempat_lahir' => $s['tmpt'],
                    'tanggal_lahir' => $s['tgl'],
                    'jenis_kelamin' => $s['jk'],
                    'alamat' => 'Pondok Pesantren Nurul Khidmah, Kembangbahu, Lamongan',
                    'nama_wali' => $s['wali'],
                    'tahun_masuk' => '2025',
                    'status_santri' => 'Aktif',
                ]
            );
        }

        // 6. Create Initial Schedules
        $mapelFiqih = MataPelajaran::where('kode_mapel', 'MP-001')->first();
        $mapelAqidah = MataPelajaran::where('kode_mapel', 'MP-002')->first();
        $mapelAlquran = MataPelajaran::where('kode_mapel', 'MP-003')->first();

        if ($mapelFiqih && $mapelAqidah && $mapelAlquran) {
            \App\Models\JadwalPelajaran::firstOrCreate(
                [
                    'tahun_ajaran_id' => $taActive->id,
                    'kelas_id' => $kelas7A->id,
                    'hari' => 'Senin',
                    'waktu_mulai' => '07:30',
                ],
                [
                    'mata_pelajaran_id' => $mapelFiqih->id,
                    'pengajar_id' => $pengajar1->id,
                    'waktu_selesai' => '09:00',
                    'ruang' => 'Ruang Kelas 7A',
                    'keterangan' => 'KBM Fiqih Dasar',
                ]
            );

            \App\Models\JadwalPelajaran::firstOrCreate(
                [
                    'tahun_ajaran_id' => $taActive->id,
                    'kelas_id' => $kelas7A->id,
                    'hari' => 'Senin',
                    'waktu_mulai' => '09:15',
                ],
                [
                    'mata_pelajaran_id' => $mapelAqidah->id,
                    'pengajar_id' => $pengajar2->id,
                    'waktu_selesai' => '10:45',
                    'ruang' => 'Ruang Kelas 7A',
                    'keterangan' => 'KBM Aqidah Akhlaq',
                ]
            );

            \App\Models\JadwalPelajaran::firstOrCreate(
                [
                    'tahun_ajaran_id' => $taActive->id,
                    'kelas_id' => $kelas8A->id,
                    'hari' => 'Selasa',
                    'waktu_mulai' => '07:30',
                ],
                [
                    'mata_pelajaran_id' => $mapelAlquran->id,
                    'pengajar_id' => $pengajar1->id,
                    'waktu_selesai' => '09:00',
                    'ruang' => 'Ruang Kelas 8A',
                    'keterangan' => 'KBM Al-Qur\'an Hadits',
                ]
            );
        }
    }
}
