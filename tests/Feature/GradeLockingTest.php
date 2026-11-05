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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GradeLockingTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicFixture(): array
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-LOCK'],
            ['nama_unit' => 'MTs Nurul Khidmah Locking', 'jenjang' => 'MTs', 'is_active' => true]
        );

        GradeComponent::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'nama_komponen' => 'Tugas'],
            ['bobot_persen' => 30]
        );
        GradeComponent::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'nama_komponen' => 'UTS'],
            ['bobot_persen' => 30]
        );
        GradeComponent::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'nama_komponen' => 'UAS'],
            ['bobot_persen' => 40]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Ust. Zaidan Pengajar',
            'email' => 'zaidan.pengajar@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'NIY-LOCK-01',
            'nama_lengkap' => 'Ust. Zaidan, M.Pd.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '7A-LOCK',
            'nama_kelas' => 'Kelas 7A Locking',
            'tingkat_kelas' => '7',
            'wali_kelas_id' => $pengajar->id,
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'TAH-01',
            'nama_mapel' => 'Tahfidz Al-Quran',
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
            'ruangan' => 'Ruang Tahfidz',
        ]);

        $santri = Santri::create([
            'nis' => '26MTS001',
            'nama_lengkap' => 'Ahmad Fathanah',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
            'kelas_id' => $kelas->id,
            'educational_unit_id' => $unit->id,
        ]);

        return compact('unit', 'ta', 'guruUser', 'pengajar', 'kelas', 'mapel', 'jadwal', 'santri');
    }

    public function test_teacher_cannot_update_grade_when_rapor_is_approved_returns_403(): void
    {
        $fixture = $this->createAcademicFixture();
        $guru = $fixture['guruUser'];
        $jadwal = $fixture['jadwal'];
        $santri = $fixture['santri'];
        $ta = $fixture['ta'];
        $kelas = $fixture['kelas'];

        // Buat nilai awal
        Nilai::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'nilai_tugas' => 80,
            'nilai_uts' => 80,
            'nilai_uas' => 80,
            'nilai_akhir' => 80.0,
            'grade' => 'B',
            'keterangan' => 'Nilai Awal',
        ]);

        // Kunci rapor dengan status 'approved'
        RaporSemester::create([
            'santri_id' => $santri->id,
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'status_publikasi' => 'approved',
            'approved_by' => null,
            'approved_at' => now(),
        ]);

        // Guru mencoba mengubah nilai santri yang sudah berstatus 'approved'
        $response = $this->actingAs($guru)->put(route('teacher.grades.update', $jadwal), [
            'nilai' => [
                $santri->id => [
                    'tugas' => 95,
                    'uts' => 95,
                    'uas' => 95,
                    'keterangan' => 'Mencoba manipulasi nilai setelah disetujui',
                ],
            ],
        ]);

        // Harus ditolak dengan status 403 Forbidden
        $response->assertForbidden();

        // Nilai di database tidak boleh berubah
        $nilaiDb = Nilai::where('jadwal_pelajaran_id', $jadwal->id)->where('santri_id', $santri->id)->first();
        $this->assertEquals(80.0, (float) $nilaiDb->nilai_akhir);
    }

    public function test_teacher_cannot_update_attendance_when_rapor_is_approved_returns_403(): void
    {
        $fixture = $this->createAcademicFixture();
        $guru = $fixture['guruUser'];
        $jadwal = $fixture['jadwal'];
        $santri = $fixture['santri'];
        $ta = $fixture['ta'];
        $kelas = $fixture['kelas'];

        // Kunci rapor dengan status 'approved'
        RaporSemester::create([
            'santri_id' => $santri->id,
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'status_publikasi' => 'approved',
            'approved_by' => null,
            'approved_at' => now(),
        ]);

        // Guru mencoba mengubah/mengisi presensi santri yang rapornya sudah approved
        $response = $this->actingAs($guru)->post(route('teacher.attendance.store'), [
            'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => now()->toDateString(),
            'absensi' => [
                $santri->id => [
                    'status' => 'Hadir',
                    'keterangan' => 'Mencoba absen setelah disetujui',
                ],
            ],
        ]);

        // Harus ditolak dengan 403 Forbidden
        $response->assertForbidden();
    }

    public function test_teacher_can_update_grade_and_attendance_when_rapor_is_draft(): void
    {
        $fixture = $this->createAcademicFixture();
        $guru = $fixture['guruUser'];
        $jadwal = $fixture['jadwal'];
        $santri = $fixture['santri'];
        $ta = $fixture['ta'];
        $kelas = $fixture['kelas'];

        // Rapor berstatus 'draft' (belum disetujui pimpinan/kepala sekolah)
        RaporSemester::create([
            'santri_id' => $santri->id,
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'status_publikasi' => 'draft',
        ]);

        // 1. Update nilai berhasil
        $responseGrade = $this->actingAs($guru)->put(route('teacher.grades.update', $jadwal), [
            'nilai' => [
                $santri->id => [
                    'tugas' => 90,
                    'uts' => 85,
                    'uas' => 95,
                    'keterangan' => 'Sangat aktif dalam tahfidz',
                ],
            ],
        ]);

        $responseGrade->assertRedirect(route('teacher.grades.index'));
        $this->assertDatabaseHas('nilais', [
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'nilai_tugas' => 90,
            'nilai_uts' => 85,
            'nilai_uas' => 95,
        ]);

        // 2. Update absensi berhasil
        $tanggal = now()->toDateString();
        $responseAttendance = $this->actingAs($guru)->post(route('teacher.attendance.store'), [
            'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => $tanggal,
            'absensi' => [
                $santri->id => [
                    'status' => 'Hadir',
                    'keterangan' => 'Tepat waktu',
                ],
            ],
        ]);

        $responseAttendance->assertRedirect(route('teacher.attendance.index'));
        $this->assertDatabaseHas('absensis', [
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'status_kehadiran' => 'Hadir',
        ]);
    }

    public function test_unauthorized_teacher_cannot_update_grade_on_other_schedule(): void
    {
        $fixture = $this->createAcademicFixture();
        $jadwal = $fixture['jadwal'];
        $santri = $fixture['santri'];

        $guruLainUser = User::create([
            'name' => 'Guru Lain',
            'email' => 'gurulain@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        Pengajar::create([
            'user_id' => $guruLainUser->id,
            'nomor_identitas' => 'NIY-OTHER-99',
            'nama_lengkap' => 'Ust. Guru Lain, S.Pd.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($guruLainUser)->put(route('teacher.grades.update', $jadwal), [
            'nilai' => [
                $santri->id => [
                    'tugas' => 90,
                    'uts' => 90,
                    'uas' => 90,
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_approval_route_allows_kepala_sekolah_or_pimpinan_to_lock_rapor(): void
    {
        $fixture = $this->createAcademicFixture();
        $santri = $fixture['santri'];
        $kelas = $fixture['kelas'];
        $ta = $fixture['ta'];

        $kepalaSekolah = User::create([
            'name' => 'Kepala Sekolah Ust. Faruq',
            'email' => 'kepala@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KEPALA_SEKOLAH,
            'email_verified_at' => now(),
        ]);

        // 1. Approval per santri oleh Kepala Sekolah
        $resApproveSantri = $this->actingAs($kepalaSekolah)->post(route('admin.reports.approve-santri', $santri->id), [
            'tahun_ajaran_id' => $ta->id,
        ]);
        $resApproveSantri->assertRedirect();

        $this->assertDatabaseHas('rapor_semesters', [
            'santri_id' => $santri->id,
            'tahun_ajaran_id' => $ta->id,
            'status_publikasi' => 'approved',
            'approved_by' => $kepalaSekolah->id,
        ]);

        // 2. Approval per kelas oleh Pimpinan
        $pimpinan = User::create([
            'name' => 'K.H. Pimpinan Pondok',
            'email' => 'pimpinan@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PIMPINAN,
            'email_verified_at' => now(),
        ]);

        $resApproveClass = $this->actingAs($pimpinan)->post(route('admin.reports.approve-class', $kelas->id), [
            'tahun_ajaran_id' => $ta->id,
        ]);
        $resApproveClass->assertRedirect();

        $this->assertDatabaseHas('rapor_semesters', [
            'kelas_id' => $kelas->id,
            'status_publikasi' => 'approved',
            'approved_by' => $pimpinan->id,
        ]);

        // 3. Tenaga Pengajar (Guru biasa) tidak berhak menyetujui approval (403 Forbidden)
        $guru = $fixture['guruUser'];
        $resGuruReject = $this->actingAs($guru)->post(route('admin.reports.approve-santri', $santri->id));
        $resGuruReject->assertForbidden();
    }
}
