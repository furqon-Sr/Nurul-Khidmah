<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\RegistrationTable;
use App\Models\SantriRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTableTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        SantriRegistration::create([
            'registration_number' => 'REG-101',
            'student_name' => 'Fulan bin Fulan',
            'guardian_name' => 'Wali Fulan',
            'guardian_phone' => '0811111111',
            'birth_date' => '2010-01-01',
            'residential_address' => 'Lamongan',
            'education_qualification' => 'SD',
            'gender' => 'L',
            'target_class' => 'Madrasah Tsanawiyah',
            'status' => 'pending',
            'branch' => 'Lamongan',
            'academic_year' => '2026/2027',
        ]);

        SantriRegistration::create([
            'registration_number' => 'REG-102',
            'student_name' => 'Fatimah Az-Zahra',
            'guardian_name' => 'Wali Fatimah',
            'guardian_phone' => '0822222222',
            'birth_date' => '2010-02-02',
            'residential_address' => 'Lamongan',
            'education_qualification' => 'MI',
            'gender' => 'P',
            'target_class' => 'Madrasah Aliyah',
            'status' => 'accepted',
            'branch' => 'Lamongan',
            'academic_year' => '2026/2027',
        ]);
    }

    public function test_registration_table_renders(): void
    {
        Livewire::actingAs($this->admin)
            ->test(RegistrationTable::class)
            ->assertStatus(200)
            ->assertSee('Fulan bin Fulan')
            ->assertSee('Fatimah Az-Zahra');
    }

    public function test_realtime_search_pendaftar(): void
    {
        Livewire::actingAs($this->admin)
            ->test(RegistrationTable::class)
            ->set('search', 'Fatimah')
            ->assertSee('Fatimah Az-Zahra')
            ->assertDontSee('Fulan bin Fulan');
    }

    public function test_realtime_filter_status(): void
    {
        Livewire::actingAs($this->admin)
            ->test(RegistrationTable::class)
            ->set('status', 'pending')
            ->assertSee('Fulan bin Fulan')
            ->assertDontSee('Fatimah Az-Zahra');
    }
}
