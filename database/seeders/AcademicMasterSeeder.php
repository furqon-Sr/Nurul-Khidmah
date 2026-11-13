<?php

namespace Database\Seeders;

use App\Models\EducationalUnit;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajar;
use App\Models\RiwayatKelasSantri;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicMasterSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Tahun Ajaran (Idempotent)
        $taActive = TahunAjaran::updateOrCreate(
            [
                'nama_tahun_ajaran' => '2025/2026',
                'semester' => 'Ganjil',
            ],
            [
                'tanggal_mulai' => '2025-07-15',
                'tanggal_selesai' => '2025-12-20',
                'status_aktif' => true,
                'keterangan' => 'Tahun Ajaran Aktif 2025/2026 Semester Ganjil',
            ]
        );

        TahunAjaran::updateOrCreate(
            [
                'nama_tahun_ajaran' => '2025/2026',
                'semester' => 'Genap',
            ],
            [
                'tanggal_mulai' => '2026-01-05',
                'tanggal_selesai' => '2026-06-25',
                'status_aktif' => false,
                'keterangan' => 'Tahun Ajaran 2025/2026 Semester Genap',
            ]
        );

        // 2. Mata Pelajaran (Kurikulum Pondok & Madrasah)
        $subjects = [
            ['kode' => 'MP-001', 'nama' => 'Fiqih Ibadaat', 'ket' => 'Pembahasan tata cara ibadah sesuai madzhab Syafi\'i'],
            ['kode' => 'MP-002', 'nama' => 'Aqidah Akhlaq', 'ket' => 'Penanaman tauhid dan akhlaqul karimah'],
            ['kode' => 'MP-003', 'nama' => 'Al-Qur\'an Hadits', 'ket' => 'Tahfizh, tajwid, dan pemahaman hadits-hadits pilihan'],
            ['kode' => 'MP-004', 'nama' => 'Bahasa Arab', 'ket' => 'Penguasaan mufradat, hiwar, dan qira\'ah'],
            ['kode' => 'MP-005', 'nama' => 'Nahwu (Matan Al-Jurumiyah)', 'ket' => 'Kaidah tata bahasa Arab tingkat dasar'],
            ['kode' => 'MP-006', 'nama' => 'Shorof (Al-Amtsilah At-Tashrifiyah)', 'ket' => 'Perubahan bentuk kata bahasa Arab'],
            ['kode' => 'MP-007', 'nama' => 'Tarikh Islam / Sejarah', 'ket' => 'Sejarah Nabi Muhammad SAW dan khulafaur rasyidin'],
        ];

        $mapels = [];
        foreach ($subjects as $s) {
            $mapels[$s['kode']] = MataPelajaran::updateOrCreate(
                ['kode_mapel' => $s['kode']],
                ['nama_mapel' => $s['nama'], 'keterangan' => $s['ket']]
            );
        }

        // 3. Sinkronisasi Akun Role Utama Sesuai Dokumen URS Tervalidasi (14 Responden Internal + Penyusun)
        User::updateOrCreate(
            ['email' => 'super@nurulkhidmah.org'],
            [
                'name' => 'Fahruri Anafi',
                'password' => $defaultPassword,
                'role' => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

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

        $kepalaUser = User::updateOrCreate(
            ['email' => 'kepala@nurulkhidmah.org'],
            [
                'name' => 'Ibu Uswatun Hasanah SP, S.Pd.I.',
                'password' => $defaultPassword,
                'role' => User::ROLE_KEPALA_SEKOLAH,
                'email_verified_at' => now(),
            ]
        );

        $adminTuUser = User::updateOrCreate(
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

        // Hubungkan Kepala Madrasah ke Unit Pendidikan
        EducationalUnit::query()->update(['headmaster_id' => $kepalaUser->id]);
        $unitMts = EducationalUnit::where('kode_unit', 'MTS')->first() ?? EducationalUnit::first();

        // 4. Migrasi Akun Guru Lama (Jika Ada) & Buat 9 Akun Tenaga Pengajar Sesuai Responden URS
        $legacyTeacherMigrations = [
            'ustaz.faiz@nurulkhidmah.org' => 'ustazah.hesti@nurulkhidmah.org',
            'ustazah.nisa@nurulkhidmah.org' => 'ustazah.annisa@nurulkhidmah.org',
        ];
        foreach ($legacyTeacherMigrations as $oldEmail => $newEmail) {
            $oldUser = User::where('email', $oldEmail)->first();
            if ($oldUser && ! User::where('email', $newEmail)->exists()) {
                $oldUser->update(['email' => $newEmail]);
            } elseif ($oldUser) {
                $oldUser->delete();
            }
        }

        $teachersData = [
            [
                'niy' => 'UST-2024-001',
                'email' => 'ustazah.hesti@nurulkhidmah.org',
                'nama' => 'Ustazah Hesti Eka Nur Dianti',
                'jk' => 'Perempuan',
                'telp' => '081234567801',
                'alamat' => 'Dsn. Blawi, Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-002',
                'email' => 'ustazah.annisa@nurulkhidmah.org',
                'nama' => 'Ustazah Annisa\' Mutma\'inah',
                'jk' => 'Perempuan',
                'telp' => '081234567802',
                'alamat' => 'Dsn. Blawi, Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-003',
                'email' => 'ustaz.dimas@nurulkhidmah.org',
                'nama' => 'Ustaz Dimas Arfian',
                'jk' => 'Laki-laki',
                'telp' => '081234567803',
                'alamat' => 'Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-004',
                'email' => 'ustaz.nasikhul@nurulkhidmah.org',
                'nama' => 'Ustaz Moh. Nasikhul Ashar Fanani',
                'jk' => 'Laki-laki',
                'telp' => '081234567804',
                'alamat' => 'Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-005',
                'email' => 'ustazah.lani@nurulkhidmah.org',
                'nama' => 'Ustazah Lani',
                'jk' => 'Perempuan',
                'telp' => '081234567805',
                'alamat' => 'Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-006',
                'email' => 'ustaz.khoirul@nurulkhidmah.org',
                'nama' => 'Ustaz Khoirul',
                'jk' => 'Laki-laki',
                'telp' => '081234567806',
                'alamat' => 'Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-007',
                'email' => 'ustaz.aji@nurulkhidmah.org',
                'nama' => 'Ustaz Aji Prawiro',
                'jk' => 'Laki-laki',
                'telp' => '081234567807',
                'alamat' => 'Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-008',
                'email' => 'ustaz.shobib@nurulkhidmah.org',
                'nama' => 'Ustaz Ahmad Ash Shobib',
                'jk' => 'Laki-laki',
                'telp' => '081234567808',
                'alamat' => 'Karangbinangun, Lamongan',
            ],
            [
                'niy' => 'UST-2024-009',
                'email' => 'ustazah.fadian@nurulkhidmah.org',
                'nama' => 'Ustazah Fadian Miftah Utami',
                'jk' => 'Perempuan',
                'telp' => '081234567809',
                'alamat' => 'Karangbinangun, Lamongan',
            ],
        ];

        $pengajars = [];
        foreach ($teachersData as $t) {
            $userGuru = User::updateOrCreate(
                ['email' => $t['email']],
                [
                    'name' => $t['nama'],
                    'password' => $defaultPassword,
                    'role' => User::ROLE_TENAGA_PENGAJAR,
                    'email_verified_at' => now(),
                ]
            );

            $pengajars[$t['niy']] = Pengajar::updateOrCreate(
                ['nomor_identitas' => $t['niy']],
                [
                    'user_id' => $userGuru->id,
                    'nama_lengkap' => $t['nama'],
                    'jenis_kelamin' => $t['jk'],
                    'alamat' => $t['alamat'],
                    'nomor_telepon' => $t['telp'],
                    'status_pengajar' => 'Aktif',
                ]
            );
        }

        // 5. Kelas & Wali Kelas
        $kelas7A = Kelas::updateOrCreate(
            ['kode_kelas' => 'KLS-7A', 'tahun_ajaran_id' => $taActive->id],
            [
                'nama_kelas' => 'Kelas 7 Ula A',
                'tingkat_kelas' => '7 Ula',
                'wali_kelas_id' => $pengajars['UST-2024-001']->id,
                'educational_unit_id' => $unitMts?->id,
                'keterangan' => 'Kelas santri tingkat 7 Ula A',
            ]
        );

        $kelas7B = Kelas::updateOrCreate(
            ['kode_kelas' => 'KLS-7B', 'tahun_ajaran_id' => $taActive->id],
            [
                'nama_kelas' => 'Kelas 7 Ula B',
                'tingkat_kelas' => '7 Ula',
                'wali_kelas_id' => $pengajars['UST-2024-002']->id,
                'educational_unit_id' => $unitMts?->id,
                'keterangan' => 'Kelas santri tingkat 7 Ula B',
            ]
        );

        $kelas8A = Kelas::updateOrCreate(
            ['kode_kelas' => 'KLS-8A', 'tahun_ajaran_id' => $taActive->id],
            [
                'nama_kelas' => 'Kelas 8 Wustho A',
                'tingkat_kelas' => '8 Wustho',
                'wali_kelas_id' => $pengajars['UST-2024-003']->id,
                'educational_unit_id' => $unitMts?->id,
                'keterangan' => 'Kelas santri tingkat 8 Wustho A',
            ]
        );

        $kelas9A = Kelas::updateOrCreate(
            ['kode_kelas' => 'KLS-9A', 'tahun_ajaran_id' => $taActive->id],
            [
                'nama_kelas' => 'Kelas 9 Ulya A',
                'tingkat_kelas' => '9 Ulya',
                'wali_kelas_id' => $pengajars['UST-2024-004']->id,
                'educational_unit_id' => $unitMts?->id,
                'keterangan' => 'Kelas santri tingkat 9 Ulya A',
            ]
        );

        // 6. Bersihkan Data Dummy Santri Lama (Jika Ada) & Tambahkan 49 Data Siswa Sesuai Berkas Terlampir
        $legacyDummyNis = ['SAN-2025-001', 'SAN-2025-002', 'SAN-2025-003'];
        $legacyDummyEmails = [
            'santri.badrus@nurulkhidmah.org',
            'santri.fatimah@nurulkhidmah.org',
            'santri.farhan@nurulkhidmah.org',
        ];
        Santri::withTrashed()->whereIn('nis', $legacyDummyNis)->forceDelete();
        User::whereIn('email', $legacyDummyEmails)->delete();

        $studentsFromCsv = [
            ['no' => 1, 'nama' => 'Ach. Azyan Uwais Al Qorny', 'nisn' => '3148118810', 'jk' => 'Laki-laki'],
            ['no' => 2, 'nama' => 'Ahmad Alfian Ramdani', 'nisn' => '0147404399', 'jk' => 'Laki-laki'],
            ['no' => 3, 'nama' => 'Ainun Faizzatul Ummah', 'nisn' => '3142363852', 'jk' => 'Perempuan'],
            ['no' => 4, 'nama' => 'Ainiyah Aqsho Arofah', 'nisn' => '0149972851', 'jk' => 'Perempuan'],
            ['no' => 5, 'nama' => 'Aisyah Wahyuningtias', 'nisn' => '0136788422', 'jk' => 'Perempuan'],
            ['no' => 6, 'nama' => 'Akhmad Danu Al Adha', 'nisn' => '3139222138', 'jk' => 'Laki-laki'],
            ['no' => 7, 'nama' => 'Alex Dwi Darmawan', 'nisn' => '0131812277', 'jk' => 'Laki-laki'],
            ['no' => 8, 'nama' => 'Angga Maulana Saputra', 'nisn' => '0148760671', 'jk' => 'Laki-laki'],
            ['no' => 9, 'nama' => 'Anggun Putri Syahroni', 'nisn' => '0138687810', 'jk' => 'Perempuan'],
            ['no' => 10, 'nama' => 'Arkhab Bassama', 'nisn' => '0134431790', 'jk' => 'Laki-laki'],
            ['no' => 11, 'nama' => 'Bimo Raya Saputro', 'nisn' => '0144966343', 'jk' => 'Laki-laki'],
            ['no' => 12, 'nama' => 'Bintang Aprilia Khotima Putri', 'nisn' => '3147114112', 'jk' => 'Perempuan'],
            ['no' => 13, 'nama' => 'Caka Tri Prasetya', 'nisn' => '0136459420', 'jk' => 'Laki-laki'],
            ['no' => 14, 'nama' => 'Dhamar Pamungkas', 'nisn' => '3135332341', 'jk' => 'Laki-laki'],
            ['no' => 15, 'nama' => 'Dzulham Raka Rahmadani', 'nisn' => '0144423523', 'jk' => 'Laki-laki'],
            ['no' => 16, 'nama' => 'Fadhil Radinka Erabbani', 'nisn' => '0137756555', 'jk' => 'Laki-laki'],
            ['no' => 17, 'nama' => 'Jawahirul Ibad', 'nisn' => '3133906255', 'jk' => 'Laki-laki'],
            ['no' => 18, 'nama' => 'Kiera Zuyin Azmi', 'nisn' => '3137680206', 'jk' => 'Perempuan'],
            ['no' => 19, 'nama' => 'Mohammad Nathiq', 'nisn' => '3149459625', 'jk' => 'Laki-laki'],
            ['no' => 20, 'nama' => 'Muhammad Alfan Nur Johani', 'nisn' => '0131957312', 'jk' => 'Laki-laki'],
            ['no' => 21, 'nama' => 'Muhammad Iqbalul Akbar', 'nisn' => '0149864758', 'jk' => 'Laki-laki'],
            ['no' => 22, 'nama' => 'Muhammad Syamsyu Dhuha', 'nisn' => '0148053362', 'jk' => 'Laki-laki'],
            ['no' => 23, 'nama' => 'Rafa Wahyu Ramadhan', 'nisn' => '3131052781', 'jk' => 'Laki-laki'],
            ['no' => 24, 'nama' => 'Akhmad Celo Febrizio', 'nisn' => '3132420019', 'jk' => 'Laki-laki'],
            ['no' => 25, 'nama' => 'Ainul Isti Karomah', 'nisn' => '3133516058', 'jk' => 'Perempuan'],
            ['no' => 26, 'nama' => 'Achmad Bactiar Jefry Ariansyah', 'nisn' => '0132773071', 'jk' => 'Laki-laki'],
            ['no' => 27, 'nama' => 'Adellia Hafiza Asfaraini', 'nisn' => '3130188965', 'jk' => 'Perempuan'],
            ['no' => 28, 'nama' => 'Aulia Arinindya Sabila', 'nisn' => '0112346716', 'jk' => 'Perempuan'],
            ['no' => 29, 'nama' => 'Ervan Prayuda Andika Rama Putra', 'nisn' => '3110640240', 'jk' => 'Laki-laki'],
            ['no' => 30, 'nama' => 'Ferix Setiawan', 'nisn' => '0128716612', 'jk' => 'Laki-laki'],
            ['no' => 31, 'nama' => 'Finsa Febriani', 'nisn' => '0135442097', 'jk' => 'Perempuan'],
            ['no' => 32, 'nama' => 'Ganesa Septia Herga Putra', 'nisn' => '0122012240', 'jk' => 'Laki-laki'],
            ['no' => 33, 'nama' => 'Imam Abiyyu Atha\'illah Alfatan', 'nisn' => '3131115384', 'jk' => 'Laki-laki'],
            ['no' => 34, 'nama' => 'Imam Bahy Atha\'illah Alfatan', 'nisn' => '3135494696', 'jk' => 'Laki-laki'],
            ['no' => 35, 'nama' => 'Levi Henang Fatoni', 'nisn' => '3136391145', 'jk' => 'Laki-laki'],
            ['no' => 36, 'nama' => 'Mar\'atus Sholikhah', 'nisn' => '3122095543', 'jk' => 'Perempuan'],
            ['no' => 37, 'nama' => 'Mochammad Nahari Habibi Rachman', 'nisn' => '0133690814', 'jk' => 'Laki-laki'],
            ['no' => 38, 'nama' => 'Mu\'amar Khadafi Jauhari', 'nisn' => '3120724614', 'jk' => 'Laki-laki'],
            ['no' => 39, 'nama' => 'Muhammad Furqon Ainur Zamani', 'nisn' => '3131662527', 'jk' => 'Laki-laki'],
            ['no' => 40, 'nama' => 'Nadya Alyssa Azzahra', 'nisn' => '0121043594', 'jk' => 'Perempuan'],
            ['no' => 41, 'nama' => 'Nazriel Irham Saputra', 'nisn' => '3139354306', 'jk' => 'Laki-laki'],
            ['no' => 42, 'nama' => 'Noor Fatimah Binti Maimun', 'nisn' => '3122846009', 'jk' => 'Perempuan'],
            ['no' => 43, 'nama' => 'Nur Maghfiroh', 'nisn' => '3138932782', 'jk' => 'Perempuan'],
            ['no' => 44, 'nama' => 'Zain Alif Addien', 'nisn' => '3128271288', 'jk' => 'Laki-laki'],
            ['no' => 45, 'nama' => 'Zakiatus Soleha', 'nisn' => '3129666427', 'jk' => 'Perempuan'],
            ['no' => 46, 'nama' => 'Qonita Federica', 'nisn' => '3110628067', 'jk' => 'Perempuan'],
            ['no' => 47, 'nama' => 'M. Iqbal Atoqillah', 'nisn' => '3136949550', 'jk' => 'Laki-laki'],
            ['no' => 48, 'nama' => 'Nabila Miftahuz Zahirah', 'nisn' => '3139265557', 'jk' => 'Perempuan'],
            ['no' => 49, 'nama' => 'Suvia Ananda Hidayati', 'nisn' => '3135935807', 'jk' => 'Perempuan'],
        ];

        foreach ($studentsFromCsv as $s) {
            $no = $s['no'];
            $nis = sprintf('2026%04d', $no);
            $email = sprintf('wali.%s@nurulkhidmah.org', $nis);

            $targetKelas = null;
            $targetUnitId = $unitMts?->id;
            $previousSchool = null;

            if ($no === 46) {
                // Qonita Federica: jenjang SMA di luar pesantren (hanya mukim/mondok saja)
                $targetKelas = null;
                $targetUnitId = null;
                $previousSchool = 'SMA (Sekolah di Luar — Mondok Saja)';
                $tahunMasuk = '2024';
            } elseif ($no <= 13) {
                $targetKelas = $kelas7A;
                $tahunMasuk = '2025';
            } elseif ($no <= 26) {
                $targetKelas = $kelas7B;
                $tahunMasuk = '2025';
            } else {
                // No 27-45 dan 47-49 masuk jenjang Kelas 8
                $targetKelas = $kelas8A;
                $tahunMasuk = '2024';
            }

            // Estimasi tahun lahir dari 3 digit awal NISN (contoh: 014 / 314 -> 2014)
            $birthYearDigits = (int) substr($s['nisn'], 1, 2);
            $birthYear = ($birthYearDigits >= 10 && $birthYearDigits <= 16) ? (2000 + $birthYearDigits) : 2013;
            $birthMonth = (($no - 1) % 12) + 1;
            $birthDay = (($no * 3) % 27) + 1;
            $tanggalLahir = sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay);

            $userWali = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Wali '.$s['nama'],
                    'password' => $defaultPassword,
                    'role' => User::ROLE_SANTRI,
                    'email_verified_at' => now(),
                ]
            );

            $santri = Santri::withTrashed()->updateOrCreate(
                ['nis' => $nis],
                [
                    'nisn' => $s['nisn'],
                    'user_id' => $userWali->id,
                    'educational_unit_id' => $targetUnitId,
                    'kelas_id' => $targetKelas?->id,
                    'nama_lengkap' => $s['nama'],
                    'tempat_lahir' => 'Lamongan',
                    'tanggal_lahir' => $tanggalLahir,
                    'jenis_kelamin' => $s['jk'],
                    'alamat' => 'Karangbinangun, Lamongan',
                    'previous_school' => $previousSchool,
                    'boarding_status' => 'Mukim',
                    'nama_wali' => 'Bapak/Ibu Wali '.$s['nama'],
                    'no_hp_wali' => sprintf('08133000%04d', $no),
                    'tahun_masuk' => $tahunMasuk,
                    'status_santri' => 'Aktif',
                    'deleted_at' => null,
                ]
            );

            if ($targetKelas) {
                RiwayatKelasSantri::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'tahun_ajaran_id' => $taActive->id,
                        'jenis_aksi' => 'pendaftaran_awal',
                    ],
                    [
                        'kelas_lama_id' => null,
                        'kelas_baru_id' => $targetKelas->id,
                        'catatan' => 'Penempatan kelas awal Tahun Ajaran '.$taActive->nama_tahun_ajaran,
                        'created_by' => $adminTuUser->id,
                    ]
                );
            } else {
                RiwayatKelasSantri::where('santri_id', $santri->id)
                    ->where('tahun_ajaran_id', $taActive->id)
                    ->where('jenis_aksi', 'pendaftaran_awal')
                    ->delete();
            }
        }

        // 7. Jadwal Pelajaran Terdistribusi pada 9 Tenaga Pengajar
        $schedules = [
            ['kelas' => $kelas7A, 'hari' => 'Senin', 'mulai' => '07:30', 'selesai' => '09:00', 'mapel' => 'MP-001', 'niy' => 'UST-2024-001', 'ruang' => 'Ruang Kelas 7A', 'ket' => 'KBM Fiqih Ibadaat'],
            ['kelas' => $kelas7A, 'hari' => 'Senin', 'mulai' => '09:15', 'selesai' => '10:45', 'mapel' => 'MP-002', 'niy' => 'UST-2024-005', 'ruang' => 'Ruang Kelas 7A', 'ket' => 'KBM Aqidah Akhlaq'],
            ['kelas' => $kelas7B, 'hari' => 'Senin', 'mulai' => '07:30', 'selesai' => '09:00', 'mapel' => 'MP-003', 'niy' => 'UST-2024-002', 'ruang' => 'Ruang Kelas 7B', 'ket' => 'KBM Al-Qur\'an Hadits'],
            ['kelas' => $kelas7B, 'hari' => 'Selasa', 'mulai' => '07:30', 'selesai' => '09:00', 'mapel' => 'MP-004', 'niy' => 'UST-2024-006', 'ruang' => 'Ruang Kelas 7B', 'ket' => 'KBM Bahasa Arab'],
            ['kelas' => $kelas8A, 'hari' => 'Selasa', 'mulai' => '07:30', 'selesai' => '09:00', 'mapel' => 'MP-005', 'niy' => 'UST-2024-003', 'ruang' => 'Ruang Kelas 8A', 'ket' => 'KBM Nahwu Al-Jurumiyah'],
            ['kelas' => $kelas8A, 'hari' => 'Rabu', 'mulai' => '07:30', 'selesai' => '09:00', 'mapel' => 'MP-006', 'niy' => 'UST-2024-007', 'ruang' => 'Ruang Kelas 8A', 'ket' => 'KBM Shorof'],
            ['kelas' => $kelas9A, 'hari' => 'Rabu', 'mulai' => '07:30', 'selesai' => '09:00', 'mapel' => 'MP-007', 'niy' => 'UST-2024-004', 'ruang' => 'Ruang Kelas 9A', 'ket' => 'KBM Tarikh Islam'],
            ['kelas' => $kelas9A, 'hari' => 'Kamis', 'mulai' => '07:30', 'selesai' => '09:00', 'mapel' => 'MP-001', 'niy' => 'UST-2024-008', 'ruang' => 'Ruang Kelas 9A', 'ket' => 'KBM Fiqih Lanjutan'],
            ['kelas' => $kelas7A, 'hari' => 'Kamis', 'mulai' => '09:15', 'selesai' => '10:45', 'mapel' => 'MP-004', 'niy' => 'UST-2024-009', 'ruang' => 'Ruang Kelas 7A', 'ket' => 'KBM Bahasa Arab Dasar'],
        ];

        foreach ($schedules as $sc) {
            if (isset($mapels[$sc['mapel']], $pengajars[$sc['niy']])) {
                JadwalPelajaran::updateOrCreate(
                    [
                        'tahun_ajaran_id' => $taActive->id,
                        'kelas_id' => $sc['kelas']->id,
                        'hari' => $sc['hari'],
                        'waktu_mulai' => $sc['mulai'],
                    ],
                    [
                        'mata_pelajaran_id' => $mapels[$sc['mapel']]->id,
                        'pengajar_id' => $pengajars[$sc['niy']]->id,
                        'waktu_selesai' => $sc['selesai'],
                        'ruang' => $sc['ruang'],
                        'keterangan' => $sc['ket'],
                    ]
                );
            }
        }
    }
}
