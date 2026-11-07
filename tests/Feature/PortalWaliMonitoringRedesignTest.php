<?php

use App\Models\Absensi;
use App\Models\EducationalUnit;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\RaporSemester;
use App\Models\RiwayatKelasSantri;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => User::ROLE_PENGURUS_AKADEMIK,
    ]);

    $this->kepalaSekolah = User::factory()->create([
        'role' => User::ROLE_KEPALA_SEKOLAH,
    ]);

    $this->waliUser = User::factory()->create([
        'role' => User::ROLE_SANTRI,
        'email' => 'wali.monitoring@example.com',
    ]);

    $this->taArsip = TahunAjaran::create([
        'nama_tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
        'status_aktif' => false,
        'tanggal_mulai' => '2026-01-01',
        'tanggal_selesai' => '2026-06-30',
    ]);

    $this->taAktif = TahunAjaran::create([
        'nama_tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'status_aktif' => true,
        'tanggal_mulai' => '2026-07-01',
        'tanggal_selesai' => '2026-12-31',
    ]);

    $this->unit = EducationalUnit::first() ?? EducationalUnit::create([
        'kode_unit' => 'MTS-MON',
        'nama_unit' => 'MTs Nurul Khidmah',
        'jenjang' => 'MTs',
        'is_active' => true,
    ]);

    $teacherUser = User::factory()->create(['role' => User::ROLE_TENAGA_PENGAJAR]);
    $this->pengajar = Pengajar::create([
        'user_id' => $teacherUser->id,
        'nomor_identitas' => '198801012020',
        'nama_lengkap' => 'Ustaz Ahmad Fauzi',
        'jenis_kelamin' => 'Laki-laki',
        'status_pengajar' => 'Aktif',
    ]);

    $this->kelas7Arsip = Kelas::create([
        'kode_kelas' => '7A-2025',
        'nama_kelas' => 'Kelas 7A',
        'tingkat_kelas' => '7',
        'tahun_ajaran_id' => $this->taArsip->id,
        'wali_kelas_id' => $this->pengajar->id,
        'educational_unit_id' => $this->unit->id,
    ]);

    $this->kelas8Aktif = Kelas::create([
        'kode_kelas' => '8A-2026',
        'nama_kelas' => 'Kelas 8A',
        'tingkat_kelas' => '8',
        'tahun_ajaran_id' => $this->taAktif->id,
        'wali_kelas_id' => $this->pengajar->id,
        'educational_unit_id' => $this->unit->id,
    ]);

    $this->mapelFiqih = MataPelajaran::create([
        'educational_unit_id' => $this->unit->id,
        'kode_mapel' => 'FQH-08',
        'nama_mapel' => 'Fiqih Muamalah',
    ]);

    $this->santri = Santri::create([
        'user_id' => $this->waliUser->id,
        'kelas_id' => $this->kelas8Aktif->id,
        'educational_unit_id' => $this->unit->id,
        'nis' => '20250099',
        'nisn' => '0099887766',
        'nama_lengkap' => 'Muhammad Nabil Al-Farisi',
        'nama_wali' => 'H.Ridwan',
        'status_santri' => 'Aktif',
        'jenis_kelamin' => 'Laki-laki',
    ]);
});

test('compact filter toolbar replaces dominant blue panel and exposes searchable santri combobox with count', function () {
    $response = $this->actingAs($this->admin)->get(route('wali.dashboard'));

    $response->assertOk();
    $response->assertDontSee('bg-indigo-50', false);
    $response->assertSee('id="santri-combobox-panel"', false);
    $response->assertSee('id="santri-realtime-search"', false);
    $response->assertSee('id="portal-advanced-filters"', false);
    $response->assertSee('1 santri');
});

test('student profile card is neutral without green gradient and read-only mode uses compact tooltip badge and contextual tabs', function () {
    $response = $this->actingAs($this->admin)->get(route('wali.dashboard'));

    $response->assertOk();
    $response->assertDontSee('from-primary-500 via-primary-600', false);
    $response->assertSee('Mode Pantau · Read-only');
    $response->assertSee('interestfor="readonly-mode-tooltip"', false);
    $response->assertSee('popover="hint"', false);
    $response->assertSee('Muhammad Nabil Al-Farisi');
    $response->assertSee('20250099');
    $response->assertSee('H.Ridwan');
    $response->assertSee('Unduh Rapor');
    $response->assertSee('Ringkasan &amp; Jadwal', false);
    $response->assertSee('Detail Presensi');
    $response->assertSee('Rincian Nilai');
});

test('empty grades display Belum ada nilai instead of misleading 0.00 and omit chart canvas', function () {
    $response = $this->actingAs($this->waliUser)->get(route('wali.dashboard'));

    $response->assertOk();
    $response->assertSee('Belum ada nilai');
    $response->assertDontSee('0.00');
    $response->assertDontSee('santriSubjectScoresChart', false);

    $gradesResponse = $this->actingAs($this->waliUser)->get(route('wali.grades'));
    $gradesResponse->assertOk();
    $gradesResponse->assertSee('Belum ada nilai');
    $gradesResponse->assertDontSee('0.00');
});

test('academic kpis show attendance denominator, valid average grade, and render chartjs when grade data exists', function () {
    $jadwal = JadwalPelajaran::create([
        'tahun_ajaran_id' => $this->taAktif->id,
        'kelas_id' => $this->kelas8Aktif->id,
        'mata_pelajaran_id' => $this->mapelFiqih->id,
        'pengajar_id' => $this->pengajar->id,
        'hari' => 'Senin',
        'waktu_mulai' => '07:30:00',
        'waktu_selesai' => '09:00:00',
        'ruang' => 'Ruang 8A',
    ]);

    foreach (['2026-08-03' => 'Hadir', '2026-08-10' => 'Hadir', '2026-08-17' => 'Hadir', '2026-08-24' => 'Izin', '2026-08-31' => 'Sakit'] as $tgl => $status) {
        Absensi::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'santri_id' => $this->santri->id,
            'tanggal' => $tgl,
            'status_kehadiran' => $status,
        ]);
    }

    Nilai::create([
        'jadwal_pelajaran_id' => $jadwal->id,
        'santri_id' => $this->santri->id,
        'nilai_tugas' => 85,
        'nilai_uts' => 88,
        'nilai_uas' => 92,
        'nilai_akhir' => 88.70,
        'grade' => 'A',
        'keterangan' => 'Sangat menguasai materi',
    ]);

    RaporSemester::create([
        'santri_id' => $this->santri->id,
        'kelas_id' => $this->kelas8Aktif->id,
        'tahun_ajaran_id' => $this->taAktif->id,
        'nilai_sikap' => 'Sangat Baik (A)',
        'catatan_wali_kelas' => 'Istiqomah dalam belajar.',
    ]);

    $response = $this->actingAs($this->admin)->get(route('wali.dashboard', ['santri_id' => $this->santri->id]));

    $response->assertOk();
    $response->assertSee('3 dari 5 pertemuan');
    $response->assertSee('Izin 1 · Sakit 1');
    $response->assertSee('88.70');
    $response->assertDontSee('Belum ada nilai');
    $response->assertSee('santriSubjectScoresChart', false);

    // Schedule appears BEFORE Catatan Wali Kelas and exposes Hari Ini & Mingguan tabs
    $response->assertSeeInOrder([
        'Ringkasan Akademik',
        'Jadwal Pelajaran',
        'Hari Ini',
        'Mingguan',
        'Fiqih Muamalah',
        'Ustaz Ahmad Fauzi',
        'Ruang 8A',
        'Catatan Wali Kelas',
    ]);
});

test('archive period displays Arsip badge, syncs historical class data, and preserves santri_id for kepala sekolah', function () {
    RiwayatKelasSantri::create([
        'santri_id' => $this->santri->id,
        'kelas_lama_id' => $this->kelas7Arsip->id,
        'kelas_baru_id' => $this->kelas8Aktif->id,
        'tahun_ajaran_id' => $this->taAktif->id,
        'jenis_aksi' => 'Naik Kelas',
    ]);

    $jadwalArsip = JadwalPelajaran::create([
        'tahun_ajaran_id' => $this->taArsip->id,
        'kelas_id' => $this->kelas7Arsip->id,
        'mata_pelajaran_id' => $this->mapelFiqih->id,
        'pengajar_id' => $this->pengajar->id,
        'hari' => 'Selasa',
        'waktu_mulai' => '08:00:00',
        'waktu_selesai' => '09:30:00',
        'ruang' => 'Gedung Ula 1',
    ]);

    Nilai::create([
        'jadwal_pelajaran_id' => $jadwalArsip->id,
        'santri_id' => $this->santri->id,
        'nilai_tugas' => 80,
        'nilai_uts' => 80,
        'nilai_uas' => 80,
        'nilai_akhir' => 80.00,
        'grade' => 'B',
    ]);

    $response = $this->actingAs($this->kepalaSekolah)->get(route('wali.dashboard', [
        'santri_id' => $this->santri->id,
        'tahun_ajaran_id' => $this->taArsip->id,
    ]));

    $response->assertOk();
    $response->assertSee('Arsip');
    $response->assertSee('Kelas 7A');
    $response->assertSee('Gedung Ula 1');
    $response->assertSee('80.00');
    // Contextual navigation links preserve santri_id & tahun_ajaran_id for Kepala Sekolah
    $response->assertSee('santri_id='.$this->santri->id, false);
    $response->assertSee('tahun_ajaran_id='.$this->taArsip->id, false);
});
