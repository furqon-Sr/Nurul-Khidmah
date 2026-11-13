<?php

namespace Tests\Feature;

use App\Actions\AcceptSantriRegistration;
use App\Models\Absensi;
use App\Models\EducationalUnit;
use App\Models\GradeComponent;
use App\Models\GradePredicate;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\PageContent;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UrsFase5AutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dinamisasi_komponen_nilai_dan_predikat(): void
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'SMK-OTO'],
            [
                'nama_unit' => 'SMK Otomotif Nurul Khidmah',
                'jenjang' => 'SMK',
                'is_active' => true,
            ]
        );

        // Buat bobot komponen dinamis: Tugas 20%, UTS 30%, UAS 50%
        GradeComponent::create([
            'educational_unit_id' => $unit->id,
            'nama_komponen' => 'Tugas',
            'bobot_persen' => 20,
        ]);
        GradeComponent::create([
            'educational_unit_id' => $unit->id,
            'nama_komponen' => 'UTS',
            'bobot_persen' => 30,
        ]);
        GradeComponent::create([
            'educational_unit_id' => $unit->id,
            'nama_komponen' => 'UAS',
            'bobot_persen' => 50,
        ]);

        // Buat predikat nilai dinamis khusus unit: A >= 90, B >= 80, C >= 70, D >= 60, E < 60
        GradePredicate::create([
            'educational_unit_id' => $unit->id,
            'huruf' => 'A',
            'batas_bawah' => 90,
            'batas_atas' => 100,
        ]);
        GradePredicate::create([
            'educational_unit_id' => $unit->id,
            'huruf' => 'B',
            'batas_bawah' => 80,
            'batas_atas' => 89.99,
        ]);
        GradePredicate::create([
            'educational_unit_id' => $unit->id,
            'huruf' => 'C',
            'batas_bawah' => 70,
            'batas_atas' => 79.99,
        ]);
        GradePredicate::create([
            'educational_unit_id' => $unit->id,
            'huruf' => 'D',
            'batas_bawah' => 60,
            'batas_atas' => 69.99,
        ]);
        GradePredicate::create([
            'educational_unit_id' => $unit->id,
            'huruf' => 'E',
            'batas_bawah' => 0,
            'batas_atas' => 59.99,
        ]);

        // Uji fungsi model Nilai::calculateGrade dengan unit id
        $this->assertSame('A', Nilai::calculateGrade(92.0, $unit->id));
        $this->assertSame('B', Nilai::calculateGrade(88.0, $unit->id)); // Di rumus lama 88 adalah A, tapi di unit ini adalah B
        $this->assertSame('C', Nilai::calculateGrade(75.0, $unit->id));
        $this->assertSame('D', Nilai::calculateGrade(65.0, $unit->id));
        $this->assertSame('E', Nilai::calculateGrade(55.0, $unit->id));

        // Setup entitas untuk pengujian GradeController update
        $guruUser = User::create([
            'name' => 'Guru Kejuruan',
            'email' => 'kejuruan@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);
        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Guru Kejuruan',
            'nomor_identitas' => 'GK-001',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);
        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        $kelas = Kelas::create([
            'kode_kelas' => 'KLS-10OTO',
            'nama_kelas' => '10 SMK Otomotif',
            'tingkat_kelas' => '10',
            'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $pengajar->id,
            'educational_unit_id' => $unit->id,
        ]);
        $mapel = MataPelajaran::create([
            'kode_mapel' => 'OTO-01',
            'nama_mapel' => 'Teknologi Dasar Otomotif',
            'educational_unit_id' => $unit->id,
        ]);
        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $pengajar->id,
            'hari' => 'Senin',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruangan' => 'Bengkel 1',
        ]);
        $santri = Santri::create([
            'nis' => '26SMK001',
            'nama_lengkap' => 'Budi Santoso',
            'kelas_id' => $kelas->id,
            'educational_unit_id' => $unit->id,
            'status_santri' => 'Aktif',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        // Input nilai via GradeController: Tugas = 100, UTS = 80, UAS = 60
        // Berdasarkan bobot dinamis (20/30/50):
        // (100 * 0.2) + (80 * 0.3) + (60 * 0.5) = 20 + 24 + 30 = 74.00
        // Berdasarkan predikat unit SMK: 74.00 mendapatkan grade 'C'
        $this->actingAs($guruUser)->put(route('teacher.grades.update', $jadwal), [
            'nilai' => [
                $santri->id => [
                    'tugas' => 100,
                    'uts' => 80,
                    'uas' => 60,
                    'keterangan' => 'Praktik mesin baik',
                ],
            ],
        ])->assertRedirect(route('teacher.grades.index'));

        $nilaiRecord = Nilai::where('jadwal_pelajaran_id', $jadwal->id)->where('santri_id', $santri->id)->firstOrFail();
        $this->assertEquals(74.00, (float) $nilaiRecord->nilai_akhir);
        $this->assertSame('C', $nilaiRecord->grade);
    }

    public function test_dinamisasi_absen_mendukung_terlambat_dan_bolos(): void
    {
        $guruUser = User::create([
            'name' => 'Guru Presensi',
            'email' => 'presensi@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);
        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Guru Presensi',
            'nomor_identitas' => 'GP-001',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);
        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        $kelas = Kelas::create([
            'kode_kelas' => 'KLS-7A',
            'nama_kelas' => 'Kelas 7A',
            'tingkat_kelas' => '7',
            'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $pengajar->id,
        ]);
        $mapel = MataPelajaran::create(['kode_mapel' => 'MP-ARB', 'nama_mapel' => 'Bahasa Arab']);
        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $pengajar->id,
            'hari' => 'Selasa',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruangan' => 'R-01',
        ]);
        $santri1 = Santri::create([
            'nis' => '26MTS001',
            'nama_lengkap' => 'Santri Terlambat',
            'kelas_id' => $kelas->id,
            'status_santri' => 'Aktif',
            'jenis_kelamin' => 'Laki-laki',
        ]);
        $santri2 = Santri::create([
            'nis' => '26MTS002',
            'nama_lengkap' => 'Santri Bolos',
            'kelas_id' => $kelas->id,
            'status_santri' => 'Aktif',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $this->actingAs($guruUser)->post(route('teacher.attendance.store'), [
            'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => $jadwal->latestMatchingDate(),
            'absensi' => [
                $santri1->id => [
                    'status' => 'Terlambat',
                    'keterangan' => 'Terlambat 15 menit',
                ],
                $santri2->id => [
                    'status' => 'Bolos',
                    'keterangan' => 'Meninggalkan jam ke-2 tanpa izin',
                ],
            ],
        ])->assertRedirect(route('teacher.attendance.index'));

        $this->assertDatabaseHas('absensis', [
            'santri_id' => $santri1->id,
            'status_kehadiran' => 'Terlambat',
        ]);
        $this->assertDatabaseHas('absensis', [
            'santri_id' => $santri2->id,
            'status_kehadiran' => 'Bolos',
        ]);
    }

    public function test_automasi_ppdb_accept_santri_registration_action(): void
    {
        $unitMts = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS'],
            [
                'nama_unit' => 'MTs Nurul Khidmah',
                'jenjang' => 'MTs',
                'is_active' => true,
            ]
        );
        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        $kelas = Kelas::create([
            'kode_kelas' => 'KLS-7MTS',
            'nama_kelas' => 'Kelas 7 MTs',
            'tingkat_kelas' => '7 MTs',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unitMts->id,
        ]);

        $registration = SantriRegistration::create([
            'registration_number' => 'REG-26001',
            'student_name' => 'Muhammad Wildan',
            'gender' => 'L',
            'birth_date' => '2013-08-20',
            'target_class' => 'MTs',
            'academic_year' => '2026/2027',
            'branch' => 'Lamongan',
            'guardian_name' => 'Ahmad Fauzi',
            'guardian_email' => 'fauzi@example.com',
            'guardian_phone' => '085712345678',
            'education_qualification' => 'SD/MI',
            'residential_address' => 'Lamongan, Jawa Timur',
            'status' => 'pending',
        ]);

        $action = new AcceptSantriRegistration;
        $result = $action->execute($registration);

        $registration->refresh();
        $this->assertSame('accepted', $registration->status);
        $this->assertNotNull($registration->santri_id);

        $santri = Santri::find($registration->santri_id);
        $this->assertNotNull($santri);
        // Format NIS otomatis tahun 2 digit + unit + 3 digit: 26MTS001
        $this->assertSame('26MTS001', $santri->nis);
        $this->assertSame('Muhammad Wildan', $santri->nama_lengkap);
        $this->assertSame('Laki-laki', $santri->jenis_kelamin);
        $this->assertSame($unitMts->id, $santri->educational_unit_id);

        // Akun Wali otomatis dibuat
        $this->assertNotNull($santri->user);
        $this->assertSame('fauzi@example.com', $santri->user->email);
        $this->assertSame(User::ROLE_SANTRI, $santri->user->role);
        $this->assertTrue((bool) $santri->user->must_change_password);

        // Uji idempotensi
        $result2 = $action->execute($registration);
        $this->assertSame($santri->id, $result2['santri']->id);
        $this->assertSame(1, Santri::count());
    }

    public function test_setup_server_side_pdf_engine_download_routes(): void
    {
        $admin = User::create([
            'name' => 'Admin Akademik',
            'email' => 'akademik.pdf@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        $waliUser = User::create([
            'name' => 'Wali Santri PDF',
            'email' => 'wali.pdf@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SANTRI,
            'email_verified_at' => now(),
        ]);

        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        $guru = Pengajar::create([
            'user_id' => $admin->id,
            'nama_lengkap' => 'Ust. Zulkifli, M.Pd.',
            'nomor_identitas' => 'GR-999',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);
        $kelas = Kelas::create([
            'kode_kelas' => 'KLS-8A',
            'nama_kelas' => 'Kelas 8A',
            'tingkat_kelas' => '8',
            'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $guru->id,
        ]);
        $mapel = MataPelajaran::create(['kode_mapel' => 'MP-FIQ', 'nama_mapel' => 'Fiqih Fathul Qorib']);
        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $guru->id,
            'hari' => 'Rabu',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruangan' => 'R-08',
        ]);
        $santri = Santri::create([
            'user_id' => $waliUser->id,
            'nis' => '26MTS008',
            'nama_lengkap' => 'Ahmad Raporwan',
            'kelas_id' => $kelas->id,
            'status_santri' => 'Aktif',
            'jenis_kelamin' => 'Laki-laki',
            'nama_wali' => 'Bapak Raporwan',
        ]);

        Nilai::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'nilai_tugas' => 85,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
            'nilai_akhir' => 87.00,
            'grade' => 'A',
            'keterangan' => 'Faham matan fiqih dengan baik',
        ]);

        Absensi::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-04',
            'status_kehadiran' => 'Hadir',
        ]);

        // 1. Download Rapor Santri PDF sejati
        $responseRapor = $this->actingAs($admin)
            ->get(route('admin.reports.santri-rapor.download', $santri));
        $responseRapor->assertOk();
        $this->assertSame('application/pdf', $responseRapor->headers->get('Content-Type'));
        $this->assertStringContainsString('.pdf', $responseRapor->headers->get('Content-Disposition') ?? '');
        $this->assertStringStartsWith('%PDF-', $responseRapor->getContent());

        // Pastikan preview rapor santri menyertakan favicon situs & 3 penandatangan
        PageContent::updateOrCreate(
            ['section_key' => 'favicon'],
            ['image_path' => 'contents/site-icon.png']
        );
        Cache::forget('site_global_data');

        $previewRapor = $this->actingAs($admin)->get(route('admin.reports.santri-rapor-pdf', $santri));
        $previewRapor->assertOk();
        $previewRapor->assertSee('storage/contents/site-icon.png');
        $previewRapor->assertSee('rel="icon"', false);
        $previewRapor->assertSee('Orang Tua / Wali Santri');
        $previewRapor->assertSee('Wali Kelas');
        $previewRapor->assertSee('Kepala Sekolah');

        // 2. Download Leger Nilai Kelas PDF sejati
        $responseLeger = $this->actingAs($admin)
            ->get(route('admin.reports.grades.download', ['kelas_id' => $kelas->id]));
        $responseLeger->assertOk();
        $this->assertSame('application/pdf', $responseLeger->headers->get('Content-Type'));
        $this->assertStringContainsString('.pdf', $responseLeger->headers->get('Content-Disposition') ?? '');
        $this->assertStringStartsWith('%PDF-', $responseLeger->getContent());

        // 3. Download Rekap Absen Kelas PDF sejati
        $responseAbsen = $this->actingAs($admin)
            ->get(route('admin.reports.attendance.download', ['kelas_id' => $kelas->id]));
        $responseAbsen->assertOk();
        $this->assertSame('application/pdf', $responseAbsen->headers->get('Content-Type'));
        $this->assertStringContainsString('.pdf', $responseAbsen->headers->get('Content-Disposition') ?? '');
        $this->assertStringStartsWith('%PDF-', $responseAbsen->getContent());

        // 4. Download Rapor Santri dari Portal Wali Santri
        $responseWaliRapor = $this->actingAs($waliUser)
            ->get(route('wali.rapor.download'));
        $responseWaliRapor->assertOk();
        $this->assertSame('application/pdf', $responseWaliRapor->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $responseWaliRapor->getContent());
    }
}
