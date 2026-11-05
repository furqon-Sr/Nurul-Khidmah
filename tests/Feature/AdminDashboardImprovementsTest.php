<?php

namespace Tests\Feature;

use App\Models\EducationalUnit;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminTu;
    protected TahunAjaran $taAktif;
    protected Kelas $kelasA;
    protected Santri $santri1;
    protected Santri $santri2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminTu = User::factory()->create([
            'role' => User::ROLE_PENGURUS_AKADEMIK,
        ]);

        $this->taAktif = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'status_aktif' => true,
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
        ]);

        $unit = EducationalUnit::first() ?? EducationalUnit::create([
            'kode_unit' => 'MTS-TEST',
            'nama_unit' => 'Madrasah Tsanawiyah',
            'jenjang' => 'MTs',
            'is_active' => true,
        ]);

        $this->kelasA = Kelas::create([
            'kode_kelas' => '7A-2026',
            'nama_kelas' => 'Kelas 7A',
            'tingkat_kelas' => '7',
            'tahun_ajaran_id' => $this->taAktif->id,
            'educational_unit_id' => $unit->id,
        ]);

        $waliUser = User::factory()->create([
            'role' => User::ROLE_SANTRI,
            'email' => 'wali.test@example.com',
        ]);

        $this->santri1 = Santri::create([
            'user_id' => $waliUser->id,
            'kelas_id' => $this->kelasA->id,
            'educational_unit_id' => $unit->id,
            'nis' => '10000001',
            'nisn' => '0098765432',
            'nama_lengkap' => 'Ahmad Faiz Test',
            'status_santri' => 'Aktif',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $this->santri2 = Santri::create([
            'user_id' => $waliUser->id,
            'kelas_id' => $this->kelasA->id,
            'educational_unit_id' => $unit->id,
            'nis' => '10000002',
            'nisn' => '0098765433',
            'nama_lengkap' => 'Budi Santoso Test',
            'status_santri' => 'Lulus',
            'jenis_kelamin' => 'Laki-laki',
        ]);
    }

    public function test_sidebar_does_not_contain_duplicate_profile(): void
    {
        $response = $this->actingAs($this->adminTu)->get(route('dashboard'));

        $response->assertOk();
        // Ensure sidebar profile brief is removed
        $response->assertDontSee('Profil singkat sidebar');
    }

    public function test_ppdb_registration_filter_by_status(): void
    {
        SantriRegistration::create([
            'registration_number' => 'REG-001',
            'student_name' => 'Calon Pending',
            'guardian_name' => 'Wali Calon',
            'guardian_phone' => '081234567890',
            'birth_date' => '2010-01-01',
            'residential_address' => 'Lamongan',
            'education_qualification' => 'SD/MI',
            'gender' => 'L',
            'target_class' => 'MTs Nurul Khidmah',
            'status' => 'pending',
            'branch' => 'Pusat',
            'academic_year' => '2026/2027',
        ]);

        SantriRegistration::create([
            'registration_number' => 'REG-002',
            'student_name' => 'Calon Approved',
            'guardian_name' => 'Wali Calon',
            'guardian_phone' => '081234567890',
            'birth_date' => '2010-01-01',
            'residential_address' => 'Lamongan',
            'education_qualification' => 'SD/MI',
            'gender' => 'L',
            'target_class' => 'MTs Nurul Khidmah',
            'status' => 'accepted',
            'branch' => 'Pusat',
            'academic_year' => '2026/2027',
        ]);

        // Filter pending
        $respPending = $this->actingAs($this->adminTu)
            ->get(route('admin.registrations.index', ['status' => 'pending']));
        $respPending->assertOk();
        $respPending->assertSee('Calon Pending');
        $respPending->assertDontSee('Calon Approved');

        // Filter accepted / approved
        $respApproved = $this->actingAs($this->adminTu)
            ->get(route('admin.registrations.index', ['status' => 'accepted']));
        $respApproved->assertOk();
        $respApproved->assertSee('Calon Approved');
        $respApproved->assertDontSee('Calon Pending');
    }

    public function test_santri_management_search_filter_and_profile_modal(): void
    {
        // Search by NISN
        $response = $this->actingAs($this->adminTu)
            ->get(route('admin.santri.index', ['search' => '0098765432']));

        $response->assertOk();
        $response->assertSee('Ahmad Faiz Test');
        $response->assertDontSee('Budi Santoso Test');

        // Filter by status Lulus
        $respLulus = $this->actingAs($this->adminTu)
            ->get(route('admin.santri.index', ['status' => 'Lulus']));
        $respLulus->assertOk();
        $respLulus->assertSee('Budi Santoso Test');
        $respLulus->assertDontSee('Ahmad Faiz Test');

        // Ensure photo modal and initials method exist
        $this->assertEquals('AF', $this->santri1->initials());
        $response->assertSee('showPhotoModal');
    }

    public function test_schedule_filter_by_all_years(): void
    {
        $response = $this->actingAs($this->adminTu)
            ->get(route('admin.schedules.index', ['tahun_ajaran_id' => 'all']));

        $response->assertOk();
        $response->assertSee('-- Semua Thn Ajaran --');
    }

    public function test_portal_wali_search_does_not_stick_to_stale_santri(): void
    {
        $response = $this->actingAs($this->adminTu)
            ->get(route('wali.dashboard', [
                'search' => 'Budi',
                'santri_id' => $this->santri1->id, // Stale santri_id
            ]));

        $response->assertOk();
        // The resolved santri should be Budi, not Ahmad
        $response->assertViewHas('santri', function ($resolvedSantri) {
            return $resolvedSantri && $resolvedSantri->id === $this->santri2->id;
        });
    }

    public function test_reports_page_contains_download_pdf_buttons(): void
    {
        $response = $this->actingAs($this->adminTu)
            ->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Download PDF');
        $response->assertSee('btn-quick-rapor-download');
    }
}
