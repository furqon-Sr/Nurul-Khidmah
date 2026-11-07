<?php

namespace Tests\Feature;

use App\Models\EducationalUnit;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GradeIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Model::preventLazyLoading(true);
    }

    private function makeTeacher(string $name, string $email, string $niy): array
    {
        $user = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make('password'), 'role' => User::ROLE_TENAGA_PENGAJAR, 'email_verified_at' => now()]);
        $pengajar = Pengajar::create(['user_id' => $user->id, 'nomor_identitas' => $niy, 'nama_lengkap' => $name, 'jenis_kelamin' => 'Laki-laki', 'status_aktif' => true]);

        return [$user, $pengajar];
    }

    private function makeSchedule(TahunAjaran $ta, EducationalUnit $unit, Pengajar $pengajar, string $kelasName, string $mapelName, int $activeSantri, int $inactiveSantri = 0): array
    {
        static $seq = 0;
        $seq++;

        $kelas = Kelas::create(['kode_kelas' => "K{$seq}", 'nama_kelas' => $kelasName, 'tingkat_kelas' => '7', 'tahun_ajaran_id' => $ta->id, 'educational_unit_id' => $unit->id]);
        $mapel = MataPelajaran::create(['kode_mapel' => "M{$seq}", 'nama_mapel' => $mapelName, 'educational_unit_id' => $unit->id]);
        $jadwal = JadwalPelajaran::create(['tahun_ajaran_id' => $ta->id, 'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'pengajar_id' => $pengajar->id, 'hari' => 'Senin', 'waktu_mulai' => '08:00', 'waktu_selesai' => '09:00', 'ruangan' => 'R1']);

        $santris = [];
        foreach (range(1, $activeSantri + $inactiveSantri) as $i) {
            if ($activeSantri + $inactiveSantri === 0) {
                break;
            }
            $santris[] = Santri::create([
                'nis' => "S{$seq}-{$i}", 'nama_lengkap' => "Santri {$seq}-{$i}", 'jenis_kelamin' => 'Laki-laki',
                'status_santri' => $i <= $activeSantri ? 'Aktif' : 'Lulus', 'kelas_id' => $kelas->id, 'educational_unit_id' => $unit->id,
            ]);
        }

        return [$jadwal, $santris];
    }

    public function test_progress_distinguishes_no_students_not_started_and_in_progress_and_ignores_other_teachers(): void
    {
        $unit = EducationalUnit::create(['kode_unit' => 'MTS-G', 'nama_unit' => 'MTs G', 'jenjang' => 'MTs', 'is_active' => true]);
        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        [$guru, $pengajar] = $this->makeTeacher('Ust. Satu', 'satu@test.org', 'NIY-1');
        [, $lain] = $this->makeTeacher('Ust. Lain', 'lain@test.org', 'NIY-2');

        $this->makeSchedule($ta, $unit, $pengajar, 'Kelas Kosong', 'Fiqih Kosong', 0);               // tanpa santri → bukan 0%
        $this->makeSchedule($ta, $unit, $pengajar, 'Kelas Baru', 'Aqidah Baru', 2);                    // 0 dari 2 → belum dimulai
        [$jProses, $santriProses] = $this->makeSchedule($ta, $unit, $pengajar, 'Kelas Proses', 'Nahwu Proses', 2, 1); // 1 santri non-aktif diabaikan
        $this->makeSchedule($ta, $unit, $lain, 'Kelas Orang Lain', 'Mapel Rahasia', 3);

        Nilai::create(['jadwal_pelajaran_id' => $jProses->id, 'santri_id' => $santriProses[0]->id, 'nilai_tugas' => 80, 'nilai_uts' => 80, 'nilai_uas' => 80, 'nilai_akhir' => 80, 'grade' => 'B']);
        Nilai::create(['jadwal_pelajaran_id' => $jProses->id, 'santri_id' => $santriProses[2]->id, 'nilai_tugas' => 90, 'nilai_uts' => 90, 'nilai_uas' => 90, 'nilai_akhir' => 90, 'grade' => 'A']); // santri non-aktif

        $response = $this->actingAs($guru)->get(route('teacher.grades.index'));

        $response->assertOk()
            ->assertSee('Pengolahan Nilai')
            ->assertSee('Fiqih Kosong')
            ->assertSee('Tidak ada santri aktif yang perlu dinilai')
            ->assertSee('0 dari 2 santri selesai dinilai')
            ->assertSee('Mulai penilaian')
            ->assertSee('1 dari 2 santri selesai dinilai')
            ->assertSee('Lanjutkan penilaian')
            ->assertSee('1 penugasan belum memiliki santri aktif')
            ->assertDontSee('Mapel Rahasia')
            ->assertDontSee('0 dari 0');

        $summary = $response->viewData('summary');
        $this->assertSame(3, $summary['schedules']);
        $this->assertSame(3, $summary['pending_students']);   // (2-0) + (2-1)
        $this->assertSame(1, $summary['complete_entries']);
        $this->assertSame(4, $summary['expected_entries']);
        $this->assertSame(2, $summary['needs_action']);
        $this->assertSame(1, $summary['without_students']);

        $byMapel = collect($response->viewData('items'))->keyBy('mapel');
        $this->assertSame('no_students', $byMapel['Fiqih Kosong']['status']);
        $this->assertNull($byMapel['Fiqih Kosong']['percent']);
        $this->assertSame('not_started', $byMapel['Aqidah Baru']['status']);
        $this->assertSame('in_progress', $byMapel['Nahwu Proses']['status']);
        $this->assertSame(50, $byMapel['Nahwu Proses']['percent']);
    }

    public function test_done_status_and_teacher_name_hidden_for_teacher_but_shown_for_admin(): void
    {
        $unit = EducationalUnit::create(['kode_unit' => 'MTS-H', 'nama_unit' => 'MTs H', 'jenjang' => 'MTs', 'is_active' => true]);
        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        [$guru, $pengajar] = $this->makeTeacher('Ust. Terlihat Admin', 'tampak@test.org', 'NIY-3');
        $pengajar->update(['nama_lengkap' => 'Nama Pengajar Pada Kartu']);
        [$jadwal, $santris] = $this->makeSchedule($ta, $unit, $pengajar, 'Kelas Tuntas', 'Mapel Tuntas', 1);
        Nilai::create(['jadwal_pelajaran_id' => $jadwal->id, 'santri_id' => $santris[0]->id, 'nilai_tugas' => 70, 'nilai_uts' => 70, 'nilai_uas' => 70, 'nilai_akhir' => 70, 'grade' => 'C']);

        $this->actingAs($guru)->get(route('teacher.grades.index'))
            ->assertOk()->assertSee('Kelola nilai')->assertSee('Semua penilaian pada periode ini sudah selesai.')
            ->assertDontSee('Nama Pengajar Pada Kartu');

        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin)->get(route('teacher.grades.index'))
            ->assertOk()->assertSee('Nama Pengajar Pada Kartu');
    }
}
