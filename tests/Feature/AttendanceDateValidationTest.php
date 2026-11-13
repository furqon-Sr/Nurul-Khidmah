<?php

namespace Tests\Feature;

use App\Livewire\Teacher\BatchAttendanceInput;
use App\Models\EducationalUnit;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceDateValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createFixture(string $hari = 'Senin'): array
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-DATE'],
            ['nama_unit' => 'MTs Nurul Khidmah Date', 'jenjang' => 'MTs', 'is_active' => true]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Ust. Validasi Tanggal',
            'email' => 'validasi.tanggal@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'nomor_identitas' => 'NIY-DATE-01',
            'nama_lengkap' => 'Ust. Validasi Tanggal, S.Pd.',
            'jenis_kelamin' => 'Laki-laki',
            'status_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '7A-DATE',
            'nama_kelas' => 'Kelas 7A Date',
            'tingkat_kelas' => '7',
            'wali_kelas_id' => $pengajar->id,
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'MP-DATE',
            'nama_mapel' => 'Nahwu Shorof',
            'educational_unit_id' => $unit->id,
        ]);

        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'pengajar_id' => $pengajar->id,
            'hari' => $hari,
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:00',
            'ruang' => 'R-101',
        ]);

        $santri = Santri::create([
            'nis' => '26DATE001',
            'nama_lengkap' => 'Muhammad Rayyan',
            'jenis_kelamin' => 'Laki-laki',
            'status_santri' => 'Aktif',
            'kelas_id' => $kelas->id,
            'educational_unit_id' => $unit->id,
        ]);

        return compact('guruUser', 'jadwal', 'santri');
    }

    public function test_future_date_is_rejected_across_create_store_and_livewire(): void
    {
        $fixture = $this->createFixture('Senin');
        $guru = $fixture['guruUser'];
        $jadwal = $fixture['jadwal'];
        $santri = $fixture['santri'];

        // Cari tanggal masa depan (yang juga hari Senin agar benar-benar menguji aturan masa depan)
        $futureMonday = now()->next(Carbon::MONDAY)->toDateString();

        $this->actingAs($guru)
            ->get(route('teacher.attendance.create', [
                'schedule_id' => $jadwal->id,
                'tanggal' => $futureMonday,
            ]))
            ->assertRedirect(route('teacher.attendance.index'))
            ->assertSessionHasErrors('tanggal');

        $this->actingAs($guru)
            ->post(route('teacher.attendance.store'), [
                'jadwal_pelajaran_id' => $jadwal->id,
                'tanggal' => $futureMonday,
                'absensi' => [
                    $santri->id => ['status' => 'Hadir'],
                ],
            ])
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('absensis', 0);

        Livewire::actingAs($guru)
            ->test(BatchAttendanceInput::class, ['jadwal' => $jadwal])
            ->set('tanggal', $futureMonday)
            ->assertHasErrors(['tanggal'])
            ->call('save')
            ->assertHasErrors(['tanggal']);

        $this->assertDatabaseCount('absensis', 0);
    }

    public function test_mismatched_weekday_is_rejected_across_create_store_and_livewire(): void
    {
        $fixture = $this->createFixture('Senin');
        $guru = $fixture['guruUser'];
        $jadwal = $fixture['jadwal'];
        $santri = $fixture['santri'];

        $pastWednesday = now()->isWednesday()
            ? now()->toDateString()
            : now()->previous(Carbon::WEDNESDAY)->toDateString();

        $this->actingAs($guru)
            ->get(route('teacher.attendance.create', [
                'schedule_id' => $jadwal->id,
                'tanggal' => $pastWednesday,
            ]))
            ->assertRedirect(route('teacher.attendance.index'))
            ->assertSessionHasErrors('tanggal');

        $this->actingAs($guru)
            ->post(route('teacher.attendance.store'), [
                'jadwal_pelajaran_id' => $jadwal->id,
                'tanggal' => $pastWednesday,
                'absensi' => [
                    $santri->id => ['status' => 'Hadir'],
                ],
            ])
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('absensis', 0);

        Livewire::actingAs($guru)
            ->test(BatchAttendanceInput::class, ['jadwal' => $jadwal])
            ->set('tanggal', $pastWednesday)
            ->assertHasErrors(['tanggal'])
            ->call('save')
            ->assertHasErrors(['tanggal']);

        $this->assertDatabaseCount('absensis', 0);
    }

    public function test_matching_past_or_today_weekday_is_accepted_and_prefilled_on_index(): void
    {
        $fixture = $this->createFixture('Selasa');
        $guru = $fixture['guruUser'];
        $jadwal = $fixture['jadwal'];
        $santri = $fixture['santri'];

        $expectedDate = $jadwal->latestMatchingDate();

        $this->assertSame('Selasa', JadwalPelajaran::dayNameForDate($expectedDate));
        $this->assertLessThanOrEqual(now()->toDateString(), $expectedDate);

        $this->actingAs($guru)
            ->get(route('teacher.attendance.index'))
            ->assertOk()
            ->assertSee('value="'.$expectedDate.'"', false)
            ->assertSee('max="'.now()->toDateString().'"', false);

        $this->actingAs($guru)
            ->get(route('teacher.attendance.create', [
                'schedule_id' => $jadwal->id,
                'tanggal' => $expectedDate,
            ]))
            ->assertOk()
            ->assertSee($santri->nama_lengkap);

        Livewire::actingAs($guru)
            ->test(BatchAttendanceInput::class, ['jadwal' => $jadwal, 'tanggal' => $expectedDate])
            ->assertHasNoErrors()
            ->set("attendances.{$santri->id}.status", 'Hadir')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('absensis', [
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $santri->id,
            'tanggal' => $expectedDate,
            'status_kehadiran' => 'Hadir',
        ]);
    }
}
