<?php

namespace Tests\Feature;

use App\Imports\PengajarImport;
use App\Imports\SantriImport;
use App\Models\EducationalUnit;
use App\Models\ImportBatch;
use App\Models\Kelas;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\QueueHealth;
use Database\Seeders\AcademicMasterSeeder;
use Database\Seeders\EducationalUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Jobs\QueueImport;
use Tests\TestCase;

class QueueImportTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicEnvironment(): array
    {
        $unit = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS-QUEUE'],
            ['nama_unit' => 'MTs Nurul Khidmah Queue', 'jenjang' => 'MTs', 'is_active' => true]
        );

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $adminTU = User::create([
            'name' => 'Staf Tata Usaha',
            'email' => 'tu@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $guru = User::create([
            'name' => 'Ust. Biasa',
            'email' => 'biasa@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENAGA_PENGAJAR,
            'email_verified_at' => now(),
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => '7A-QUEUE',
            'nama_kelas' => 'Kelas 7A Queue',
            'tingkat_kelas' => '7',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        return compact('unit', 'ta', 'adminTU', 'superAdmin', 'guru', 'kelas');
    }

    public function test_admin_tu_can_access_import_page(): void
    {
        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        $response = $this->actingAs($admin)->get(route('admin.imports.index'));

        $response->assertOk();
        $response->assertSee('Import Data');
        $response->assertSee('Santri');
        $response->assertSee('Pengajar');
        $response->assertSee('Unggah & validasi', false);
        $response->assertSee('Layanan import aktif'); // queue sync pada lingkungan test = langsung aktif
    }

    public function test_unauthorized_user_cannot_access_import_page(): void
    {
        $env = $this->createAcademicEnvironment();
        $guru = $env['guru'];

        $response = $this->actingAs($guru)->get(route('admin.imports.index'));

        $response->assertForbidden();
    }

    public function test_upload_santri_is_validated_first_and_does_not_touch_main_tables_or_queue(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        $csvContent = "nama_lengkap,nis,jenis_kelamin,unit,kelas,alamat\n".
                      "Ahmad Fathanah,,Laki-laki,MTs,7A,Lamongan\n".
                      "Fatimah Az Zahra,,Perempuan,MTs,7A,Lamongan\n";

        $file = UploadedFile::fake()->createWithContent('santri_batch_2026.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.imports.santri'), ['file' => $file]);

        $batch = ImportBatch::first();
        $this->assertNotNull($batch);
        $response->assertRedirect(route('admin.imports.show', $batch));

        $this->assertEquals('santri_batch_2026.csv', $batch->file_name);
        $this->assertEquals('santri', $batch->type);
        $this->assertEquals(ImportBatch::STATUS_VALIDATED, $batch->status);
        $this->assertEquals(2, $batch->total_rows);
        $this->assertEquals(2, $batch->valid_rows);
        $this->assertEquals(0, $batch->invalid_rows);
        $this->assertEquals($admin->id, $batch->created_by);
        Storage::disk('local')->assertExists($batch->file_path);

        // Tahap validasi: belum ada job, belum ada data santri
        Queue::assertNothingPushed();
        $this->assertSame(0, Santri::count());

        $this->actingAs($admin)->get(route('admin.imports.show', $batch))
            ->assertOk()
            ->assertSee('Pratinjau import')
            ->assertSee('baris akan diimpor');
    }

    public function test_validation_reports_invalid_rows_duplicates_and_existing_records(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        Santri::create(['nis' => '26MTS777', 'nama_lengkap' => 'Sudah Ada', 'jenis_kelamin' => 'Laki-laki', 'status_santri' => 'Aktif']);

        $csv = "nama_lengkap,nis,jenis_kelamin,tanggal_lahir\n".
               "Budi Valid,26MTS100,Laki-laki,2012-01-02\n".      // baris 2: valid
               ",26MTS101,Laki-laki,2012-01-02\n".                  // baris 3: nama kosong
               "Citra Salah JK,26MTS102,Tidak Jelas,2012-01-02\n".  // baris 4: jenis kelamin tidak dikenal
               "Dedi Tanggal,26MTS103,Laki-laki,bukan-tanggal\n".   // baris 5: tanggal tidak valid
               "Eko Duplikat,26MTS100,Laki-laki,2012-01-02\n".      // baris 6: NIS dobel dengan baris 2
               "Fajar Sudah Ada,26MTS777,Laki-laki,2012-01-02\n";   // baris 7: sudah terdaftar

        $this->actingAs($admin)->post(route('admin.imports.santri'), [
            'file' => UploadedFile::fake()->createWithContent('campur.csv', $csv),
        ])->assertRedirect();

        $batch = ImportBatch::first();
        $this->assertSame(6, $batch->total_rows);
        $this->assertSame(1, $batch->valid_rows);
        $this->assertSame(4, $batch->invalid_rows);
        $this->assertSame(1, $batch->duplicate_rows);

        $issues = collect($batch->report['issues'])->keyBy('n');
        $this->assertSame('invalid', $issues[3]['status']);
        $this->assertSame('invalid', $issues[4]['status']);
        $this->assertSame('invalid', $issues[5]['status']);
        $this->assertSame('invalid', $issues[6]['status']);
        $this->assertStringContainsString('baris 2', $issues[6]['errors'][0]);
        $this->assertSame('exists', $issues[7]['status']);

        // Laporan kesalahan dapat diunduh
        $download = $this->actingAs($admin)->get(route('admin.imports.errors', $batch));
        $download->assertOk();
        $this->assertStringContainsString('Nama lengkap wajib diisi.', $download->streamedContent());
    }

    public function test_upload_rejects_file_missing_required_column_and_wrong_extension(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        $this->actingAs($admin)->post(route('admin.imports.santri'), [
            'file' => UploadedFile::fake()->createWithContent('tanpa_nama.csv', "nis,alamat\n26MTS001,Lamongan\n"),
        ])->assertSessionHasErrors('file');

        $this->actingAs($admin)->post(route('admin.imports.santri'), [
            'file' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, ImportBatch::count());
    }

    public function test_confirm_queues_only_valid_rows_and_respects_existing_data_mode(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        Santri::create(['nis' => '26MTS777', 'nama_lengkap' => 'Sudah Ada', 'jenis_kelamin' => 'Laki-laki', 'status_santri' => 'Aktif']);

        $csv = "nama_lengkap,nis,jenis_kelamin\n".
               "Budi Valid,26MTS100,Laki-laki\n".
               ",26MTS101,Laki-laki\n".
               "Fajar Sudah Ada,26MTS777,Laki-laki\n";

        $this->actingAs($admin)->post(route('admin.imports.santri'), [
            'file' => UploadedFile::fake()->createWithContent('campur.csv', $csv),
        ]);
        $batch = ImportBatch::first();

        // Mode lewati: hanya 1 baris valid; baris sudah-ada dilewati; baris invalid tidak ikut
        $this->actingAs($admin)->post(route('admin.imports.confirm', $batch), ['mode' => 'skip'])
            ->assertRedirect(route('admin.imports.index'));

        $batch->refresh();
        $this->assertSame(ImportBatch::STATUS_PENDING, $batch->status);
        $this->assertSame(1, $batch->total_rows);
        $this->assertSame(1, $batch->skipped_rows);
        $this->assertSame(1, $batch->invalid_rows);
        Queue::assertPushed(QueueImport::class);

        $clean = Storage::disk('local')->get('imports/clean_'.$batch->id.'.csv');
        $this->assertStringContainsString('Budi Valid', $clean);
        $this->assertStringNotContainsString('Fajar Sudah Ada', $clean);

        // Sudah dikonfirmasi: tidak dapat dikonfirmasi ulang
        $this->actingAs($admin)->post(route('admin.imports.confirm', $batch), ['mode' => 'skip'])->assertStatus(409);
    }

    public function test_update_mode_includes_existing_rows_and_cancel_discards_batch(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        Santri::create(['nis' => '26MTS777', 'nama_lengkap' => 'Sudah Ada', 'jenis_kelamin' => 'Laki-laki', 'status_santri' => 'Aktif']);
        $csv = "nama_lengkap,nis\nBudi Valid,26MTS100\nFajar Update,26MTS777\n";

        $this->actingAs($admin)->post(route('admin.imports.santri'), ['file' => UploadedFile::fake()->createWithContent('a.csv', $csv)]);
        $this->actingAs($admin)->post(route('admin.imports.santri'), ['file' => UploadedFile::fake()->createWithContent('b.csv', $csv)]);
        [$first, $second] = ImportBatch::orderBy('id')->get()->all();

        $this->actingAs($admin)->post(route('admin.imports.confirm', $first), ['mode' => 'update']);
        $this->assertSame(2, $first->fresh()->total_rows);
        $this->assertSame(0, $first->fresh()->skipped_rows);

        $this->actingAs($admin)->post(route('admin.imports.cancel', $second))->assertRedirect(route('admin.imports.index'));
        $this->assertSame(ImportBatch::STATUS_CANCELLED, $second->fresh()->status);
        Storage::disk('local')->assertMissing($second->file_path);
        $this->assertSame(0, Santri::where('nis', '26MTS100')->count());
    }

    public function test_upload_pengajar_is_validated_with_status_rules(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $superAdmin = $env['superAdmin'];

        $csv = "nama_lengkap,nomor_identitas,jenis_kelamin,alamat,nomor_telepon,status_pengajar\n".
               "Ust. Zaidan Pengajar,,Laki-laki,Lamongan,081234567890,Aktif\n".
               "Ust. Salah Status,,Laki-laki,Lamongan,081234567890,Pensiun\n";

        $this->actingAs($superAdmin)->post(route('admin.imports.pengajar'), [
            'file' => UploadedFile::fake()->createWithContent('guru_batch_2026.csv', $csv),
        ])->assertRedirect();

        $batch = ImportBatch::where('type', 'pengajar')->first();
        $this->assertNotNull($batch);
        $this->assertEquals(ImportBatch::STATUS_VALIDATED, $batch->status);
        $this->assertEquals(2, $batch->total_rows);
        $this->assertEquals(1, $batch->valid_rows);
        $this->assertEquals(1, $batch->invalid_rows);
        Queue::assertNothingPushed();
        Storage::disk('local')->assertExists($batch->file_path);
    }

    public function test_status_endpoint_and_history_filters(): void
    {
        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        $done = ImportBatch::create(['file_name' => 'selesai.csv', 'type' => 'santri', 'status' => ImportBatch::STATUS_COMPLETED, 'total_rows' => 4, 'processed_rows' => 4]);
        $run = ImportBatch::create(['file_name' => 'berjalan.csv', 'type' => 'pengajar', 'status' => ImportBatch::STATUS_PROCESSING, 'total_rows' => 10, 'processed_rows' => 5]);

        $this->actingAs($admin)->getJson(route('admin.imports.status', ['ids' => [$done->id, $run->id]]))
            ->assertOk()
            ->assertJsonPath('service.state', 'active')
            ->assertJsonFragment(['id' => $run->id, 'percent' => 50, 'active' => true])
            ->assertJsonFragment(['id' => $done->id, 'status_label' => 'Selesai']);

        $this->actingAs($admin)->get(route('admin.imports.index', ['status' => 'processing']))
            ->assertOk()->assertSee('berjalan.csv')->assertDontSee('selesai.csv');
        $this->actingAs($admin)->get(route('admin.imports.index', ['type' => 'santri']))
            ->assertOk()->assertSee('selesai.csv')->assertDontSee('berjalan.csv');
    }

    public function test_queue_health_reflects_real_worker_signal(): void
    {
        config(['queue.default' => 'database']);
        Cache::flush();

        // Belum ada detak & belum ada probe → belum diketahui
        $this->assertSame('unknown', QueueHealth::status()['state']);

        // Worker memproses probe → aktif
        QueueHealth::recordHeartbeat();
        $this->assertSame('active', QueueHealth::status()['state']);

        // Probe terkirim sudah lama tanpa detak baru → tidak tersedia
        Cache::flush();
        Cache::put(QueueHealth::PROBE_KEY, now()->subMinutes(5)->timestamp, now()->addHour());
        $this->assertSame('unavailable', QueueHealth::status()['state']);
    }

    public function test_santri_import_model_maps_data_and_auto_generates_nis_when_empty(): void
    {
        $env = $this->createAcademicEnvironment();
        $unit = $env['unit'];
        $kelas = $env['kelas'];

        $batch = ImportBatch::create([
            'file_name' => 'test_santri.csv',
            'type' => 'santri',
            'status' => 'pending',
            'total_rows' => 1,
            'processed_rows' => 0,
        ]);

        $import = new SantriImport($batch->id);

        $model = $import->model([
            'nama_lengkap' => 'Muhammad Iqbal Al-Fikri',
            'nis' => '', // Dikosongkan agar auto-generate
            'jenis_kelamin' => 'Laki-laki',
            'unit' => $unit->kode_unit,
            'kelas' => $kelas->nama_kelas,
            'tempat_lahir' => 'Lamongan',
            'tanggal_lahir' => '2012-08-15',
            'alamat' => 'Ds. Simo Lamongan',
            'nama_wali' => 'H. Bakri',
            'no_hp_wali' => '081234567891',
            'tahun_masuk' => '2026',
        ]);

        $this->assertInstanceOf(Santri::class, $model);
        $this->assertEquals('Muhammad Iqbal Al-Fikri', $model->nama_lengkap);
        $this->assertEquals($unit->id, $model->educational_unit_id);
        $this->assertEquals($kelas->id, $model->kelas_id);
        $this->assertEquals('Laki-laki', $model->jenis_kelamin);

        // Verifikasi format NIS otomatis: [2DigitTahun][KodeUnit][3DigitUrut], misal 26MTS001
        $this->assertNotEmpty($model->nis);
        $this->assertMatchesRegularExpression('/^26MTS\d{3}$/', $model->nis);

        // Batch processed_rows bertambah
        $batch->refresh();
        $this->assertEquals(1, $batch->processed_rows);
    }

    public function test_pengajar_import_model_maps_data_and_auto_generates_niy_when_empty(): void
    {
        $batch = ImportBatch::create([
            'file_name' => 'test_pengajar.csv',
            'type' => 'pengajar',
            'status' => 'pending',
            'total_rows' => 1,
            'processed_rows' => 0,
        ]);

        $import = new PengajarImport($batch->id);

        $model = $import->model([
            'nama_lengkap' => 'Ustazah Aisyah, S.Pd.',
            'nomor_identitas' => '', // Dikosongkan
            'jenis_kelamin' => 'Perempuan',
            'alamat' => 'Komplek Pondok',
            'nomor_telepon' => '081398765432',
            'status_pengajar' => 'Aktif',
        ]);

        $this->assertInstanceOf(Pengajar::class, $model);
        $this->assertEquals('Ustazah Aisyah, S.Pd.', $model->nama_lengkap);
        $this->assertEquals('Perempuan', $model->jenis_kelamin);

        // Format NIY otomatis: NIY-[2DigitTahun]-[3DigitUrut], misal NIY-26-001
        $this->assertNotEmpty($model->nomor_identitas);
        $this->assertMatchesRegularExpression('/^NIY-26-\d{3}$/', $model->nomor_identitas);

        $batch->refresh();
        $this->assertEquals(1, $batch->processed_rows);
    }

    public function test_admin_can_download_csv_templates(): void
    {
        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        $resSantri = $this->actingAs($admin)->get(route('admin.imports.template', 'santri'));
        $resSantri->assertOk();
        $resSantri->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('nama_lengkap,nis,jenis_kelamin', $resSantri->getContent());

        $resPengajar = $this->actingAs($admin)->get(route('admin.imports.template', 'pengajar'));
        $resPengajar->assertOk();
        $resPengajar->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('nama_lengkap,nomor_identitas,jenis_kelamin', $resPengajar->getContent());
    }

    public function test_upload_supports_no_nama_nisn_headers_from_student_sheet_and_academic_seeder_populates_urs_roles_and_49_students(): void
    {
        Storage::fake('local');
        Queue::fake();

        $this->seed(EducationalUnitSeeder::class);
        $this->seed(AcademicMasterSeeder::class);

        // Pastikan seluruh akun role sesuai dokumen URS Tervalidasi
        $this->assertDatabaseHas('users', ['email' => 'super@nurulkhidmah.org', 'name' => 'Fahruri Anafi', 'role' => User::ROLE_SUPER_ADMIN]);
        $this->assertDatabaseHas('users', ['email' => 'pimpinan@nurulkhidmah.org', 'name' => 'KH. Imam Machmudi', 'role' => User::ROLE_PIMPINAN]);
        $this->assertDatabaseHas('users', ['email' => 'ketua@nurulkhidmah.org', 'name' => 'Bapak Mas\'ud, S.Pd.', 'role' => User::ROLE_PIMPINAN]);
        $this->assertDatabaseHas('users', ['email' => 'kepala@nurulkhidmah.org', 'name' => 'Ibu Uswatun Hasanah SP, S.Pd.I.', 'role' => User::ROLE_KEPALA_SEKOLAH]);
        $this->assertDatabaseHas('users', ['email' => 'admin@nurulkhidmah.org', 'name' => 'Bapak Mufti', 'role' => User::ROLE_PENGURUS_AKADEMIK]);
        $this->assertDatabaseHas('users', ['email' => 'akademik@nurulkhidmah.org', 'name' => 'Bapak Mufti (Staf TU)', 'role' => User::ROLE_PENGURUS_AKADEMIK]);

        // Pastikan 9 tenaga pengajar sesuai responden URS
        $this->assertSame(9, Pengajar::count());
        $this->assertDatabaseHas('pengajars', ['nomor_identitas' => 'UST-2024-001', 'nama_lengkap' => 'Ustazah Hesti Eka Nur Dianti']);
        $this->assertDatabaseHas('pengajars', ['nomor_identitas' => 'UST-2024-009', 'nama_lengkap' => 'Ustazah Fadian Miftah Utami']);

        // Pastikan 49 data siswa dari berkas terlampir masuk dengan penempatan kelas tepat:
        // No 1-26 = Kelas 7 (7A: 1-13, 7B: 14-26); No 27-45 & 47-49 = Kelas 8; No 46 (Qonita) = SMA Sekolah Luar / Mondok Saja
        $this->assertSame(49, Santri::count());
        $this->assertDatabaseHas('santris', ['nis' => '20260001', 'nisn' => '3148118810', 'nama_lengkap' => 'Ach. Azyan Uwais Al Qorny']);
        $this->assertDatabaseHas('santris', ['nis' => '20260002', 'nisn' => '0147404399', 'nama_lengkap' => 'Ahmad Alfian Ramdani']);
        $this->assertDatabaseHas('santris', ['nis' => '20260049', 'nisn' => '3135935807', 'nama_lengkap' => 'Suvia Ananda Hidayati']);

        $santri26 = Santri::with('kelas')->where('nis', '20260026')->firstOrFail();
        $this->assertSame('Kelas 7 Ula B', $santri26->kelas?->nama_kelas);

        foreach (['20260027', '20260045', '20260047', '20260049'] as $nisKelas8) {
            $s8 = Santri::with('kelas')->where('nis', $nisKelas8)->firstOrFail();
            $this->assertSame('Kelas 8 Wustho A', $s8->kelas?->nama_kelas);
        }

        $qonita = Santri::where('nis', '20260046')->firstOrFail();
        $this->assertSame('Qonita Federica', $qonita->nama_lengkap);
        $this->assertNull($qonita->kelas_id);
        $this->assertNull($qonita->educational_unit_id);
        $this->assertSame('Mukim', $qonita->boarding_status);
        $this->assertSame('Aktif', $qonita->status_santri);
        $this->assertSame('SMA (Sekolah di Luar — Mondok Saja)', $qonita->previous_school);

        // Pastikan format CSV "No,Nama,NISN" juga didukung langsung pada fitur Import Santri
        $admin = User::where('email', 'admin@nurulkhidmah.org')->firstOrFail();
        $csv = "No,Nama,NISN\n1,Santri Baru Uji,0149999999\n";

        $this->actingAs($admin)->post(route('admin.imports.santri'), [
            'file' => UploadedFile::fake()->createWithContent('data_siswa.csv', $csv),
        ])->assertRedirect();

        $batch = ImportBatch::latest('id')->first();
        $this->assertNotNull($batch);
        $this->assertSame(1, $batch->valid_rows);
        $this->assertSame(0, $batch->invalid_rows);
        $this->assertEmpty($batch->report['unknown_headers'] ?? []);
    }
}
