<?php

use App\Enums\UserStatus;
use App\Models\User;

test('active user can access protected route', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

test('pending user is redirected to account pending page', function () {
    $user = User::factory()->create(['status' => UserStatus::PENDING]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('auth.pending'));
});

test('suspended user is logged out and redirected to login', function () {
    $user = User::factory()->create(['status' => UserStatus::SUSPENDED]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
