<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\SantriTable;
use App\Models\EducationalUnit;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SantriTableTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Kelas $kelasA;
    private Kelas $kelasB;
    private Santri $santri1;
    private Santri $santri2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $ta = TahunAjaran::create([
            'nama_tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'tanggal_mulai' => '2026-07-15',
            'tanggal_selesai' => '2026-12-20',
        ]);

        $unit = EducationalUnit::create([
            'kode_unit' => 'MTS-LW',
            'nama_unit' => 'MTs Nurul Khidmah Livewire',
            'jenjang' => 'MTs',
            'is_active' => true,
        ]);

        $this->kelasA = Kelas::create([
            'kode_kelas' => '7A-LW',
            'nama_kelas' => 'Kelas 7A Livewire',
            'tingkat_kelas' => '7',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $this->kelasB = Kelas::create([
            'kode_kelas' => '7B-LW',
            'nama_kelas' => 'Kelas 7B Livewire',
            'tingkat_kelas' => '7',
            'tahun_ajaran_id' => $ta->id,
            'educational_unit_id' => $unit->id,
        ]);

        $this->santri1 = Santri::create([
            'user_id' => $this->admin->id,
            'kelas_id' => $this->kelasA->id,
            'educational_unit_id' => $unit->id,
            'nis' => '99000001',
            'nisn' => '0011223344',
            'nama_lengkap' => 'Zaidan Al-Farisi',
            'status_santri' => 'Aktif',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $this->santri2 = Santri::create([
            'user_id' => $this->admin->id,
            'kelas_id' => $this->kelasB->id,
            'educational_unit_id' => $unit->id,
            'nis' => '99000002',
            'nisn' => '0055667788',
            'nama_lengkap' => 'Bilal Al-Habasyi',
            'status_santri' => 'Lulus',
            'jenis_kelamin' => 'Laki-laki',
        ]);
    }

    public function test_santri_table_component_renders_correctly(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SantriTable::class)
            ->assertStatus(200)
            ->assertSee('Zaidan Al-Farisi')
            ->assertSee('Bilal Al-Habasyi')
            ->assertSee('Total: 2 santri');
    }

    public function test_realtime_search_by_name(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SantriTable::class)
            ->set('search', 'Zaidan')
            ->assertSee('Zaidan Al-Farisi')
            ->assertDontSee('Bilal Al-Habasyi')
            ->assertSee('Total: 1 santri');
    }

    public function test_realtime_search_by_nisn(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SantriTable::class)
            ->set('search', '0055667788')
            ->assertSee('Bilal Al-Habasyi')
            ->assertDontSee('Zaidan Al-Farisi')
            ->assertSee('Total: 1 santri');
    }

    public function test_realtime_filter_by_status(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SantriTable::class)
            ->set('status', 'Lulus')
            ->assertSee('Bilal Al-Habasyi')
            ->assertDontSee('Zaidan Al-Farisi');
    }

    public function test_realtime_filter_by_kelas(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SantriTable::class)
            ->set('kelas_id', (string) $this->kelasA->id)
            ->assertSee('Zaidan Al-Farisi')
            ->assertDontSee('Bilal Al-Habasyi');
    }

    public function test_reset_filters(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SantriTable::class)
            ->set('search', 'Zaidan')
            ->set('status', 'Aktif')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('kelas_id', '')
            ->assertSee('Zaidan Al-Farisi')
            ->assertSee('Bilal Al-Habasyi');
    }
}
