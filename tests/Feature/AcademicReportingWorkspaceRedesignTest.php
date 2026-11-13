<?php

use App\Livewire\Admin\RaporSantriSearch;
use App\Models\AdminActivityLog;
use App\Models\EducationalUnit;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\RaporSemester;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->kepalaSekolah = User::factory()->create([
        'name' => 'Ustaz H. Faruq Kepala Madrasah',
        'role' => User::ROLE_KEPALA_SEKOLAH,
    ]);

    $this->adminAkademik = User::factory()->create([
        'name' => 'Staf Akademik TU',
        'role' => User::ROLE_PENGURUS_AKADEMIK,
    ]);

    $this->waliKelasUser = User::factory()->create([
        'name' => 'Ustaz Zaidan Wali Kelas',
        'role' => User::ROLE_TENAGA_PENGAJAR,
    ]);

    $this->pengajar = Pengajar::create([
        'user_id' => $this->waliKelasUser->id,
        'nomor_identitas' => 'NIY-2026-001',
        'nama_lengkap' => 'Ustaz Zaidan, M.Pd.',
        'jenis_kelamin' => 'Laki-laki',
        'status_pengajar' => 'Aktif',
    ]);

    $this->taAktif = TahunAjaran::create([
        'nama_tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'status_aktif' => true,
        'tanggal_mulai' => '2026-07-01',
        'tanggal_selesai' => '2026-12-31',
    ]);

    $this->unit = EducationalUnit::first() ?? EducationalUnit::create([
        'kode_unit' => 'MTS-RPT',
        'nama_unit' => 'MTs Nurul Khidmah',
        'jenjang' => 'MTs',
        'is_active' => true,
    ]);

    $this->kelas = Kelas::create([
        'kode_kelas' => '8A-RPT',
        'nama_kelas' => '8 Wustho A',
        'tingkat_kelas' => '8',
        'tahun_ajaran_id' => $this->taAktif->id,
        'wali_kelas_id' => $this->pengajar->id,
        'educational_unit_id' => $this->unit->id,
    ]);

    $this->mapelFiqih = MataPelajaran::create([
        'educational_unit_id' => $this->unit->id,
        'kode_mapel' => 'FQH-08',
        'nama_mapel' => 'Fiqih Ibadah',
    ]);

    $this->jadwalFiqih = JadwalPelajaran::create([
        'tahun_ajaran_id' => $this->taAktif->id,
        'kelas_id' => $this->kelas->id,
        'mata_pelajaran_id' => $this->mapelFiqih->id,
        'pengajar_id' => $this->pengajar->id,
        'hari' => 'Senin',
        'waktu_mulai' => '07:30:00',
        'waktu_selesai' => '09:00:00',
        'ruang' => 'R-8A',
    ]);

    $this->santriLengkap = Santri::create([
        'kelas_id' => $this->kelas->id,
        'educational_unit_id' => $this->unit->id,
        'nis' => '20250101',
        'nama_lengkap' => 'Badrus Sholeh',
        'nama_wali' => 'H. Munir',
        'jenis_kelamin' => 'Laki-laki',
        'status_santri' => 'Aktif',
    ]);

    $this->santriKosong = Santri::create([
        'kelas_id' => $this->kelas->id,
        'educational_unit_id' => $this->unit->id,
        'nis' => '20250102',
        'nama_lengkap' => 'Fatimah Az-Zahra',
        'nama_wali' => 'H. Ridwan',
        'jenis_kelamin' => 'Perempuan',
        'status_santri' => 'Aktif',
    ]);
});

test('workspace renders concise header, four separated tabs, global context filter, and neutral report cards without duplicate class selects', function () {
    $response = $this->actingAs($this->adminAkademik)->get(route('admin.reports.index'));

    $response->assertOk();
    $response->assertSee('Laporan &amp; Rapor', false);
    $response->assertDontSee('Pusat Laporan Kolektif &amp; Cetak Rapor PDF', false);

    // 4 Workspace Tabs
    $response->assertSee('Laporan Akademik');
    $response->assertSee('Kelola Rapor');
    $response->assertSee('Persetujuan &amp; Cetak', false);
    $response->assertSee('Riwayat');

    // Neutral cards without numbering 1., 2., 3. and without repeated class selects
    $response->assertSee('Rekap Presensi');
    $response->assertSee('Leger Nilai');
    $response->assertSee('Rapor Santri');
    $response->assertDontSee('1. Rekap Presensi Kelas');
    $response->assertDontSee('2. Leger &amp; Nilai Spesifik Mapel', false);
    $response->assertDontSee('id="attendance_kelas_id"', false);
    $response->assertDontSee('id="grades_kelas_id"', false);

    // Consistent Pratinjau & Unduh PDF actions (not "Pratinjau / Cetak")
    $response->assertDontSee('Pratinjau / Cetak');
    $response->assertSee('Unduh PDF');
});

test('compact evaluation table uses non-modal side drawer with textarea and saves draft without prematurely locking grades', function () {
    $response = $this->actingAs($this->waliKelasUser)->get(route('admin.reports.index', ['tab' => 'kelola']));

    $response->assertOk();
    // Side drawer (<dialog popover="manual">) + textarea + sticky action bar exist
    $response->assertSee('id="rapor-eval-drawer"', false);
    $response->assertSee('popover="manual"', false);
    $response->assertSee('id="drawer_catatan_wali"', false);
    $response->assertSee('id="sticky-unsaved-bar"', false);
    $response->assertSee('Simpan Perubahan');
    $response->assertDontSee('Simpan Nilai Sikap &amp; Catatan Wali Kelas', false);

    // Save single evaluation via Side Drawer JSON endpoint as draft
    $jsonResp = $this->actingAs($this->waliKelasUser)
        ->postJson(route('admin.reports.santri-evaluation', $this->santriLengkap->id), [
            'tahun_ajaran_id' => $this->taAktif->id,
            'nilai_sikap' => 'Sangat Baik (A)',
            'catatan_wali_kelas' => 'Aktif dalam diskusi kitab dan tertib berjamaah.',
            'save_mode' => 'draft',
        ]);

    $jsonResp->assertOk()
        ->assertJson([
            'ok' => true,
            'nilai_sikap' => 'Sangat Baik (A)',
        ]);

    $rapor = RaporSemester::where('santri_id', $this->santriLengkap->id)
        ->where('tahun_ajaran_id', $this->taAktif->id)
        ->firstOrFail();

    // Draft save keeps status_publikasi = 'draft' so subject teachers can still input grades
    expect($rapor->status_publikasi)->toBe('draft')
        ->and($rapor->workflow_stage)->toBe('draft')
        ->and($rapor->isFinalLocked())->toBeFalse();
});

test('pre-approval validation blocks incomplete rapor and allows full 4-stage workflow once requirements are complete', function () {
    // 1. Attempt UI approval (enforce_validation=1) when grades & evaluation are still empty -> MUST BE REJECTED
    $rejectResp = $this->actingAs($this->kepalaSekolah)->post(route('admin.reports.approve-santri', $this->santriKosong->id), [
        'tahun_ajaran_id' => $this->taAktif->id,
        'enforce_validation' => '1',
    ]);

    $rejectResp->assertRedirect();
    $rejectResp->assertSessionHas('error');
    expect(RaporSemester::where('santri_id', $this->santriKosong->id)->exists())->toBeFalse();

    // 2. Complete grades + evaluation for santriLengkap
    Nilai::create([
        'jadwal_pelajaran_id' => $this->jadwalFiqih->id,
        'santri_id' => $this->santriLengkap->id,
        'nilai_tugas' => 88,
        'nilai_uts' => 90,
        'nilai_uas' => 92,
        'nilai_akhir' => 90.20,
        'grade' => 'A',
    ]);

    $this->actingAs($this->waliKelasUser)
        ->postJson(route('admin.reports.santri-evaluation', $this->santriLengkap->id), [
            'tahun_ajaran_id' => $this->taAktif->id,
            'nilai_sikap' => 'Sangat Baik (A)',
            'catatan_wali_kelas' => 'Capaian akademik dan akhlak sangat baik sepanjang semester.',
            'save_mode' => 'validate',
        ])
        ->assertOk()
        ->assertJson(['ok' => true, 'validated' => true]);

    $raporValidated = RaporSemester::where('santri_id', $this->santriLengkap->id)->firstOrFail();
    expect($raporValidated->workflow_stage)->toBe('validated');

    // 3. Batch class approval with enforce_validation=1 approves ONLY santriLengkap and skips santriKosong
    $batchResp = $this->actingAs($this->kepalaSekolah)->post(route('admin.reports.approve-class', $this->kelas->id), [
        'tahun_ajaran_id' => $this->taAktif->id,
        'enforce_validation' => '1',
    ]);
    $batchResp->assertRedirect()->assertSessionHas('success');

    $raporApproved = RaporSemester::where('santri_id', $this->santriLengkap->id)->firstOrFail();
    expect($raporApproved->status_publikasi)->toBe('approved')
        ->and($raporApproved->workflow_stage)->toBe('approved')
        ->and($raporApproved->version)->toBe(1)
        ->and(RaporSemester::where('santri_id', $this->santriKosong->id)->exists())->toBeFalse();

    // 4. Publish official rapor (Stage 4: Terbit)
    $pubResp = $this->actingAs($this->kepalaSekolah)->post(route('admin.reports.publish'), [
        'santri_id' => $this->santriLengkap->id,
        'tahun_ajaran_id' => $this->taAktif->id,
    ]);
    $pubResp->assertRedirect()->assertSessionHas('success');

    $raporPublished = $raporApproved->fresh();
    expect($raporPublished->status_publikasi)->toBe('published')
        ->and($raporPublished->workflow_stage)->toBe('published')
        ->and($raporPublished->published_at)->not->toBeNull();
});

test('approved rapor blocks free edits, requires official revision procedure, increments version to v2.0, and stamps PDF audit metadata', function () {
    Nilai::create([
        'jadwal_pelajaran_id' => $this->jadwalFiqih->id,
        'santri_id' => $this->santriLengkap->id,
        'nilai_tugas' => 85,
        'nilai_uts' => 85,
        'nilai_uas' => 85,
        'nilai_akhir' => 85.00,
        'grade' => 'A',
    ]);

    RaporSemester::create([
        'santri_id' => $this->santriLengkap->id,
        'tahun_ajaran_id' => $this->taAktif->id,
        'kelas_id' => $this->kelas->id,
        'nilai_sikap' => 'Baik (B)',
        'catatan_wali_kelas' => 'Catatan versi pertama.',
        'status_publikasi' => 'published',
        'workflow_stage' => 'published',
        'version' => 1,
        'approved_by' => $this->kepalaSekolah->id,
        'approved_at' => now(),
        'published_by' => $this->kepalaSekolah->id,
        'published_at' => now(),
    ]);

    // 1. Free edit by Wali Kelas MUST be blocked with 422 while locked
    $blockedResp = $this->actingAs($this->waliKelasUser)
        ->postJson(route('admin.reports.santri-evaluation', $this->santriLengkap->id), [
            'tahun_ajaran_id' => $this->taAktif->id,
            'nilai_sikap' => 'Sangat Baik (A)',
            'catatan_wali_kelas' => 'Mencoba ubah tanpa prosedur revisi.',
            'save_mode' => 'draft',
        ]);
    $blockedResp->assertStatus(422);

    // 2. Kepala Sekolah opens Official Revision with mandatory note
    $revResp = $this->actingAs($this->kepalaSekolah)->post(route('admin.reports.request-revision', $this->santriLengkap->id), [
        'tahun_ajaran_id' => $this->taAktif->id,
        'revision_note' => 'Penyesuaian predikat sikap sesuai rapat dewan asatidz.',
    ]);
    $revResp->assertRedirect()->assertSessionHas('success');

    $raporInRevision = RaporSemester::where('santri_id', $this->santriLengkap->id)->firstOrFail();
    expect($raporInRevision->workflow_stage)->toBe('revision')
        ->and($raporInRevision->status_publikasi)->toBe('draft')
        ->and(AdminActivityLog::where('action', 'revision')->exists())->toBeTrue();

    // 3. Wali Kelas updates evaluation during official revision, then Kepala Sekolah re-approves & publishes
    $this->actingAs($this->waliKelasUser)
        ->postJson(route('admin.reports.santri-evaluation', $this->santriLengkap->id), [
            'tahun_ajaran_id' => $this->taAktif->id,
            'nilai_sikap' => 'Sangat Baik (A)',
            'catatan_wali_kelas' => 'Catatan resmi hasil revisi dewan asatidz.',
            'save_mode' => 'validate',
        ])
        ->assertOk();

    $this->actingAs($this->kepalaSekolah)->post(route('admin.reports.approve-santri', $this->santriLengkap->id), [
        'tahun_ajaran_id' => $this->taAktif->id,
        'enforce_validation' => '1',
        'auto_publish' => '1',
    ])->assertRedirect()->assertSessionHas('success');

    $raporV2 = $raporInRevision->fresh();
    expect($raporV2->version)->toBe(2)
        ->and($raporV2->workflow_stage)->toBe('published')
        ->and($raporV2->nilai_sikap)->toBe('Sangat Baik (A)');

    // 4. Rapor PDF preview stamps official v2.0 revision metadata & approver name
    $pdfPreview = $this->actingAs($this->kepalaSekolah)->get(route('admin.reports.santri-rapor-pdf', [
        'santri' => $this->santriLengkap->id,
        'tahun_ajaran_id' => $this->taAktif->id,
    ]));
    $pdfPreview->assertOk();
    $pdfPreview->assertSee('STATUS DOKUMEN RESMI TERVALIDASI');
    $pdfPreview->assertSee('Versi Dokumen: v2.0');
    $pdfPreview->assertSee('Dokumen Hasil Revisi Resmi');
    $pdfPreview->assertSee('Ustaz H. Faruq Kepala Madrasah');

    // 5. Riwayat tab shows archived v2.0 document and audit logs
    $riwayatPage = $this->actingAs($this->kepalaSekolah)->get(route('admin.reports.index', ['tab' => 'riwayat']));
    $riwayatPage->assertOk();
    $riwayatPage->assertSee('v2.0');
    $riwayatPage->assertSee('Penyesuaian predikat sikap sesuai rapat dewan asatidz.');
});

test('rapor santri card uses livewire search engine instead of static dropdown and updates preview links live', function () {
    $page = $this->actingAs($this->kepalaSekolah)->get(route('admin.reports.index'));
    $page->assertOk()
        ->assertDontSee('id="santri_select_id"', false)
        ->assertSee('id="rapor_santri_search_input"', false);

    $component = Livewire::actingAs($this->kepalaSekolah)
        ->test(RaporSantriSearch::class, ['tahunAjaranId' => $this->taAktif->id])
        ->assertSee($this->santriLengkap->nama_lengkap)
        ->assertSee($this->santriKosong->nama_lengkap)
        ->set('search', $this->santriKosong->nis)
        ->assertSee($this->santriKosong->nama_lengkap)
        ->assertDontSee($this->santriLengkap->nama_lengkap);

    expect($component->get('selectedSantriId'))->toBe($this->santriKosong->id);

    $component->assertSee(route('admin.reports.santri-rapor-pdf', [
        'santri' => $this->santriKosong->id,
        'tahun_ajaran_id' => $this->taAktif->id,
    ]), false);

    $component->call('clearSearch')
        ->assertSee($this->santriLengkap->nama_lengkap)
        ->call('selectSantri', $this->santriLengkap->id)
        ->assertSee(route('admin.reports.santri-rapor.download', [
            'santri' => $this->santriLengkap->id,
            'tahun_ajaran_id' => $this->taAktif->id,
        ]), false);
});
