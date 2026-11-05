<?php

namespace Tests\Feature;

use App\Livewire\Teacher\BatchAttendanceInput;
use App\Livewire\Teacher\BatchGradeInput;
use App\Models\Absensi;
use App\Models\EducationalUnit;
use App\Models\GradeComponent;
use App\Models\GradePredicate;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\RaporSemester;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class BatchUpsertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Strict Mode pencegahan lazy loading harus aktif selama pengujian
        Model::preventLazyLoading(true);
    }

    private function createAcademicEnvironment(int $santriCount = 10): array
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-BATCH'],
            ['nama_unit' => 'MTs Nurul Khidmah Batch', 'jenjang' => 'MTs', 'is_active' => true]
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

        GradePredicate::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'huruf' => 'A'],
            ['batas_bawah' => 85, 'batas_atas' => 100]
        );
        GradePredicate::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'huruf' => 'B'],
            ['batas_bawah' => 75, 'batas_atas' => 84.99]
        );
        GradePredicate::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'huruf' => 'C'],
            ['batas_bawah' => 65, 'batas_atas' => 74.99]
        );
        GradePredicate::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'huruf' => 'D'],
            ['batas_bawah' => 50, 'batas_atas' => 64.99]
        );
        GradePredicate::firstOrCreate(
            ['educational_unit_id' => $unit->id, 'huruf' => 'E'],
            ['batas_bawah' => 0, 'batas_atas' => 49.99]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Ust. Batch Master',
            'email' => 'batch.master@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'NIY-BATCH-01',
            'nama_lengkap' => 'Ust. Batch Master, M.Pd.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '7B-BATCH',
            'nama_kelas' => 'Kelas 7B Batch',
            'tingkat_kelas' => '7',
            'wali_kelas_id' => $pengajar->id,
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'FIQ-BATCH',
            'nama_mapel' => 'Fiqih Ibadah',
            'educational_unit_id' => $unit->id,
        ]);

        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $pengajar->id,
            'hari' => 'Rabu',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '09:30',
            'ruangan' => 'Ruang 7B',
        ]);

        $santris = [];
        for ($i = 1; $i <= $santriCount; $i++) {
            $santris[] = Santri::create([
                'nis' => sprintf('26MTS%03d', $i),
                'nama_lengkap' => sprintf('Santri Uji Ke-%02d', $i),
                'jenis_kelamin' => $i % 2 === 0 ? 'Perempuan' : 'Laki-laki',
                'status_santri' => 'Aktif',
                'kelas_id' => $kelas->id,
                'educational_unit_id' => $unit->id,
            ]);
        }

        return compact('unit', 'ta', 'guruUser', 'pengajar', 'kelas', 'mapel', 'jadwal', 'santris');
    }

    public function test_batch_grade_input_component_saves_and_upserts_10_santris_without_query_explosion(): void
    {
        $env = $this->createAcademicEnvironment(10);
        $guru = $env['guruUser'];
        $jadwal = $env['jadwal'];
        $santris = $env['santris'];

        $test = Livewire::actingAs($guru)
            ->test(BatchGradeInput::class, ['jadwal' => $jadwal]);

        // Pastikan seluruh 10 santri tampil pada komponen
        foreach ($santris as $santri) {
            $test->assertSee($santri->nama_lengkap);
        }

        // Siapkan input nilai untuk 10 santri
        $gradesPayload = [];
        foreach ($santris as $index => $santri) {
            $tugas = 80 + $index; // 80 .. 89
            $uts = 75 + $index;   // 75 .. 84
            $uas = 85;

            $gradesPayload[$santri->id] = [
                'santri_id' => $santri->id,
                'nama_lengkap' => $santri->nama_lengkap,
                'nis' => $santri->nis,
                'nilai_tugas' => $tugas,
                'nilai_uts' => $uts,
                'nilai_uas' => $uas,
                'nilai_akhir' => '',
                'grade' => '-',
                'keterangan' => 'Evaluasi batch santri #'.($index + 1),
            ];
        }

        $test->set('grades', $gradesPayload);

        // Pantau jumlah query selama pemanggilan method save()
        DB::flushQueryLog();
        DB::enableQueryLog();

        $test->call('save');

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Verifikasi tidak terjadi N+1 query explosion saat save 10 santri
        // Query: 1 query RaporSemester eager check + 1 query Nilai::upsert + relasi internal Livewire re-render (<= 8 query)
        $this->assertLessThanOrEqual(8, count($queryLog), 'Query count must remain minimal due to batch upsert and eager loading.');

        // Verifikasi 10 data nilai tersimpan di database
        $this->assertDatabaseCount('nilais', 10);

        // Verifikasi nilai santri pertama: Tugas 80, UTS 75, UAS 85 => (80*30 + 75*30 + 85*40)/100 = (2400 + 2250 + 3400)/100 = 80.5 (Grade B)
        $firstNilai = Nilai::where('santri_id', $santris[0]->id)->where('jadwal_pelajaran_id', $jadwal->id)->first();
        $this->assertNotNull($firstNilai);
        $this->assertEquals(80.5, (float) $firstNilai->nilai_akhir);
        $this->assertEquals('B', $firstNilai->grade);

        // Verifikasi upsert idempotency / update:
        // Update nilai santri pertama menjadi 95 di semua komponen
        $gradesPayload[$santris[0]->id]['nilai_tugas'] = 95;
        $gradesPayload[$santris[0]->id]['nilai_uts'] = 95;
        $gradesPayload[$santris[0]->id]['nilai_uas'] = 95;

        $test->set('grades', $gradesPayload);
        $test->call('save');

        // Jumlah baris di database tetap 10 (tidak terduplikasi menjadi 11)
        $this->assertDatabaseCount('nilais', 10);

        $firstNilaiUpdated = Nilai::where('santri_id', $santris[0]->id)->where('jadwal_pelajaran_id', $jadwal->id)->first();
        $this->assertEquals(95.0, (float) $firstNilaiUpdated->nilai_akhir);
        $this->assertEquals('A', $firstNilaiUpdated->grade);
    }

    public function test_batch_attendance_input_component_saves_and_upserts_10_santris_without_query_explosion(): void
    {
        $env = $this->createAcademicEnvironment(10);
        $guru = $env['guruUser'];
        $jadwal = $env['jadwal'];
        $santris = $env['santris'];
        $tanggal = '2026-11-04';

        $test = Livewire::actingAs($guru)
            ->test(BatchAttendanceInput::class, ['jadwal' => $jadwal, 'tanggal' => $tanggal]);

        // Verifikasi santri dirender
        foreach ($santris as $santri) {
            $test->assertSee($santri->nama_lengkap);
        }

        // Uji aksi 'markAll' Hadir
        $test->call('markAll', 'Hadir');

        // Modifikasi beberapa santri dengan status berbeda
        $test->set("attendances.{$santris[1]->id}.status", 'Izin');
        $test->set("attendances.{$santris[1]->id}.keterangan", 'Izin kepulangan keluarga');

        $test->set("attendances.{$santris[2]->id}.status", 'Sakit');
        $test->set("attendances.{$santris[2]->id}.keterangan", 'Dirawat di poskestren');

        $test->set("attendances.{$santris[3]->id}.status", 'Terlambat');
        $test->set("attendances.{$santris[3]->id}.keterangan", 'Terlambat 10 menit');

        // Pantau jumlah query selama pemanggilan method save()
        DB::flushQueryLog();
        DB::enableQueryLog();

        $test->call('save');

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Verifikasi tidak ada query explosion (1 upsert tunggal + relasi internal Livewire re-render <= 8 query)
        $this->assertLessThanOrEqual(8, count($queryLog), 'Query count must remain minimal due to batch upsert.');

        // Verifikasi 10 data absensi tersimpan
        $this->assertDatabaseCount('absensis', 10);

        $this->assertDatabaseHas('absensis', [
            'santri_id' => $santris[1]->id,
            'jadwal_pelajaran_id' => $jadwal->id,
            'status_kehadiran' => 'Izin',
            'keterangan' => 'Izin kepulangan keluarga',
        ]);

        $this->assertDatabaseHas('absensis', [
            'santri_id' => $santris[2]->id,
            'jadwal_pelajaran_id' => $jadwal->id,
            'status_kehadiran' => 'Sakit',
        ]);

        $this->assertDatabaseHas('absensis', [
            'santri_id' => $santris[3]->id,
            'jadwal_pelajaran_id' => $jadwal->id,
            'status_kehadiran' => 'Terlambat',
        ]);

        // Uji upsert update: ubah santri ke-3 menjadi 'Hadir'
        $test->set("attendances.{$santris[3]->id}.status", 'Hadir');
        $test->call('save');

        // Jumlah baris tetap 10
        $this->assertDatabaseCount('absensis', 10);
        $this->assertDatabaseHas('absensis', [
            'santri_id' => $santris[3]->id,
            'jadwal_pelajaran_id' => $jadwal->id,
            'status_kehadiran' => 'Hadir',
        ]);
    }

    public function test_batch_grade_input_excludes_approved_santri_and_blocks_unauthorized_upsert(): void
    {
        $env = $this->createAcademicEnvironment(3);
        $guru = $env['guruUser'];
        $jadwal = $env['jadwal'];
        $santris = $env['santris'];
        $ta = $env['ta'];
        $kelas = $env['kelas'];

        // Santri pertama status rapornya 'approved'
        RaporSemester::create([
            'santri_id' => $santris[0]->id,
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'status_publikasi' => 'approved',
            'approved_at' => now(),
        ]);

        $test = Livewire::actingAs($guru)
            ->test(BatchGradeInput::class, ['jadwal' => $jadwal]);

        // Santri pertama tidak boleh ada di array grades yang dapat diedit
        $grades = $test->get('grades');
        $this->assertArrayNotHasKey($santris[0]->id, $grades);
        $this->assertArrayHasKey($santris[1]->id, $grades);
        $this->assertArrayHasKey($santris[2]->id, $grades);

        // Jika terjadi manipulasi state di sisi client untuk menyisipkan santri yang approved
        $manipulatedGrades = $grades;
        $manipulatedGrades[$santris[0]->id] = [
            'santri_id' => $santris[0]->id,
            'nama_lengkap' => $santris[0]->nama_lengkap,
            'nis' => $santris[0]->nis,
            'nilai_tugas' => 99,
            'nilai_uts' => 99,
            'nilai_uas' => 99,
            'keterangan' => 'Injeksi nilai santri terkunci',
        ];

        $test->set('grades', $manipulatedGrades);

        // Pemanggilan save() harus melempar 403 Forbidden via NilaiPolicy
        $test->call('save')
            ->assertForbidden();
    }

    public function test_batch_attendance_input_excludes_approved_santri_and_blocks_unauthorized_upsert(): void
    {
        $env = $this->createAcademicEnvironment(3);
        $guru = $env['guruUser'];
        $jadwal = $env['jadwal'];
        $santris = $env['santris'];
        $ta = $env['ta'];
        $kelas = $env['kelas'];

        // Kunci rapor santri pertama
        RaporSemester::create([
            'santri_id' => $santris[0]->id,
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'status_publikasi' => 'approved',
            'approved_at' => now(),
        ]);

        $test = Livewire::actingAs($guru)
            ->test(BatchAttendanceInput::class, ['jadwal' => $jadwal, 'tanggal' => '2026-11-04']);

        $attendances = $test->get('attendances');
        $this->assertArrayNotHasKey($santris[0]->id, $attendances);
        $this->assertArrayHasKey($santris[1]->id, $attendances);

        // Coba injeksi santri yang terkunci
        $manipulatedAttendances = $attendances;
        $manipulatedAttendances[$santris[0]->id] = [
            'santri_id' => $santris[0]->id,
            'nama_lengkap' => $santris[0]->nama_lengkap,
            'nis' => $santris[0]->nis,
            'status' => 'Bolos',
            'keterangan' => 'Mencoba manipulasi absen santri approved',
        ];

        $test->set('attendances', $manipulatedAttendances);

        // Harus ditolak dengan 403 Forbidden via AbsensiPolicy
        $test->call('save')
            ->assertForbidden();
    }

    public function test_unauthorized_teacher_cannot_mount_batch_components_on_other_schedules(): void
    {
        $env = $this->createAcademicEnvironment(2);
        $jadwal = $env['jadwal'];

        // Guru lain yang tidak mengajar di jadwal tersebut
        $guruLain = User::create([
            'name' => 'Ust. Orang Asing',
            'email' => 'asing@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);
        $pengajarLain = Pengajar::create([
            'user_id' => $guruLain->id,
            'nomor_identitas' => 'NIY-ASING-01',
            'nama_lengkap' => 'Ust. Orang Asing, S.Pd.I.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        Livewire::actingAs($guruLain)
            ->test(BatchGradeInput::class, ['jadwal' => $jadwal])
            ->assertForbidden();

        Livewire::actingAs($guruLain)
            ->test(BatchAttendanceInput::class, ['jadwal' => $jadwal, 'tanggal' => '2026-11-04'])
            ->assertForbidden();
    }
}
