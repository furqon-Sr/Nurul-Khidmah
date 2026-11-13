<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seluruh role jajaran yayasan selain wali santri dapat mengakses halaman ubah foto profil,
     * mengunggah foto baru, mengganti foto lama, dan menghapus foto profilnya sendiri.
     */
    public function test_all_foundation_staff_roles_can_manage_their_own_profile_photo(): void
    {
        Storage::fake('public');

        $staffRoles = [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_PIMPINAN,
            User::ROLE_KEPALA_SEKOLAH,
            User::ROLE_PENGURUS_AKADEMIK,
            User::ROLE_TENAGA_PENGAJAR,
        ];

        foreach ($staffRoles as $index => $role) {
            $user = User::create([
                'name' => "Staf {$role}",
                'email' => "staff.{$role}.{$index}@nurulkhidmah.org",
                'password' => Hash::make('password'),
                'role' => $role,
                'email_verified_at' => now(),
            ]);

            // 1. Dapat mengakses halaman profil & melihat tautan menu di header
            $this->actingAs($user)
                ->get(route('staff.profile.edit'))
                ->assertOk()
                ->assertSee('Foto & Profil Saya')
                ->assertSee('Ubah Foto Profil');

            // 2. Dapat mengunggah foto profil pertama
            $firstPhoto = UploadedFile::fake()->image("avatar-{$role}-1.jpg", 300, 300);
            $this->actingAs($user)
                ->post(route('staff.profile.avatar.update'), ['avatar' => $firstPhoto])
                ->assertRedirect(route('staff.profile.edit'))
                ->assertSessionHas('success');

            $user->refresh();
            $firstPath = $user->avatar;
            $this->assertNotNull($firstPath);
            Storage::disk('public')->assertExists($firstPath);

            // 3. Mengganti dengan foto baru (menggunakan data hasil crop base64) menghapus file lama
            $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
            $dataUri = 'data:image/png;base64,'.base64_encode($pngBinary);

            $this->actingAs($user)
                ->post(route('staff.profile.avatar.update'), ['avatar_cropped_data' => $dataUri])
                ->assertRedirect(route('staff.profile.edit'))
                ->assertSessionHas('success');

            $user->refresh();
            $secondPath = $user->avatar;
            $this->assertNotNull($secondPath);
            $this->assertNotSame($firstPath, $secondPath);
            Storage::disk('public')->assertMissing($firstPath);
            Storage::disk('public')->assertExists($secondPath);

            // 4. Dapat menghapus foto profil sendiri
            $this->actingAs($user)
                ->delete(route('staff.profile.avatar.destroy'))
                ->assertRedirect(route('staff.profile.edit'))
                ->assertSessionHas('success');

            $user->refresh();
            $this->assertNull($user->avatar);
            Storage::disk('public')->assertMissing($secondPath);
        }
    }

    /**
     * Akun Wali Santri (role santri) tidak diperbolehkan mengakses fitur ubah foto profil jajaran yayasan.
     */
    public function test_wali_santri_cannot_access_or_update_staff_profile_photo(): void
    {
        Storage::fake('public');

        $wali = User::create([
            'name' => 'Wali Santri Uji',
            'email' => 'wali.uji@nurulkhidmah.org',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SANTRI,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($wali)
            ->get(route('staff.profile.edit'))
            ->assertForbidden();

        $this->actingAs($wali)
            ->post(route('staff.profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('wali.jpg', 200, 200),
            ])
            ->assertForbidden();

        $this->actingAs($wali)
            ->delete(route('staff.profile.avatar.destroy'))
            ->assertForbidden();

        $this->actingAs($wali)
            ->get(route('wali.dashboard'))
            ->assertOk()
            ->assertDontSee('Ubah Foto Profil');
    }
}
