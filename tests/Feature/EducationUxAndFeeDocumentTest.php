<?php

namespace Tests\Feature;

use App\Models\EducationFee;
use App\Models\EducationProgram;
use App\Models\PageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EducationUxAndFeeDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_dropdown_and_dedicated_program_page_render_active_programs_with_clean_ux(): void
    {
        Cache::forget('site_global_data');

        $mts = EducationProgram::create([
            'name' => 'MTs Nurul Khidmah',
            'slug' => 'mts-nurul-khidmah',
            'description' => "MTs adalah jenjang pendidikan formal setingkat SMP di bawah Kementerian Agama.\n\nKarakteristik Utama MTs:\n• Jenjang dan Durasi: Ditempuh selama 3 tahun (kelas 7, 8, dan 9).\n• Kurikulum: Perpaduan kurikulum nasional dan diniyah pesantren.",
            'is_active' => true,
            'is_registration_open' => true,
            'is_coming_soon' => false,
            'order' => 1,
        ]);

        $smk = EducationProgram::create([
            'name' => 'SMK Otomotif Nurul Khidmah',
            'slug' => 'smk-otomotif',
            'description' => 'Sekolah Menengah Kejuruan berbasis pesantren.',
            'is_active' => true,
            'is_registration_open' => true,
            'is_coming_soon' => false,
            'order' => 2,
        ]);

        $inactive = EducationProgram::create([
            'name' => 'Unit Nonaktif',
            'slug' => 'unit-nonaktif',
            'description' => 'Tidak boleh tampil.',
            'is_active' => false,
            'is_registration_open' => false,
            'is_coming_soon' => false,
            'order' => 3,
        ]);

        // Halaman /pendidikan menampilkan kartu pilihan & tautan dropdown navigasi ke halaman masing-masing
        $overviewResponse = $this->get(route('education'));
        $overviewResponse->assertOk()
            ->assertSee('MTs Nurul Khidmah')
            ->assertSee('SMK Otomotif Nurul Khidmah')
            ->assertSee(route('education.program', $mts->slug))
            ->assertSee(route('education.program', $smk->slug))
            ->assertDontSee('Unit Nonaktif')
            ->assertDontSee('Mulai Pendaftaran Unit Ini');

        // Halaman tersendiri program MTs menampilkan judul menonjol, deskripsi rata kiri + poin, CTA "Daftar MTs", dan "Lihat Biaya"
        $mtsResponse = $this->get(route('education.program', $mts->slug));
        $mtsResponse->assertOk()
            ->assertSee('MTs Nurul Khidmah')
            ->assertSee('Karakteristik Utama MTs')
            ->assertSee('Jenjang dan Durasi:')
            ->assertSee('Daftar MTs')
            ->assertSee('Lihat Biaya')
            ->assertSee('#biaya-pendidikan')
            ->assertDontSee('text-justify');

        // Halaman program nonaktif mengembalikan 404
        $this->get(route('education.program', $inactive->slug))->assertNotFound();
    }

    public function test_admin_can_upload_csv_pdf_and_image_fee_documents_and_frontend_renders_live_preview(): void
    {
        Storage::fake('public');
        Cache::forget('site_global_data');

        $admin = User::create([
            'name' => 'Bapak Mufti',
            'email' => 'mufti@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENGURUS_AKADEMIK,
            'email_verified_at' => now(),
        ]);

        $mts = EducationProgram::create([
            'name' => 'MTs Nurul Khidmah',
            'slug' => 'mts-nurul-khidmah',
            'description' => 'Program Madrasah Tsanawiyah.',
            'is_active' => true,
            'is_registration_open' => true,
            'order' => 1,
        ]);

        // 1. Unggah file CSV khusus program MTs -> Frontend menampilkan pratinjau tabel CSV
        $csvFile = UploadedFile::fake()->createWithContent(
            'biaya_mts.csv',
            "Komponen Biaya,Nominal,Keterangan\nUang Pendaftaran,Rp 150.000,Sekali saat daftar\nSPP Bulanan,Rp 150.000,Dibayar tiap bulan\n"
        );

        $this->actingAs($admin)->post(route('admin.fees.upload-document'), [
            'target_program' => $mts->slug,
            'fee_document' => $csvFile,
        ])->assertRedirect(route('admin.fees.index'))
            ->assertSessionHas('success');

        $csvRecord = PageContent::where('section_key', 'education_fee_file_'.$mts->slug)->first();
        $this->assertNotNull($csvRecord);
        Storage::disk('public')->assertExists($csvRecord->image_path);

        $this->get(route('education.program', $mts->slug))
            ->assertOk()
            ->assertSee('Uang Pendaftaran')
            ->assertSee('Rp 150.000')
            ->assertSee('SPP Bulanan')
            ->assertSee('Unduh (CSV)');

        // 2. Unggah gambar brosur biaya global -> Frontend menampilkan pratinjau <img>
        $imageFile = UploadedFile::fake()->image('brosur_biaya.png', 800, 600);
        $this->actingAs($admin)->post(route('admin.fees.upload-document'), [
            'target_program' => 'global',
            'fee_document' => $imageFile,
        ])->assertRedirect(route('admin.fees.index'));

        $imageRecord = PageContent::where('section_key', 'education_fee_pdf')->first();
        $this->assertNotNull($imageRecord);
        Storage::disk('public')->assertExists($imageRecord->image_path);

        $this->get(route('education'))
            ->assertOk()
            ->assertSee('/storage/'.$imageRecord->image_path)
            ->assertSee('Unduh (PNG)');

        // 3. Unggah PDF menggantikan global -> Frontend menampilkan pratinjau <iframe ...>
        $pdfFile = UploadedFile::fake()->create('rincian_biaya.pdf', 120, 'application/pdf');
        $this->actingAs($admin)->post(route('admin.fees.upload-document'), [
            'target_program' => 'global',
            'fee_document' => $pdfFile,
        ])->assertRedirect(route('admin.fees.index'));

        Storage::disk('public')->assertMissing($imageRecord->image_path);
        $pdfRecord = PageContent::where('section_key', 'education_fee_pdf')->first();
        $this->assertNotNull($pdfRecord);
        Storage::disk('public')->assertExists($pdfRecord->image_path);

        $this->get(route('education'))
            ->assertOk()
            ->assertSee('<iframe', false)
            ->assertSee('Unduh (PDF)');

        // 4. Hapus dokumen biaya
        $this->actingAs($admin)->delete(route('admin.fees.destroy-document', 'education_fee_pdf'))
            ->assertRedirect(route('admin.fees.index'));

        Storage::disk('public')->assertMissing($pdfRecord->image_path);
        $this->assertDatabaseMissing('page_contents', ['section_key' => 'education_fee_pdf']);

        // 5. Pastikan sistem tidak menampilkan rincian biaya berbasis teks (baik di admin maupun frontend)
        EducationFee::create([
            'name' => 'Komponen Teks Lama',
            'amount' => 999999,
            'category' => 'MTs',
            'description' => 'Tidak boleh tampil lagi',
        ]);

        $this->actingAs($admin)->get(route('admin.fees.index'))
            ->assertOk()
            ->assertDontSee('Tambah Rincian Manual')
            ->assertDontSee('Komponen Teks Lama');

        $this->get(route('education'))
            ->assertOk()
            ->assertDontSee('Komponen Teks Lama')
            ->assertDontSee('999.999');

        $this->get(route('education.program', $mts->slug))
            ->assertOk()
            ->assertDontSee('Komponen Teks Lama')
            ->assertDontSee('999.999');
    }
}
