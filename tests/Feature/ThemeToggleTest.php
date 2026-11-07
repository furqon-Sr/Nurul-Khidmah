<?php

use App\Models\User;

test('theme toggle component renders on dashboard with reference ui elements', function () {
    $user = User::factory()->pengurusAkademik()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('aria-label="Pengaturan Tema Tampilan"', false);
    $response->assertSee('Light');
    $response->assertSee('Dark');
    $response->assertSee('setTheme(false)', false);
    $response->assertSee('setTheme(true)', false);
    // Sun icon circle
    $response->assertSee('<circle cx="12" cy="12" r="4.5"', false);
    // Moon icon path
    $response->assertSee('M20.354 15.354A9 9', false);
});

test('theme toggle component renders in header toolbar and not in sidebar', function () {
    $user = User::factory()->pengurusAkademik()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    // Verify that the fullWidth toggle in the sidebar is removed
    $response->assertDontSee('grid grid-cols-2', false);
    // Verify that the header toolbar toggle is present
    $response->assertSee('inline-flex items-center', false);
});
