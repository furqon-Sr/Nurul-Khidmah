<?php

namespace Tests\Feature;

use App\Actions\AcceptSantriRegistration;
use App\Models\EducationalUnit;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PpdbDetailedFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_stores_all_3_sections_and_files(): void
    {
        Storage::fake('public');

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
        ]);

        $dummyBase64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $fileKk = UploadedFile::fake()->create('kartu_keluarga.pdf', 500, 'application/pdf');
        $fileAkta = UploadedFile::fake()->create('akta_kelahiran.pdf', 400, 'application/pdf');
        $fileKtp = UploadedFile::fake()->image('ktp_orang_tua.jpg');
        $fileIjazah = UploadedFile::fake()->create('ijazah_sd.pdf', 600, 'application/pdf');

        $response = $this->post(route('registration.store'), [
            'registration_number' => 'REG-998877',
            'student_photo_base64' => $dummyBase64Image,

            // Bagian 1: Data Siswa
            'student_name' => 'Ahmad Alfian Ramdani',
            'gender' => 'L',
            'birth_place' => 'Lamongan',
            'birth_date' => '2014-05-10',
            'residential_address' => 'Dusun Lopang RT 01 RW 01 Kec. Kembangbahu Lamongan',
            'previous_school' => 'MI Mansyaul Ulum Lamongan',
            'nisn' => '0147404399',
            'boarding_status' => 'Mukim',
            'target_class' => 'Kelas 7 MTs',

            // Bagian 2: Data Orang Tua
            'father_name' => 'Zainal Abidin',
            'mother_name' => 'Siti Aminah',
            'guardian_occupation' => 'Wiraswasta',
            'guardian_address' => 'Dusun Lopang RT 01 RW 01 Kec. Kembangbahu Lamongan',
            'student_phone' => '081234567890',
            'student_email' => 'wali.alfian@gmail.com',

            // Bagian 3: Dokumen Upload
            'file_kk' => $fileKk,
            'file_akta' => $fileAkta,
            'file_ktp' => $fileKtp,
            'file_ijazah' => $fileIjazah,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('santri_registrations', [
            'registration_number' => 'REG-998877',
            'student_name' => 'Ahmad Alfian Ramdani',
            'birth_place' => 'Lamongan',
            'gender' => 'L',
            'nisn' => '0147404399',
            'previous_school' => 'MI Mansyaul Ulum Lamongan',
            'boarding_status' => 'Mukim',
            'target_class' => 'Kelas 7 MTs',
            'father_name' => 'Zainal Abidin',
            'mother_name' => 'Siti Aminah',
            'guardian_occupation' => 'Wiraswasta',
            'guardian_phone' => '081234567890',
            'guardian_email' => 'wali.alfian@gmail.com',
        ]);

        $reg = SantriRegistration::where('registration_number', 'REG-998877')->firstOrFail();
        $this->assertNotNull($reg->file_kk);
        $this->assertNotNull($reg->file_akta);
        $this->assertNotNull($reg->file_ktp);
        $this->assertNotNull($reg->file_ijazah);

        Storage::disk('public')->assertExists($reg->file_kk);
        Storage::disk('public')->assertExists($reg->file_akta);
        Storage::disk('public')->assertExists($reg->file_ktp);
        Storage::disk('public')->assertExists($reg->file_ijazah);
    }

    public function test_accept_onboarding_transfers_nisn_and_detailed_fields_to_santri(): void
    {
        $unitMts = EducationalUnit::firstOrCreate(
            ['kode_unit' => 'MTS'],
            ['nama_unit' => 'MTs Nurul Khidmah', 'jenjang' => 'MTs', 'is_active' => true]
        );
        $ta = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'status_aktif' => true]);
        $kelas7 = Kelas::create([
            'kode_kelas' => 'KLS-7MTS',
            'nama_kelas' => 'Kelas 7 MTs',
            'tingkat_kelas' => '7 MTs',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unitMts->id,
        ]);

        $reg = SantriRegistration::create([
            'registration_number' => 'REG-001122',
            'student_name' => 'Ach. Azyan Uwais Al Qorny',
            'birth_place' => 'Lamongan',
            'gender' => 'L',
            'birth_date' => '2014-08-15',
            'residential_address' => 'Lamongan, Jawa Timur',
            'previous_school' => 'MI Mansyaul Ulum',
            'nisn' => '3148118810',
            'boarding_status' => 'Mukim',
            'target_class' => 'Kelas 7 MTs',
            'academic_year' => '2026/2027',
            'branch' => 'Lamongan',
            'guardian_name' => 'Ahmad (Ayah) & Fatimah (Ibu)',
            'father_name' => 'Ahmad',
            'mother_name' => 'Fatimah',
            'guardian_occupation' => 'PNS',
            'guardian_phone' => '085712345678',
            'guardian_email' => 'azyan.wali@gmail.com',
            'education_qualification' => 'Orang Tua Kandung',
            'status' => 'pending',
        ]);

        $action = new AcceptSantriRegistration;
        $result = $action->execute($reg);

        $reg->refresh();
        $this->assertSame('accepted', $reg->status);

        $santri = Santri::find($reg->santri_id);
        $this->assertNotNull($santri);
        $this->assertSame('3148118810', $santri->nisn);
        $this->assertSame('Lamongan', $santri->tempat_lahir);
        $this->assertSame('MI Mansyaul Ulum', $santri->previous_school);
        $this->assertSame('Mukim', $santri->boarding_status);
        $this->assertSame('PNS', $santri->guardian_occupation);
        $this->assertSame('085712345678', $santri->no_hp_wali);
    }
}
