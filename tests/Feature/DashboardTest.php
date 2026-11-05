<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated staff can visit the dashboard', function () {
    $user = User::factory()->pengurusAkademik()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('authenticated santri wali is redirected to wali dashboard', function () {
    $wali = User::factory()->wali()->create();
    $this->actingAs($wali);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('wali.dashboard'));
});