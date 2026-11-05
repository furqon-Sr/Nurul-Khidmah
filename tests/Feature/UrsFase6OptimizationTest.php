<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\EducationalUnit;
use App\Models\GradeComponent;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\RaporSemester;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AcademicRaporService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UrsFase6OptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan strict mode pencegahan lazy loading selalu aktif di environment pengujian
        Model::preventLazyLoading(true);
    }

    public function test_strict_mode_prevents_lazy_loading_is_active(): void
    {
        $this->assertTrue(Model::preventsLazyLoading());

        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-OPT'],
            ['nama_unit' => 'MTs Nurul Khidmah', 'jenjang' => 'MTs', 'is_active' => true]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $kelas1 = Kelas::create([
            'kode_kelas' => '7A-OPT1',
            'nama_kelas' => 'Kelas 7A Optimasi 1',
            'tingkat_kelas' => '7',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $kelas2 = Kelas::create([
            'kode_kelas' => '7A-OPT2',
            'nama_kelas' => 'Kelas 7A Optimasi 2',
            'tingkat_kelas' => '7',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        // Model collection yang diambil tanpa eager loading relasi 'educationalUnit'
        $kelases = Kelas::all();
        $this->assertGreaterThanOrEqual(2, $kelases->count());

        $this->expectException(LazyLoadingViolationException::class);
        // Mengakses relasi pada koleksi tanpa with() harus melempar LazyLoadingViolationException
        foreach ($kelases as $k) {
            $k->educationalUnit;
        }
    }

    public function test_leger_eager_loading_with_constrained_year(): void
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-OPT2'],
            ['nama_unit' => 'MTs Nurul Khidmah 2', 'jenjang' => 'MTs', 'is_active' => true]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Ust. Eager Loading',
            'email' => 'eager@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'NIY-EAGER-01',
            'nama_lengkap' => 'Ust. Eager Loading, M.Pd.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '7B-OPT',
            'nama_kelas' => 'Kelas 7B Optimasi',
            'tingkat_kelas' => '7',
            'wali_kelas_id' => $pengajar->id,
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'FIQ-OPT',
            'nama_mapel' => 'Fiqih Optimasi',
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
            'ruangan' => 'Ruang 7B',
        ]);

        $comp = GradeComponent::create([
            'educational_unit_id' => $unit->id,
            'nama_komponen' => 'Tugas',
            'bobot_persen' => 30,
        ]);

        $santri = Santri::create([
            'nis' => '26MTS099',
            'nama_lengkap' => 'Ahmad Eager Optimizer',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
            'kelas_id' => $kelas->id,
            'educational_unit_id' => $unit->id,
        ]);

        $nilai = Nilai::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'nilai_tugas' => 90,
            'nilai_uts' => 88,
            'nilai_uas' => 92,
            'nilai_akhir' => 90.2,
            'grade' => 'A',
            'keterangan' => 'Sangat Baik',
        ]);

        $absen = Absensi::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'status_kehadiran' => 'Hadir',
        ]);

        $rapor = RaporSemester::create([
            'santri_id' => $santri->id,
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'nilai_sikap' => 'Sangat Baik (A)',
            'catatan_wali_kelas' => 'Pertahankan prestasi dan istiqomah belajar.',
            'status_publikasi' => 'published',
        ]);

        // Verifikasi struktur pemanggilan Eager Loading seperti yang disyaratkan user:
        // Kelas::with(['santris.nilais.mataPelajaran', 'santris.nilais.gradeComponent', 'santris.absensis'])->findOrFail($id);
        $loadedKelas = Kelas::with([
            'santris.nilais.mataPelajaran',
            'santris.nilais.gradeComponent',
            'santris.absensis',
            'santris.raporSemesters',
        ])->findOrFail($kelas->id);

        $this->assertTrue($loadedKelas->relationLoaded('santris'));
        $firstSantri = $loadedKelas->santris->first();
        $this->assertNotNull($firstSantri);
        $this->assertTrue($firstSantri->relationLoaded('nilais'));
        $this->assertTrue($firstSantri->relationLoaded('absensis'));
        $this->assertTrue($firstSantri->relationLoaded('raporSemesters'));

        $firstNilai = $firstSantri->nilais->first();
        $this->assertNotNull($firstNilai);
        $this->assertTrue($firstNilai->relationLoaded('mataPelajaran'));
        $this->assertSame('Fiqih Optimasi', $firstNilai->mataPelajaran->nama_mapel);

        // Akses accessor sikap & catatan wali tanpa memicu query tambahan karena raporSemesters sudah ter-load
        $this->assertSame('Sangat Baik (A)', $firstSantri->nilai_sikap);
        $this->assertSame('Pertahankan prestasi dan istiqomah belajar.', $firstSantri->catatan_wali_kelas);
    }

    public function test_teacher_controllers_eager_load_santris_without_lazy_loading_violations(): void
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-GURU'],
            ['nama_unit' => 'MTs Nurul Khidmah Guru', 'jenjang' => 'MTs', 'is_active' => true]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Ustazah Khodijah',
            'email' => 'khodijah@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'NIY-GURU-02',
            'nama_lengkap' => 'Ustazah Khodijah, S.Pd.',
            'jenis_kelamin' => 'Perempuan',
            'status_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '8A-GURU',
            'nama_kelas' => 'Kelas 8A Guru',
            'tingkat_kelas' => '8',
            'wali_kelas_id' => $pengajar->id,
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'TAF-01',
            'nama_mapel' => 'Tafsir Jalalain',
            'educational_unit_id' => $unit->id,
        ]);

        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $pengajar->id,
            'hari' => 'Selasa',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '09:30',
            'ruangan' => 'Ruang 8A',
        ]);

        $santri = Santri::create([
            'nis' => '26MTS088',
            'nama_lengkap' => 'Fatimah Az Zahra',
            'jenis_kelamin' => 'Perempuan',
            'status_santri' => 'Aktif',
            'kelas_id' => $kelas->id,
            'educational_unit_id' => $unit->id,
        ]);

        // 1. Uji GradeController index & edit
        $responseGradeIndex = $this->actingAs($guruUser)->get(route('teacher.grades.index'));
        $responseGradeIndex->assertOk();

        $responseGradeEdit = $this->actingAs($guruUser)->get(route('teacher.grades.edit', $jadwal));
        $responseGradeEdit->assertOk();
        $responseGradeEdit->assertSee('Fatimah Az Zahra');

        // 2. Uji AttendanceController index & create
        $responseAttendanceIndex = $this->actingAs($guruUser)->get(route('teacher.attendance.index'));
        $responseAttendanceIndex->assertOk();

        $responseAttendanceCreate = $this->actingAs($guruUser)->get(route('teacher.attendance.create', [
            'schedule_id' => $jadwal->id,
            'tanggal' => now()->toDateString(),
        ]));
        $responseAttendanceCreate->assertOk();
        $responseAttendanceCreate->assertSee('Fatimah Az Zahra');
    }

    public function test_academic_report_controller_pdf_exports_under_strict_mode(): void
    {
        $admin = User::create([
            'name' => 'Pengurus Akademik Opt',
            'email' => 'akademik.opt@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-REPORT'],
            ['nama_unit' => 'MTs Nurul Khidmah Report', 'jenjang' => 'MTs', 'is_active' => true]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Wali Kelas Opt',
            'email' => 'wali.opt@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'NIY-OPT-03',
            'nama_lengkap' => 'Ust. Wali Kelas Opt, S.Pd.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '9A-OPT',
            'nama_kelas' => 'Kelas 9A Optimasi',
            'tingkat_kelas' => '9',
            'wali_kelas_id' => $pengajar->id,
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'HAD-01',
            'nama_mapel' => 'Hadits Arbain',
            'educational_unit_id' => $unit->id,
        ]);

        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $pengajar->id,
            'hari' => 'Rabu',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruangan' => 'Ruang 9A',
        ]);

        $santri = Santri::create([
            'nis' => '26MTS077',
            'nama_lengkap' => 'Muhammad Zaidan Optimizer',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
            'kelas_id' => $kelas->id,
            'educational_unit_id' => $unit->id,
        ]);

        Nilai::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'nilai_tugas' => 95,
            'nilai_uts' => 90,
            'nilai_uas' => 95,
            'nilai_akhir' => 93.5,
            'grade' => 'A',
            'keterangan' => 'Mumtaz',
        ]);

        Absensi::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'status_kehadiran' => 'Hadir',
        ]);

        RaporSemester::create([
            'santri_id' => $santri->id,
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'nilai_sikap' => 'Sangat Baik (A)',
            'catatan_wali_kelas' => 'Pertahankan nilai dan akhlaq mulia.',
            'status_publikasi' => 'published',
        ]);

        // 1. Download Leger Nilai PDF
        $resLeger = $this->actingAs($admin)->get(route('admin.reports.grades-pdf', [
            'kelas_id' => $kelas->id,
            'download' => 1,
        ]));
        $resLeger->assertOk();
        $this->assertStringContainsString('application/pdf', $resLeger->headers->get('Content-Type'));

        // 2. Download Rekap Presensi PDF
        $resAbsen = $this->actingAs($admin)->get(route('admin.reports.attendance-pdf', [
            'kelas_id' => $kelas->id,
            'download' => 1,
        ]));
        $resAbsen->assertOk();
        $this->assertStringContainsString('application/pdf', $resAbsen->headers->get('Content-Type'));

        // 3. Download Lembar Rapor Santri PDF
        $resRapor = $this->actingAs($admin)->get(route('admin.reports.santri-rapor-pdf', [
            'santri' => $santri->id,
            'download' => 1,
        ]));
        $resRapor->assertOk();
        $this->assertStringContainsString('application/pdf', $resRapor->headers->get('Content-Type'));
    }

    public function test_academic_rapor_service_build_data_uses_eager_loading_and_in_memory_aggregates(): void
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-SERV'],
            ['nama_unit' => 'MTs Nurul Khidmah Service', 'jenjang' => 'MTs', 'is_active' => true]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Guru Service',
            'email' => 'guru.service@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'NIY-SERV-04',
            'nama_lengkap' => 'Guru Service, M.Pd.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '9B-SERV',
            'nama_kelas' => 'Kelas 9B Service',
            'tingkat_kelas' => '9',
            'wali_kelas_id' => $pengajar->id,
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'NAHWU-01',
            'nama_mapel' => 'Nahwu Jurumiyah',
            'educational_unit_id' => $unit->id,
        ]);

        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $pengajar->id,
            'hari' => 'Kamis',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruangan' => 'Ruang 9B',
        ]);

        $santri = Santri::create([
            'nis' => '26MTS066',
            'nama_lengkap' => 'Hasan Al Banna',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
            'kelas_id' => $kelas->id,
            'educational_unit_id' => $unit->id,
        ]);

        Nilai::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'nilai_tugas' => 85,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
            'nilai_akhir' => 87.0,
            'grade' => 'A',
            'keterangan' => 'Baik',
        ]);

        Absensi::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'status_kehadiran' => 'Hadir',
        ]);

        Absensi::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'tanggal' => now()->subDay()->toDateString(),
            'status_kehadiran' => 'Terlambat',
        ]);

        $raporService = app(AcademicRaporService::class);
        $raporData = $raporService->buildSantriRaporData($santri, $ta);

        $this->assertSame(1, $raporData['totalHadir']);
        $this->assertSame(1, $raporData['totalTerlambat']);
        $this->assertSame(0, $raporData['totalBolos']);
        $this->assertSame(0, $raporData['totalAlpa']);
        $this->assertSame(87.0, $raporData['rataRata']);
        $this->assertSame(1, $raporData['gradedCount']);
    }
}
