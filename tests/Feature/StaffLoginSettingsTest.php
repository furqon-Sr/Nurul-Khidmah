<?php

use App\Models\PageContent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    PageContent::whereIn('section_key', [
        'staff_login_title', 'staff_login_subtitle', 'staff_login_image',
        'wali_login_title', 'wali_login_subtitle', 'wali_login_image',
    ])->delete();
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

test('wali login page renders with split screen and emerald gradient elements', function () {
    $response = $this->get(route('wali.login'));

    $response->assertOk();
    $response->assertSee('Portal Wali Santri');
    $response->assertSee('NIS Ananda atau Email Wali');
    $response->assertSee('Masuk ke Portal Wali Santri');
});

test('admin can update wali login settings and upload custom banner image', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'super_admin',
    ]);

    $file = UploadedFile::fake()->image('wali_banner.jpg', 800, 1000);

    $response = $this->actingAs($admin)->post(route('settings.update'), [
        'form_type' => 'login_wali',
        'wali_login_title' => 'Portal Wali Santri Terpadu',
        'wali_login_subtitle' => 'Pemantauan Prestasi & Kehadiran Santri Realtime.',
        'wali_login_image' => $file,
        'wali_login_image_crop_skip' => '1',
    ]);

    $response->assertRedirect(route('settings.index') . '?tab=login_wali');
    $response->assertSessionHas('success');

    $titleContent = PageContent::where('section_key', 'wali_login_title')->first();
    $subtitleContent = PageContent::where('section_key', 'wali_login_subtitle')->first();
    $imageContent = PageContent::where('section_key', 'wali_login_image')->first();

    expect($titleContent?->content_text)->toBe('Portal Wali Santri Terpadu');
    expect($subtitleContent?->content_text)->toBe('Pemantauan Prestasi & Kehadiran Santri Realtime.');
    expect($imageContent?->image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($imageContent->image_path);

    auth()->logout();

    // Verify wali login page displays custom text and custom image
    $loginResponse = $this->get(route('wali.login'));
    $loginResponse->assertOk();
    $loginResponse->assertSee('Portal Wali Santri Terpadu');
    $loginResponse->assertSee('Pemantauan Prestasi & Kehadiran Santri Realtime.');
    $loginResponse->assertSee($imageContent->image_path);
});

test('admin can remove custom wali login banner image to restore default graphic', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'super_admin',
    ]);

    $filePath = 'contents/test_wali_banner.jpg';
    Storage::disk('public')->put($filePath, 'dummy content');
    PageContent::updateOrCreate(
        ['section_key' => 'wali_login_image'],
        ['image_path' => $filePath]
    );

    $response = $this->actingAs($admin)->post(route('settings.update'), [
        'form_type' => 'login_wali',
        'remove_wali_login_image' => '1',
    ]);

    $response->assertRedirect(route('settings.index') . '?tab=login_wali');

    $imageContent = PageContent::where('section_key', 'wali_login_image')->first();
    expect($imageContent?->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($filePath);
});
