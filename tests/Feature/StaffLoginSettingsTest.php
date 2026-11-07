<?php

use App\Models\PageContent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    PageContent::whereIn('section_key', ['staff_login_title', 'staff_login_subtitle', 'staff_login_image'])->delete();
    Cache::forget('site_global_data');
});

test('staff login page renders with reference ui elements', function () {
    $response = $this->get(route('staff.login'));

    $response->assertOk();
    $response->assertSee('Welcome Back');
    $response->assertSee('Sign in');
    $response->assertSee('Forgot Password?');
});

test('admin can update staff login settings and upload custom banner image', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'super_admin',
    ]);

    $file = UploadedFile::fake()->image('staff_banner.jpg', 800, 1000);

    $response = $this->actingAs($admin)->post(route('settings.update'), [
        'form_type' => 'login_staf',
        'staff_login_title' => 'Selamat Datang Kembali',
        'staff_login_subtitle' => 'Portal Resmi Tenaga Pendidik & Kependidikan Nurul Khidmah.',
        'staff_login_image' => $file,
        'staff_login_image_crop_skip' => '1',
    ]);

    $response->assertRedirect(route('settings.index') . '?tab=login_staf');
    $response->assertSessionHas('success');

    $titleContent = PageContent::where('section_key', 'staff_login_title')->first();
    $subtitleContent = PageContent::where('section_key', 'staff_login_subtitle')->first();
    $imageContent = PageContent::where('section_key', 'staff_login_image')->first();

    expect($titleContent?->content_text)->toBe('Selamat Datang Kembali');
    expect($subtitleContent?->content_text)->toBe('Portal Resmi Tenaga Pendidik & Kependidikan Nurul Khidmah.');
    expect($imageContent?->image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($imageContent->image_path);

    auth()->logout();

    // Verify staff login page displays custom text and custom image
    $loginResponse = $this->get(route('staff.login'));
    $loginResponse->assertOk();
    $loginResponse->assertSee('Selamat Datang Kembali');
    $loginResponse->assertSee('Portal Resmi Tenaga Pendidik & Kependidikan Nurul Khidmah.');
    $loginResponse->assertSee($imageContent->image_path);
});

test('admin can remove custom staff login banner image to restore default graphic', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'super_admin',
    ]);

    // Create existing image record
    $filePath = 'contents/test_staff_banner.jpg';
    Storage::disk('public')->put($filePath, 'dummy content');
    PageContent::updateOrCreate(
        ['section_key' => 'staff_login_image'],
        ['image_path' => $filePath]
    );

    $response = $this->actingAs($admin)->post(route('settings.update'), [
        'form_type' => 'login_staf',
        'remove_staff_login_image' => '1',
    ]);

    $response->assertRedirect(route('settings.index') . '?tab=login_staf');

    $imageContent = PageContent::where('section_key', 'staff_login_image')->first();
    expect($imageContent?->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($filePath);
});
