<?php

use App\Enums\UserStatus;
use App\Models\User;

test('authenticated users can render the storage workspace', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $response = $this->actingAs($user)->get('/storage');

    $response->assertOk()
        ->assertSee('Ruang file Anda')
        ->assertSee('Kuota penyimpanan');
});

test('active non-admin users can render the storage workspace inside Filament', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $response = $this->actingAs($user)->get('/workspace/storage-workspace');

    $response->assertOk()
        ->assertSee('Ruang file Anda')
        ->assertSee('Cloud Storage Sekolah');
});

test('authenticated users can render each storage section', function (string $section) {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $response = $this->actingAs($user)->get('/storage/'.$section);

    $response->assertOk()->assertSee('Cloud storage sekolah');
})->with(['shared', 'sent', 'trash']);
