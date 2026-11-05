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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        $response->assertSee('Migrasi &amp; Import Data Massal', false);
        $response->assertSee('Import Data Santri');
        $response->assertSee('Import Data Pengajar');
    }

    public function test_unauthorized_user_cannot_access_import_page(): void
    {
        $env = $this->createAcademicEnvironment();
        $guru = $env['guru'];

        $response = $this->actingAs($guru)->get(route('admin.imports.index'));

        $response->assertForbidden();
    }

    public function test_upload_santri_stores_file_creates_batch_and_pushes_job_to_queue(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $admin = $env['adminTU'];

        $csvContent = "nama_lengkap,nis,jenis_kelamin,unit,kelas,alamat\n".
                      "Ahmad Fathanah,,Laki-laki,MTs,7A,Lamongan\n".
                      "Fatimah Az Zahra,,Perempuan,MTs,7A,Lamongan\n";

        $file = UploadedFile::fake()->createWithContent('santri_batch_2026.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.imports.santri'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.imports.index'));
        $response->assertSessionHas('success');

        // 1. Assert file tersimpan di storage local (imports/...)
        $batch = ImportBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals('santri_batch_2026.csv', $batch->file_name);
        $this->assertEquals('santri', $batch->type);
        $this->assertEquals(ImportBatch::STATUS_PENDING, $batch->status);
        $this->assertEquals(2, $batch->total_rows);
        $this->assertEquals($admin->id, $batch->created_by);

        Storage::disk('local')->assertExists($batch->file_path);

        // 2. Assert job ter-push ke Queue
        Queue::assertPushed(QueueImport::class);
    }

    public function test_upload_pengajar_stores_file_creates_batch_and_pushes_job_to_queue(): void
    {
        Storage::fake('local');
        Queue::fake();

        $env = $this->createAcademicEnvironment();
        $superAdmin = $env['superAdmin'];

        $csvContent = "nama_lengkap,nomor_identitas,jenis_kelamin,alamat,nomor_telepon\n".
                      "Ust. Zaidan Pengajar,,Laki-laki,Lamongan,081234567890\n";

        $file = UploadedFile::fake()->createWithContent('guru_batch_2026.csv', $csvContent);

        $response = $this->actingAs($superAdmin)->post(route('admin.imports.pengajar'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.imports.index'));
        $response->assertSessionHas('success');

        $batch = ImportBatch::where('type', 'pengajar')->first();
        $this->assertNotNull($batch);
        $this->assertEquals('guru_batch_2026.csv', $batch->file_name);
        $this->assertEquals('pengajar', $batch->type);
        $this->assertEquals(ImportBatch::STATUS_PENDING, $batch->status);
        $this->assertEquals(1, $batch->total_rows);

        Storage::disk('local')->assertExists($batch->file_path);

        Queue::assertPushed(QueueImport::class);
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
}
