<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\EducationProgram;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\RiwayatKelasSantri;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UrsValidationFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function samplePhotoDataUrl(): string
    {
        $img = imagecreatetruecolor(30, 40);
        ob_start();
        imagepng($img);
        $raw = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($raw);
    }

    public function test_fr01_and_fr02_ppdb_store_lamongan_without_guardian_relationship_and_idempotent_conversion(): void
    {
        Storage::fake('public');

        TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        EducationProgram::create([
            'name' => 'Madrasah Diniyah Ula',
            'slug' => 'madin-ula',
            'description' => 'Program dasar kitab kuning',
            'is_active' => true,
            'is_registration_open' => true,
            'order' => 1,
        ]);

        // Pastikan halaman pendaftaran publik tidak menampilkan opsi Hubungan Keluarga maupun Cabang Surabaya
        $this->get(route('registration'))
            ->assertOk()
            ->assertDontSee('Hubungan Keluarga')
            ->assertDontSee('Surabaya');

        $response = $this->post(route('registration.store'), [
            'registration_number' => '881234',
            'student_photo_base64' => $this->samplePhotoDataUrl(),
            'student_name' => 'Ahmad Zaini Dahlan',
            'gender' => 'L',
            'birth_date' => '2013-05-14',
            'target_class' => 'Madrasah Diniyah Ula',
            'student_email' => 'wali.zaini@example.com',
            'student_phone' => '081234567890',
            'residential_address' => 'Dusun Wringinanom, Desa Lopang, Kembangbahu, Lamongan',
            'father_name' => 'H. Dahlan',
            'mother_name' => 'Hj. Aminah',
        ]);

        $response->assertRedirect();
        $registration = SantriRegistration::where('registration_number', '881234')->firstOrFail();
        $this->assertSame('Lamongan', $registration->branch);
        $this->assertNull($registration->guardian_relationship);
        $this->assertSame('2026/2027', $registration->academic_year);
        $this->assertNotEmpty($registration->student_photo);
        Storage::disk('public')->assertExists($registration->student_photo);

        $pengurusAkademik = User::create([
            'name' => 'Staf TU',
            'email' => 'tu@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        // Setujui pertama kali -> konversi ke Santri + Akun Wali (role: santri)
        $this->actingAs($pengurusAkademik)
            ->put(route('admin.registrations.update-status', $registration), ['status' => 'accepted'])
            ->assertRedirect();

        $registration->refresh();
        $this->assertNotNull($registration->santri_id);

        $santri = Santri::findOrFail($registration->santri_id);
        $this->assertSame('Laki-laki', $santri->jenis_kelamin);
        $this->assertMatchesRegularExpression('/^([0-9]{2}[A-Z0-9]+[0-9]{3,}|\d{8})$/', $santri->nis);
        $this->assertSame('081234567890', $santri->no_hp_wali);
        $this->assertNotNull($santri->user);
        $this->assertSame(User::ROLE_SANTRI, $santri->user->role);

        // Setujui kedua kali -> idempoten (tidak menduplikasi Santri maupun User)
        $this->actingAs($pengurusAkademik)
            ->put(route('admin.registrations.update-status', $registration), ['status' => 'accepted'])
            ->assertRedirect();

        $this->assertSame(1, Santri::count());
        $this->assertSame(1, User::where('role', User::ROLE_SANTRI)->count());
    }

    public function test_fr06_kode_kelas_unique_per_tahun_ajaran(): void
    {
        $admin = User::create([
            'name' => 'Admin Akademik',
            'email' => 'akademik@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        $ta1 = TahunAjaran::create(['nama_tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil', 'status_aktif' => false]);
        $ta2 = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);

        // Simpan KLS-7A di TA 1 -> sukses
        $this->actingAs($admin)->post(route('admin.classes.store'), [
            'kode_kelas' => 'KLS-7A',
            'nama_kelas' => 'Kelas 7 Ula A',
            'tingkat_kelas' => '7 Ula',
            'tahun_ajaran_id' => $ta1->id,
        ])->assertRedirect(route('admin.classes.index'));

        // Simpan KLS-7A lagi di TA 1 -> ditolak (duplikat dalam tahun ajaran sama)
        $this->actingAs($admin)->post(route('admin.classes.store'), [
            'kode_kelas' => 'KLS-7A',
            'nama_kelas' => 'Kelas 7 Ula A Duplikat',
            'tingkat_kelas' => '7 Ula',
            'tahun_ajaran_id' => $ta1->id,
        ])->assertSessionHasErrors('kode_kelas');

        // Simpan KLS-7A di TA 2 -> diizinkan sesuai FR-06
        $this->actingAs($admin)->post(route('admin.classes.store'), [
            'kode_kelas' => 'KLS-7A',
            'nama_kelas' => 'Kelas 7 Ula A Baru',
            'tingkat_kelas' => '7 Ula',
            'tahun_ajaran_id' => $ta2->id,
        ])->assertRedirect(route('admin.classes.index'));

        $this->assertSame(2, Kelas::where('kode_kelas', 'KLS-7A')->count());
    }

    public function test_fr08_schedule_anti_conflict_checks_teacher_class_and_room(): void
    {
        $pengurusAkademik = User::create([
            'name' => 'Pengurus Jadwal',
            'email' => 'jadwal@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        $mapel1 = MataPelajaran::create(['kode_mapel' => 'MP-01', 'nama_mapel' => 'Fiqih']);
        $mapel2 = MataPelajaran::create(['kode_mapel' => 'MP-02', 'nama_mapel' => 'Nahwu']);

        $u1 = User::create(['name' => 'Ustaz A', 'email' => 'u1@test.com', 'password' => 'secret123', 'role' => User::ROLE_TENAGA_PENGAJAR]);
        $u2 = User::create(['name' => 'Ustaz B', 'email' => 'u2@test.com', 'password' => 'secret123', 'role' => User::ROLE_TENAGA_PENGAJAR]);
        $p1 = Pengajar::create(['user_id' => $u1->id, 'nomor_identitas' => 'NIP-1', 'nama_lengkap' => 'Ustaz A', 'jenis_kelamin' => 'Laki-laki', 'status_pengajar' => 'Aktif']);
        $p2 = Pengajar::create(['user_id' => $u2->id, 'nomor_identitas' => 'NIP-2', 'nama_lengkap' => 'Ustaz B', 'jenis_kelamin' => 'Laki-laki', 'status_pengajar' => 'Aktif']);

        $k1 = Kelas::create(['kode_kelas' => '7A', 'nama_kelas' => '7A', 'tingkat_kelas' => '7', 'tahun_ajaran_id' => $ta->id]);
        $k2 = Kelas::create(['kode_kelas' => '8A', 'nama_kelas' => '8A', 'tingkat_kelas' => '8', 'tahun_ajaran_id' => $ta->id]);

        JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $k1->id,
            'mata_pelajaran_id' => $mapel1->id,
            'pengajar_id' => $p1->id,
            'hari' => 'Senin',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruang' => 'Aula Utama',
        ]);

        // Bentrok ruangan meskipun kelas & pengajar berbeda -> harus ditolak (FR-08)
        $this->actingAs($pengurusAkademik)->post(route('admin.schedules.store'), [
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $k2->id,
            'mata_pelajaran_id' => $mapel2->id,
            'pengajar_id' => $p2->id,
            'hari' => 'Senin',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '09:30',
            'ruang' => 'Aula Utama',
        ])->assertSessionHasErrors();

        $this->assertSame(1, JadwalPelajaran::count());
    }

    public function test_fr09_fr10_fr11_fr12_fr13_and_rbac_matrix_enforcement(): void
    {
        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        $mapel = MataPelajaran::create(['kode_mapel' => 'MP-10', 'nama_mapel' => 'Shorof']);

        $teacherUser1 = User::create([
            'name' => 'Ustaz Wali',
            'email' => 'ustaz1@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);
        $teacher1 = Pengajar::create([
            'user_id' => $teacherUser1->id,
            'nomor_identitas' => 'UST-10',
            'nama_lengkap' => 'Ustaz Wali',
            'jenis_kelamin' => 'Laki-laki',
            'status_pengajar' => 'Aktif',
        ]);

        $teacherUser2 = User::create([
            'name' => 'Ustaz Lain',
            'email' => 'ustaz2@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);
        Pengajar::create([
            'user_id' => $teacherUser2->id,
            'nomor_identitas' => 'UST-20',
            'nama_lengkap' => 'Ustaz Lain',
            'jenis_kelamin' => 'Laki-laki',
            'status_pengajar' => 'Aktif',
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '7U',
            'nama_kelas' => 'Kelas 7 Ula',
            'tingkat_kelas' => '7 Ula',
            'wali_kelas_id' => $teacher1->id,
            'tahun_ajaran_id' => $ta->id,
        ]);

        $waliUser = User::create([
            'name' => 'Wali Santri Ahmad',
            'email' => 'wali.ahmad@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SANTRI,
            'email_verified_at' => now(),
        ]);

        $santri1 = Santri::create([
            'user_id' => $waliUser->id,
            'kelas_id' => $kelas->id,
            'nis' => '20260001',
            'nama_lengkap' => 'Ahmad Santri',
            'jenis_kelamin' => 'Laki-laki',
            'nama_wali' => 'Bapak Hasan',
            'status_santri' => 'Aktif',
        ]);

        $santri2 = Santri::create([
            'kelas_id' => $kelas->id,
            'nis' => '20260002',
            'nama_lengkap' => 'Zaid Santri',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
        ]);

        $schedule = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $teacher1->id,
            'hari' => 'Selasa',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruang' => 'R-01',
        ]);

        // Pastikan halaman daftar Pengolahan Nilai & Presensi berjalan normal tanpa BadMethodCallException
        $this->actingAs($teacherUser1)->get(route('teacher.grades.index'))->assertOk();
        $this->actingAs($teacherUser1)->get(route('teacher.attendance.index'))->assertOk();

        // Ustaz lain tidak boleh mengisi nilai jadwal milik Ustaz 1 -> 403
        $this->actingAs($teacherUser2)
            ->get(route('teacher.grades.edit', $schedule))
            ->assertForbidden();

        // FR-09: Ustaz 1 menyimpan presensi + catatan disiplin
        $this->actingAs($teacherUser1)->post(route('teacher.attendance.store'), [
            'jadwal_pelajaran_id' => $schedule->id,
            'tanggal' => '2026-10-27',
            'absensi' => [
                $santri1->id => ['status' => 'Hadir', 'keterangan' => 'Terlambat 10 menit'],
                $santri2->id => ['status' => 'Izin', 'keterangan' => 'Pulang izin keluarga'],
            ],
        ])->assertRedirect(route('teacher.attendance.index'));

        $this->assertDatabaseHas('absensis', [
            'santri_id' => $santri1->id,
            'status_kehadiran' => 'Hadir',
            'keterangan' => 'Terlambat 10 menit',
        ]);

        // FR-10: Ustaz 1 menyimpan nilai 30/30/40 + catatan evaluasi; baris kosong dilewati (tidak jadi 0/E)
        $this->actingAs($teacherUser1)->put(route('teacher.grades.update', $schedule), [
            'nilai' => [
                $santri1->id => [
                    'tugas' => 80,
                    'uts' => 90,
                    'uas' => 85,
                    'keterangan' => 'Hafalan tashrif sangat baik',
                ],
                $santri2->id => [
                    'tugas' => '',
                    'uts' => '',
                    'uas' => '',
                    'keterangan' => '',
                ],
            ],
        ])->assertRedirect(route('teacher.grades.index'));

        // 80*0.3 (24) + 90*0.3 (27) + 85*0.4 (34) = 85.00 -> Grade A
        $nilai1 = Nilai::where('santri_id', $santri1->id)->firstOrFail();
        $this->assertEquals(85.00, (float) $nilai1->nilai_akhir);
        $this->assertSame('A', $nilai1->grade);
        $this->assertSame('Hafalan tashrif sangat baik', $nilai1->keterangan);
        $this->assertNull(Nilai::where('santri_id', $santri2->id)->first());

        // FR-12: Wali Kelas dapat mengakses pusat laporan & menyimpan nilai sikap + mencetak Rapor PDF spesifik per mapel
        $this->actingAs($teacherUser1)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($teacherUser1)->post(route('admin.reports.wali-notes', $kelas), [
            'evaluasi' => [
                $santri1->id => [
                    'nilai_sikap' => 'Sangat Baik (A)',
                    'catatan_wali_kelas' => 'Ananda rajin jamaah dan mutholaah kitab.',
                ],
            ],
        ])->assertRedirect();
        $this->actingAs($teacherUser1)->get(route('admin.reports.santri-rapor-pdf', $santri1))
            ->assertOk()
            ->assertSee('Shorof')
            ->assertSee('85.00')
            ->assertSee('JUMLAH NILAI AKADEMIK')
            ->assertSee('Ananda rajin jamaah dan mutholaah kitab.')
            ->assertSee('Ibu Uswatun Hasanah SP, S.Pd.I.');

        // FR-11: Wali Santri mengakses portal & mengunduh Rapor PDF, tapi ditolak (403) pada modul Admin
        $this->actingAs($waliUser)->get(route('wali.dashboard'))->assertOk()->assertSee('Ahmad Santri');
        $this->actingAs($waliUser)->get(route('wali.grades'))->assertOk()->assertSee('Hafalan tashrif sangat baik');
        $this->actingAs($waliUser)->get(route('wali.rapor-pdf'))->assertOk()->assertSee('Shorof');
        $this->actingAs($waliUser)->get(route('admin.santri.index'))->assertForbidden();

        // FR-13 & RBAC Pimpinan: Read-Only Dashboard Eksekutif & Track Record PDF -> 200; modul teknis -> 403
        $pimpinanUser = User::create([
            'name' => 'KH. Imam Machmudi',
            'email' => 'kyai@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PIMPINAN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($pimpinanUser)->get(route('pimpinan.dashboard'))->assertOk();
        $this->actingAs($pimpinanUser)->get(route('pimpinan.export-pdf'))->assertOk()->assertSee('LAPORAN EKSEKUTIF');
        $this->actingAs($pimpinanUser)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($pimpinanUser)->get(route('admin.santri.index'))->assertForbidden();
    }

    public function test_strict_role_isolation_for_pengurus_akademik_and_kepala_sekolah(): void
    {
        $stafTU = User::create([
            'name' => 'Staf Tata Usaha',
            'email' => 'tu@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        // Staf TU (Pengurus Akademik) bisa mengelola SIAKAD, PPDB, dan modul CMS Website Yayasan
        $this->actingAs($stafTU)->get(route('dashboard'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.facilities.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('settings.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.programs.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.fees.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.registrations.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.santri.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.classes.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.schedules.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.promotions.index'))->assertOk();
        $this->actingAs($stafTU)->get(route('admin.reports.index'))->assertOk();

        // Staf TU dilarang mengakses manajemen pengguna pengembang dan portal eksekutif pimpinan
        $this->actingAs($stafTU)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($stafTU)->get(route('pimpinan.dashboard'))->assertForbidden();

        // Pengujian Hak Akses Kepala Sekolah / Madrasah
        $kepalaSekolah = User::create([
            'name' => 'Drs. KH. Ahmad Fauzi, M.Pd.',
            'email' => 'kepsek@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KEPALA_SEKOLAH,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($kepalaSekolah)->get(route('dashboard'))->assertOk()->assertSee('Kepala Madrasah');
        $this->actingAs($kepalaSekolah)->get(route('teacher.attendance.index'))->assertOk();
        $this->actingAs($kepalaSekolah)->get(route('teacher.grades.index'))->assertOk();
        $this->actingAs($kepalaSekolah)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($kepalaSekolah)->get(route('admin.classes.index'))->assertOk();
        $this->actingAs($kepalaSekolah)->get(route('admin.santri.index'))->assertOk();
        $this->actingAs($kepalaSekolah)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($kepalaSekolah)->get(route('pimpinan.dashboard'))->assertForbidden();
    }

    public function test_ppdb_multi_anak_and_random_password_generation(): void
    {
        $admin = User::create([
            'name' => 'Staf PPDB',
            'email' => 'ppdb@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        // Anak Pertama mendaftar
        $reg1 = SantriRegistration::create([
            'registration_number' => 'REG2026001',
            'student_name' => 'Ahmad Kakak',
            'gender' => 'Laki-laki',
            'birth_date' => '2013-01-01',
            'guardian_name' => 'Bapak Suparman',
            'guardian_phone' => '081234567899',
            'guardian_email' => 'wali.keluarga@example.com',
            'education_qualification' => 'SD/MI',
            'residential_address' => 'Lamongan',
            'target_class' => 'Kelas 7 MTs',
            'academic_year' => '2026/2027',
            'branch' => 'Lamongan',
            'status' => 'pending',
        ]);

        // Diterima oleh staf
        $this->actingAs($admin)->put(route('admin.registrations.update-status', $reg1), [
            'status' => 'accepted',
        ])->assertRedirect();

        $santri1 = Santri::where('nama_lengkap', 'Ahmad Kakak')->firstOrFail();
        $waliUser = User::find($santri1->user_id);
        $this->assertNotNull($waliUser);
        $this->assertEquals(User::ROLE_SANTRI, $waliUser->role);
        $this->assertTrue((bool)$waliUser->must_change_password);

        // Anak Kedua mendaftar dari wali yang sama (guardian_phone sama)
        $reg2 = SantriRegistration::create([
            'registration_number' => 'REG2026002',
            'student_name' => 'Fatimah Adik',
            'gender' => 'Perempuan',
            'birth_date' => '2015-05-05',
            'guardian_name' => 'Bapak Suparman',
            'guardian_phone' => '081234567899',
            'guardian_email' => 'fatimah@example.com',
            'education_qualification' => 'SD/MI',
            'residential_address' => 'Lamongan',
            'target_class' => 'Kelas 7 MTs',
            'academic_year' => '2026/2027',
            'branch' => 'Lamongan',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->put(route('admin.registrations.update-status', $reg2), [
            'status' => 'accepted',
        ])->assertRedirect();

        $santri2 = Santri::where('nama_lengkap', 'Fatimah Adik')->firstOrFail();

        // Santri kedua otomatis dihubungkan ke akun user wali yang sama (multi-anak)
        $this->assertEquals($waliUser->id, $santri2->user_id);
        $this->assertEquals(2, $waliUser->santris()->count());

        // Wali login dan switch anak aktif di portal
        $this->actingAs($waliUser)->get(route('wali.switch-santri', $santri2->id))->assertRedirect();
        $this->assertEquals($santri2->id, session('active_wali_santri_id'));
    }

    public function test_dual_gate_wali_portal_login_by_nis_and_hard_role_isolation(): void
    {
        $waliUser = User::create([
            'name' => 'Wali Santri Nabil',
            'email' => 'wali.nabil@nurulkhidmah.org',
            'password' => Hash::make('wali123'),
            'role' => User::ROLE_SANTRI,
            'email_verified_at' => now(),
        ]);

        Santri::create([
            'user_id' => $waliUser->id,
            'nis' => '20269988',
            'nama_lengkap' => 'Muhammad Nabil',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
        ]);

        $superAdmin = User::create([
            'name' => 'Super Admin Sistem',
            'email' => 'super@nurulkhidmah.org',
            'password' => Hash::make('rahasia123'),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->get(route('wali.login'))->assertOk()->assertSee('Portal Wali Santri');
        $this->get(route('staff.login'))->assertOk();

        // Hard Role Isolation: akun Super Admin dengan password BENAR tetap ditolak di pintu publik /portal-wali
        $this->post(route('wali.login.store'), [
            'identitas' => 'super@nurulkhidmah.org',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('identitas');

        $this->assertGuest();
        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'login_failed',
            'target_type' => 'WaliPortal',
        ]);

        // Wali Santri cukup login memakai 8-digit NIS Ananda + password tanpa perlu mengingat email
        $this->post(route('wali.login.store'), [
            'identitas' => '20269988',
            'password' => 'wali123',
        ])->assertRedirect(route('wali.dashboard'));

        $this->assertAuthenticatedAs($waliUser);
    }

    public function test_collective_class_promotion_graduation_and_specific_subject_leger_rapor(): void
    {
        $pengurus = User::create([
            'name' => 'Pengurus Akademik Utama',
            'email' => 'pengurus@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        $teacherUser = User::create([
            'name' => 'Ustaz Pengampu',
            'email' => 'pengampu@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
        ]);
        $teacher = Pengajar::create([
            'user_id' => $teacherUser->id,
            'nomor_identitas' => 'UST-77',
            'nama_lengkap' => 'Ustaz Pengampu',
            'jenis_kelamin' => 'Laki-laki',
            'status_pengajar' => 'Aktif',
        ]);

        $taLama = TahunAjaran::create(['nama_tahun_ajaran' => '2025/2026', 'semester' => 'Genap', 'status_aktif' => false]);
        $taBaru = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);

        $kelas7 = Kelas::create([
            'kode_kelas' => '7A',
            'nama_kelas' => 'Kelas 7 Ula A',
            'tingkat_kelas' => '7 Ula',
            'wali_kelas_id' => $teacher->id,
            'tahun_ajaran_id' => $taLama->id,
        ]);
        $kelas8 = Kelas::create([
            'kode_kelas' => '8A',
            'nama_kelas' => 'Kelas 8 Wustho A',
            'tingkat_kelas' => '8 Wustho',
            'wali_kelas_id' => $teacher->id,
            'tahun_ajaran_id' => $taBaru->id,
        ]);

        $mapelFiqih = MataPelajaran::create(['kode_mapel' => 'MP-FQH', 'nama_mapel' => 'Fiqih Ibadah']);
        $mapelNahwu = MataPelajaran::create(['kode_mapel' => 'MP-NHW', 'nama_mapel' => 'Ilmu Nahwu']);

        $jadwalFiqih = JadwalPelajaran::create([
            'tahun_ajaran_id' => $taLama->id,
            'kelas_id' => $kelas7->id,
            'mata_pelajaran_id' => $mapelFiqih->id,
            'pengajar_id' => $teacher->id,
            'hari' => 'Senin',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruang' => 'R-7A',
        ]);
        $jadwalNahwu = JadwalPelajaran::create([
            'tahun_ajaran_id' => $taLama->id,
            'kelas_id' => $kelas7->id,
            'mata_pelajaran_id' => $mapelNahwu->id,
            'pengajar_id' => $teacher->id,
            'hari' => 'Selasa',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruang' => 'R-7A',
        ]);

        $waliUser = User::create([
            'name' => 'Wali Santri Farhan',
            'email' => 'wali.farhan@nurulkhidmah.org',
            'password' => Hash::make('wali123'),
            'role' => User::ROLE_SANTRI,
            'email_verified_at' => now(),
        ]);

        $santri = Santri::create([
            'user_id' => $waliUser->id,
            'kelas_id' => $kelas7->id,
            'nis' => '20250101',
            'nama_lengkap' => 'Farhan Al-Lamongani',
            'jenis_kelamin' => 'Laki-laki',
            'nama_wali' => 'Bapak Abdullah',
            'status_santri' => 'Aktif',
        ]);

        Nilai::create([
            'santri_id' => $santri->id,
            'jadwal_pelajaran_id' => $jadwalFiqih->id,
            'nilai_tugas' => 80,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
            'nilai_akhir' => 85.50,
            'grade' => 'A',
            'keterangan' => 'Tuntas sangat baik',
        ]);
        Nilai::create([
            'santri_id' => $santri->id,
            'jadwal_pelajaran_id' => $jadwalNahwu->id,
            'nilai_tugas' => 90,
            'nilai_uts' => 90,
            'nilai_uas' => 90,
            'nilai_akhir' => 90.00,
            'grade' => 'A',
            'keterangan' => 'Istimewa',
        ]);

        // 1. Uji Cetak Leger Nilai Kelas (menampilkan rincian spesifik tiap mapel + Jumlah + Rata-Rata)
        $this->actingAs($pengurus)
            ->get(route('admin.reports.grades-pdf', ['kelas_id' => $kelas7->id, 'tahun_ajaran_id' => $taLama->id]))
            ->assertOk()
            ->assertSee('MP-FQH')
            ->assertSee('MP-NHW')
            ->assertSee('85.50')
            ->assertSee('90.00')
            ->assertSee('175.50') // Jumlah nilai spesifik (85.50 + 90.00)
            ->assertSee('87.75'); // Rata-rata nilai

        // 2. Uji Cetak Nilai Spesifik 1 Mata Pelajaran (Tugas 30% / UTS 30% / UAS 40%)
        $this->actingAs($pengurus)
            ->get(route('admin.reports.grades-pdf', [
                'kelas_id' => $kelas7->id,
                'tahun_ajaran_id' => $taLama->id,
                'mata_pelajaran_id' => $mapelFiqih->id,
            ]))
            ->assertOk()
            ->assertSee('DAFTAR CAPAIAN NILAI SPESIFIK MATA PELAJARAN')
            ->assertSee('Fiqih Ibadah')
            ->assertSee('80.00')
            ->assertSee('85.00')
            ->assertSee('90.00')
            ->assertSee('85.50');

        // 3. Eksekusi Kenaikan Kelas Kolektif dari Kelas 7 Ula A -> Kelas 8 Wustho A
        $this->actingAs($pengurus)
            ->post(route('admin.promotions.store'), [
                'asal_kelas_id' => $kelas7->id,
                'santri_ids' => [$santri->id],
                'jenis_aksi' => 'Naik Kelas',
                'tujuan_kelas_id' => $kelas8->id,
                'catatan' => 'Naik ke tingkat Wustho',
            ])
            ->assertRedirect();

        $santri->refresh();
        $this->assertSame($kelas8->id, $santri->kelas_id);
        $this->assertSame('20250101', $santri->nis);
        $this->assertSame($waliUser->id, $santri->user_id);
        $this->assertTrue(RiwayatKelasSantri::where('santri_id', $santri->id)->where('jenis_aksi', 'Naik Kelas')->exists());

        // 4. Uji Filterisasi Pencarian Santri pada Halaman Cetak Rapor (/admin/reports)
        $this->actingAs($pengurus)
            ->get(route('admin.reports.index', [
                'tahun_ajaran_id' => $taBaru->id,
                'tingkat' => '8',
                'search' => 'Farhan',
            ]))
            ->assertOk()
            ->assertSee('Farhan Al-Lamongani')
            ->assertSee('Kelas 8 Wustho A');

        // 5. Wali Santri tetap dapat mengakses Arsip Rapor Kelas 7 (TA 2025/2026) setelah anaknya naik ke Kelas 8!
        $this->actingAs($waliUser)
            ->get(route('wali.rapor-pdf', ['tahun_ajaran_id' => $taLama->id]))
            ->assertOk()
            ->assertSee('Kelas 7 Ula A')
            ->assertSee('Fiqih Ibadah')
            ->assertSee('Ilmu Nahwu')
            ->assertSee('175.50')
            ->assertSee('87.75');

        // 6. Eksekusi Kelulusan Kolektif -> status Lulus, namun akun Wali & arsip rapor tetap hidup
        $this->actingAs($pengurus)
            ->post(route('admin.promotions.store'), [
                'asal_kelas_id' => $kelas8->id,
                'santri_ids' => [$santri->id],
                'jenis_aksi' => 'Lulus',
                'catatan' => 'Lulus dengan predikat sangat baik',
            ])
            ->assertRedirect();

        $santri->refresh();
        $this->assertSame('Lulus', $santri->status_santri);
        $this->actingAs($waliUser)
            ->get(route('wali.grades', ['tahun_ajaran_id' => $taLama->id]))
            ->assertOk()
            ->assertSee('Fiqih Ibadah');
    }

    public function test_fase2_rapor_semesters_table_multi_anak_has_many_and_service_integration(): void
    {
        // 1. Verifikasi skema tabel santris: kolom nilai_sikap & catatan_wali_kelas telah dihapus
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('santris', 'nilai_sikap'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('santris', 'catatan_wali_kelas'));

        // 2. Verifikasi skema tabel rapor_semesters
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('rapor_semesters'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumns('rapor_semesters', [
            'santri_id', 'tahun_ajaran_id', 'kelas_id', 'status_kenaikan',
            'catatan_wali_kelas', 'nilai_sikap', 'status_publikasi', 'approved_by', 'approved_at'
        ]));

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $waliUser = User::create([
            'name' => 'Haji Abdullah (Wali Santri)',
            'email' => 'wali.abdullah@nurulkhidmah.org',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SANTRI,
            'email_verified_at' => now(),
        ]);

        $guruUser = User::create([
            'name' => 'Ustaz Karim, S.Pd.I',
            'email' => 'karim@nurulkhidmah.org',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'GURU-0099',
            'nama_lengkap' => 'Ustaz Karim, S.Pd.I',
            'jenis_kelamin' => 'Laki-laki',
            'status_pengajar' => 'Aktif',
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => 'K7-FASE2',
            'nama_kelas' => 'Kelas 7 Fiqih',
            'tingkat_kelas' => '7 MTs',
            'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $pengajar->id,
        ]);

        // 3. Verifikasi Relasi Multi-Anak HasMany
        $anak1 = Santri::create([
            'user_id' => $waliUser->id,
            'kelas_id' => $kelas->id,
            'nis' => '880011',
            'nama_lengkap' => 'Ahmad Santri Pertama',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
        ]);

        $anak2 = Santri::create([
            'user_id' => $waliUser->id,
            'kelas_id' => $kelas->id,
            'nis' => '880022',
            'nama_lengkap' => 'Aisyah Santri Kedua',
            'jenis_kelamin' => 'Perempuan',
            'status_santri' => 'Aktif',
        ]);

        $waliUser->refresh();
        $this->assertEquals(2, $waliUser->santris()->count());
        $this->assertTrue($anak1->wali->is($waliUser));
        $this->assertTrue($anak2->wali->is($waliUser));

        // 4. Verifikasi Penyimpanan Catatan Wali Kelas ke Tabel rapor_semesters
        $this->actingAs($guruUser)->post(route('admin.reports.wali-notes', $kelas), [
            'evaluasi' => [
                $anak1->id => [
                    'nilai_sikap' => 'Sangat Baik (A)',
                    'catatan_wali_kelas' => 'Alhamdulillah, hafalan Al-Quran bertambah pesat.',
                ],
                $anak2->id => [
                    'nilai_sikap' => 'Baik (B)',
                    'catatan_wali_kelas' => 'Pertahankan kedisiplinan shalat berjamaah.',
                ],
            ],
        ])->assertRedirect();

        $rapor1 = \App\Models\RaporSemester::where('santri_id', $anak1->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->firstOrFail();

        $this->assertSame('Sangat Baik (A)', $rapor1->nilai_sikap);
        $this->assertSame('Alhamdulillah, hafalan Al-Quran bertambah pesat.', $rapor1->catatan_wali_kelas);
        $this->assertSame($kelas->id, $rapor1->kelas_id);
        $this->assertSame('published', $rapor1->status_publikasi);

        // 5. Verifikasi AcademicRaporService mengambil langsung dari rapor_semesters
        $service = app(\App\Services\AcademicRaporService::class);
        $raporData1 = $service->buildSantriRaporData($anak1, $ta);
        $this->assertSame('Sangat Baik (A)', $raporData1['nilaiSikap']);
        $this->assertSame('Alhamdulillah, hafalan Al-Quran bertambah pesat.', $raporData1['catatanWaliKelas']);
        $this->assertNotNull($raporData1['raporSemester']);

        // 6. Verifikasi Switch Santri Aktif via Session untuk Wali Multi-Anak
        $this->actingAs($waliUser)->get(route('wali.dashboard'))->assertOk()->assertSee('Ahmad Santri Pertama');

        // Beralih ke anak kedua via session / switcher
        $this->actingAs($waliUser)->get(route('wali.switch-santri', $anak2->id))->assertRedirect();
        $this->assertEquals($anak2->id, session('active_wali_santri_id'));

        $this->actingAs($waliUser)->get(route('wali.dashboard'))->assertOk()->assertSee('Aisyah Santri Kedua');
    }

    public function test_fase3_multi_unit_pendidikan_and_majors_integration(): void
    {
        // 1. Verifikasi 4 unit pendidikan resmi telah dibuat
        $this->assertDatabaseHas('educational_units', [
            'kode_unit' => 'MTS',
            'nama_unit' => 'MTs Nurul Khidmah',
            'jenjang' => 'MTs',
        ]);
        $this->assertDatabaseHas('educational_units', [
            'kode_unit' => 'MA',
            'nama_unit' => 'MA Nurul Khidmah',
            'jenjang' => 'MA',
        ]);
        $this->assertDatabaseHas('educational_units', [
            'kode_unit' => 'SMK-OTO',
            'nama_unit' => 'SMK Otomotif Nurul Khidmah',
            'jenjang' => 'SMK',
        ]);
        $this->assertDatabaseHas('educational_units', [
            'kode_unit' => 'SMK-DKV',
            'nama_unit' => 'SMK DKV Nurul Khidmah',
            'jenjang' => 'SMK',
        ]);

        // 2. Verifikasi Jurusan kejuruan pada SMK Otomotif & SMK DKV
        $smkOto = \App\Models\EducationalUnit::where('kode_unit', 'SMK-OTO')->firstOrFail();
        $smkDkv = \App\Models\EducationalUnit::where('kode_unit', 'SMK-DKV')->firstOrFail();

        $this->assertTrue($smkOto->majors()->where('kode_jurusan', 'TO')->exists());
        $this->assertTrue($smkDkv->majors()->where('kode_jurusan', 'DKV')->exists());

        // 3. Verifikasi Relasi Kelas, Santri, dan Mata Pelajaran dengan EducationalUnit
        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $kelasSmk = Kelas::create([
            'kode_kelas' => 'X-OTO-1',
            'nama_kelas' => 'Kelas 10 Teknik Otomotif',
            'tingkat_kelas' => '10 SMK',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $smkOto->id,
        ]);

        $wali = User::create([
            'name' => 'Wali Siswa SMK',
            'email' => 'wali.smk@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SANTRI,
            'email_verified_at' => now(),
        ]);

        $santriSmk = Santri::create([
            'user_id' => $wali->id,
            'kelas_id' => $kelasSmk->id,
            'educational_unit_id' => $smkOto->id,
            'nis' => 'SMK2026001',
            'nama_lengkap' => 'Bambang Otomotif',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
        ]);

        $mapelKejuruan = MataPelajaran::create([
            'educational_unit_id' => $smkOto->id,
            'kode_mapel' => 'OTO-01',
            'nama_mapel' => 'Teknologi Dasar Otomotif (TDO)',
            'keterangan' => 'Mapel kejuruan otomotif',
        ]);

        $this->assertTrue($kelasSmk->educationalUnit->is($smkOto));
        $this->assertTrue($santriSmk->educationalUnit->is($smkOto));
        $this->assertTrue($mapelKejuruan->educationalUnit->is($smkOto));
        $this->assertTrue($smkOto->kelasses->contains($kelasSmk));
        $this->assertTrue($smkOto->santris->contains($santriSmk));
        $this->assertTrue($smkOto->mataPelajarans->contains($mapelKejuruan));
    }

    public function test_fase4_frontend_pipeline_vite_and_tailwind_v4_integration(): void
    {
        // 1. Verifikasi manifes build Vite ada dan valid
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifestContent = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifestContent);
        $this->assertArrayHasKey('resources/css/app.css', $manifestContent);
        $this->assertArrayHasKey('resources/js/app.js', $manifestContent);

        // 2. Verifikasi halaman publik me-render asset vite dan bebas dari cdn.tailwindcss.com
        $responseHome = $this->get(route('home'));
        $responseHome->assertOk()
            ->assertDontSee('cdn.tailwindcss.com')
            ->assertSee('/build/assets/app', false);

        $responseLogin = $this->get(route('login'));
        $responseLogin->assertOk()
            ->assertDontSee('cdn.tailwindcss.com')
            ->assertSee('/build/assets/app', false);

        $responseWaliLogin = $this->get(route('wali.login'));
        $responseWaliLogin->assertOk()
            ->assertDontSee('cdn.tailwindcss.com')
            ->assertSee('/build/assets/app', false);

        $responseRegistration = $this->get(route('registration'));
        $responseRegistration->assertOk()
            ->assertDontSee('cdn.tailwindcss.com')
            ->assertSee('/build/assets/app', false);

        // 3. Verifikasi layout admin me-render asset vite
        $admin = User::factory()->pengurusAkademik()->create();
        $responseDashboard = $this->actingAs($admin)->get(route('dashboard'));
        $responseDashboard->assertOk()
            ->assertDontSee('cdn.tailwindcss.com')
            ->assertSee('/build/assets/app', false);
    }
}
