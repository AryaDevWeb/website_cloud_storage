<?php

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function userWithRole(string $role, UserStatus $status): User
{
    $user = User::factory()->create(['status' => $status]);
    $user->assignRole($role);

    return $user;
}

test('non-admin users cannot access the admin panel', function (string $role) {
    $response = $this->actingAs(userWithRole($role, UserStatus::ACTIVE))
        ->get('/admin');

    expect($response->status())->not->toBe(200);
})->with(['siswa', 'guru']);

test('pending and suspended admin users cannot access the admin panel', function (UserStatus $status) {
    $response = $this->actingAs(userWithRole('admin', $status))
        ->get('/admin');

    expect($response->status())->not->toBe(200);
})->with([UserStatus::PENDING, UserStatus::SUSPENDED]);

test('active admin users can access the admin panel', function () {
    $response = $this->actingAs(userWithRole('admin', UserStatus::ACTIVE))
        ->get('/admin');

    $response->assertOk();
});
