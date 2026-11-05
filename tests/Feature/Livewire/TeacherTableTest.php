<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\TeacherTable;
use App\Models\Pengajar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherTableTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $user1 = User::factory()->create([
            'email' => 'ustaz.faiz@example.com',
            'role' => User::ROLE_TENAGA_PENGAJAR,
        ]);

        Pengajar::create([
            'user_id' => $user1->id,
            'nomor_identitas' => 'NIY-001',
            'nama_lengkap' => 'Ustaz Ahmad Faiz',
            'jenis_kelamin' => 'Laki-laki',
            'status_pengajar' => 'Aktif',
        ]);

        $user2 = User::factory()->create([
            'email' => 'ustazah.maryam@example.com',
            'role' => User::ROLE_TENAGA_PENGAJAR,
        ]);

        Pengajar::create([
            'user_id' => $user2->id,
            'nomor_identitas' => 'NIY-002',
            'nama_lengkap' => 'Ustazah Maryam',
            'jenis_kelamin' => 'Perempuan',
            'status_pengajar' => 'Non-Aktif',
        ]);
    }

    public function test_teacher_table_renders(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TeacherTable::class)
            ->assertStatus(200)
            ->assertSee('Ustaz Ahmad Faiz')
            ->assertSee('Ustazah Maryam');
    }

    public function test_realtime_search_teacher(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TeacherTable::class)
            ->set('search', 'Faiz')
            ->assertSee('Ustaz Ahmad Faiz')
            ->assertDontSee('Ustazah Maryam');
    }

    public function test_realtime_filter_status_teacher(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TeacherTable::class)
            ->set('status', 'Non-Aktif')
            ->assertSee('Ustazah Maryam')
            ->assertDontSee('Ustaz Ahmad Faiz');
    }
}
