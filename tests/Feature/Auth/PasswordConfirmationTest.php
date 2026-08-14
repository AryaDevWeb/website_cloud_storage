<?php

/** @var Tests\TestCase $this */

use App\Enums\UserStatus;
use App\Models\User;
use Livewire\Volt\Volt;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $response = $this->actingAs($user)->get('/confirm-password');

    $response
        ->assertOk()
        ->assertSeeVolt('auth.confirm-password');
});

test('password can be confirmed', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $this->actingAs($user);

    $component = Volt::test('auth.confirm-password')
        ->set('password', 'password');

    $component->call('confirmPassword');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));
});

test('password is not confirmed with invalid password', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $this->actingAs($user);

    $component = Volt::test('auth.confirm-password')
        ->set('password', 'wrong-password');

    $component->call('confirmPassword');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();
});
