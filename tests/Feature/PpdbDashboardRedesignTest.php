<?php

use App\Livewire\Admin\RegistrationTable;
use App\Models\AdminActivityLog;
use App\Models\EducationProgram;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => User::ROLE_SUPER_ADMIN,
    ]);

    TahunAjaran::create([
        'nama_tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'status_aktif' => true,
        'tanggal_mulai' => '2026-07-01',
        'tanggal_selesai' => '2027-06-30',
    ]);

    EducationProgram::create([
        'name' => 'Madrasah Tsanawiyah',
        'slug' => 'madrasah-tsanawiyah',
        'description' => 'Program MTs',
        'is_active' => true,
        'order' => 1,
    ]);

    $this->regPending = SantriRegistration::create([
        'registration_number' => 'PPDB-2026-001',
        'student_name' => 'Ahmad Zaki',
        'guardian_name' => 'H. Abdullah',
        'guardian_phone' => '08123456001',
        'birth_date' => '2012-03-10',
        'residential_address' => 'Lamongan',
        'education_qualification' => 'SD/MI',
        'boarding_status' => 'Mukim',
        'gender' => 'L',
        'target_class' => 'Madrasah Tsanawiyah',
        'status' => 'pending',
        'branch' => 'Lamongan',
        'academic_year' => '2026/2027',
        'created_at' => now()->subDays(2),
    ]);

    $this->regAccepted = SantriRegistration::create([
        'registration_number' => 'PPDB-2026-002',
        'student_name' => 'Siti Aisyah',
        'guardian_name' => 'Hj. Aminah',
        'guardian_phone' => '08123456002',
        'birth_date' => '2012-06-15',
        'residential_address' => 'Gresik',
        'education_qualification' => 'SD/MI',
        'boarding_status' => 'Mukim',
        'gender' => 'P',
        'target_class' => 'Madrasah Aliyah',
        'status' => 'accepted',
        'branch' => 'Lamongan',
        'academic_year' => '2026/2027',
        'created_at' => now()->subDay(),
    ]);

    $this->regRejected = SantriRegistration::create([
        'registration_number' => 'PPDB-2026-003',
        'student_name' => 'Budi Pratama',
        'guardian_name' => 'Bpk. Budi',
        'guardian_phone' => '08123456003',
        'birth_date' => '2011-11-20',
        'residential_address' => 'Tuban',
        'education_qualification' => 'SD/MI',
        'boarding_status' => 'Tidak Mukim',
        'gender' => 'L',
        'target_class' => 'Madrasah Tsanawiyah',
        'status' => 'rejected',
        'branch' => 'Lamongan',
        'academic_year' => '2026/2027',
        'created_at' => now(),
    ]);
});

test('kpi cards use neutral uniform surface without loud gradients and filter table on click', function () {
    $component = Livewire::actingAs($this->admin)->test(RegistrationTable::class);

    $component
        ->assertSee('h-[102px]', false)
        ->assertDontSee('bg-gradient-to-br', false)
        ->assertDontSee('ring-4 ring-offset-2', false)
        ->call('setStatusFilter', 'pending')
        ->assertSee('Ahmad Zaki')
        ->assertDontSee('Siti Aisyah')
        ->assertDontSee('Budi Pratama')
        ->call('setStatusFilter', 'rejected')
        ->assertSee('Budi Pratama')
        ->assertDontSee('Ahmad Zaki')
        ->call('setStatusFilter', '')
        ->assertSee('Ahmad Zaki')
        ->assertSee('Siti Aisyah')
        ->assertSee('Budi Pratama');
});

test('compact filter toolbar shows reset filter only when active and export csv in table header', function () {
    Livewire::actingAs($this->admin)
        ->test(RegistrationTable::class)
        ->assertSee('h-[42px]', false)
        ->assertSee('Export CSV')
        ->assertDontSee('Reset Filter')
        ->set('search', 'Zaki')
        ->assertSee('Reset Filter')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertDontSee('Reset Filter');
});

test('clean table supports sorting on registration_number, student_name, created_at, and status plus pagination count', function () {
    Livewire::actingAs($this->admin)
        ->test(RegistrationTable::class)
        ->assertDontSee('table-builder', false)
        ->assertSee('role="region"', false)
        ->assertSee('tabindex="0"', false)
        ->assertSee('Menampilkan')
        ->call('sortBy', 'student_name')
        ->assertSet('sortField', 'student_name')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Ahmad Zaki', 'Budi Pratama', 'Siti Aisyah'])
        ->call('sortBy', 'student_name')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Siti Aisyah', 'Budi Pratama', 'Ahmad Zaki'])
        ->call('sortBy', 'registration_number')
        ->assertSet('sortField', 'registration_number')
        ->assertSeeInOrder(['PPDB-2026-001', 'PPDB-2026-002', 'PPDB-2026-003'])
        ->call('sortBy', 'status')
        ->assertSet('sortField', 'status');
});

test('three-dot action popover exposes detail, edit, status, and red delete with native confirmation dialogs', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.registrations.index'));

    $response->assertOk();
    $response->assertSee('popovertarget="reg-panel-'.$this->regPending->id.'"', false);
    $response->assertSee('Lihat Detail');
    $response->assertSee('Edit Data');
    $response->assertSee('Ubah Status');
    $response->assertSee('Hapus');
    $response->assertSee('id="ppdb-status-dialog"', false);
    $response->assertSee('id="ppdb-delete-dialog"', false);
});

test('updating status and soft deleting via livewire writes audit logs and preserves record via soft delete', function () {
    Livewire::actingAs($this->admin)
        ->test(RegistrationTable::class)
        ->call('updateSingleStatus', $this->regPending->id, 'accepted');

    expect($this->regPending->fresh()->status)->toBe('accepted')
        ->and($this->regPending->fresh()->santri_id)->not->toBeNull();

    expect(AdminActivityLog::where('action', 'update')
        ->where('target_type', 'SantriRegistration')
        ->where('target_id', $this->regPending->id)
        ->exists())->toBeTrue();

    // Soft delete via Livewire action
    Livewire::actingAs($this->admin)
        ->test(RegistrationTable::class)
        ->call('deleteRegistration', $this->regRejected->id)
        ->assertSee('Data pendaftar Budi Pratama (PPDB-2026-003) berhasil dihapus')
        ->assertDontSee('reg-row-'.$this->regRejected->id, false);

    $this->assertSoftDeleted('santri_registrations', [
        'id' => $this->regRejected->id,
    ]);

    expect(AdminActivityLog::where('action', 'delete')
        ->where('target_type', 'SantriRegistration')
        ->where('target_id', $this->regRejected->id)
        ->exists())->toBeTrue();
});

test('edit registration page loads and updates registration biodata with audit log', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.registrations.edit', $this->regPending))
        ->assertOk()
        ->assertSee('Edit Data Pendaftar')
        ->assertSee('Ahmad Zaki');

    $this->actingAs($this->admin)
        ->put(route('admin.registrations.update', $this->regPending), [
            'student_name' => 'Ahmad Zaki Mubarak',
            'nisn' => '0012345678',
            'gender' => 'L',
            'birth_place' => 'Lamongan',
            'birth_date' => '2012-03-10',
            'previous_school' => 'MI Negeri 1 Lamongan',
            'boarding_status' => 'Mukim',
            'target_class' => 'Madrasah Tsanawiyah',
            'academic_year' => '2026/2027',
            'residential_address' => 'Jl. Sunan Drajat No. 10, Lamongan',
            'father_name' => 'H. Abdullah',
            'mother_name' => 'Hj. Siti',
            'guardian_name' => 'H. Abdullah',
            'guardian_occupation' => 'Wiraswasta',
            'guardian_phone' => '08123456001',
            'guardian_email' => 'abdullah@example.com',
            'guardian_address' => 'Lamongan',
        ])
        ->assertRedirect(route('admin.registrations.index'));

    expect($this->regPending->fresh()->student_name)->toBe('Ahmad Zaki Mubarak');

    expect(AdminActivityLog::where('action', 'update')
        ->where('target_type', 'SantriRegistration')
        ->where('target_id', $this->regPending->id)
        ->exists())->toBeTrue();
});

test('soft deleted santri does not cause nis collision when accepting new registration', function () {
    // Accept first registration to generate NIS (e.g. 20260001)
    Livewire::actingAs($this->admin)
        ->test(RegistrationTable::class)
        ->call('updateSingleStatus', $this->regPending->id, 'accepted');

    $firstSantri = Santri::findOrFail($this->regPending->fresh()->santri_id);
    $firstNis = $firstSantri->nis;

    // Soft delete that Santri via SantriManagementController
    $this->actingAs($this->admin)
        ->delete(route('admin.santri.destroy', $firstSantri))
        ->assertRedirect(route('admin.santri.index'));

    $this->assertSoftDeleted('santris', ['id' => $firstSantri->id]);

    // Accepting another registration must generate the next NIS without unique constraint collision
    Livewire::actingAs($this->admin)
        ->test(RegistrationTable::class)
        ->call('updateSingleStatus', $this->regRejected->id, 'accepted');

    $secondSantri = Santri::findOrFail($this->regRejected->fresh()->santri_id);
    expect($secondSantri->nis)->not->toBe($firstNis);
});

test('sidebar supports 264px to 72px collapse, accordions, single active state, and 18px icons', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.registrations.index'));

    $response->assertOk();
    $response->assertSee('lg:w-[264px]', false);
    $response->assertSee('lg:w-[72px]', false);
    $response->assertSee('toggleSidebarCollapse()', false);
    $response->assertSee('sidebar-item nav-active', false);
    $response->assertSee('size-[18px]', false);
    $response->assertSee('x-collapse', false);
});
