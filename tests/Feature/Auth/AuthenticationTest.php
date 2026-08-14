<?php

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('login screen can be rendered', function () {

    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('auth.login');
});

test('active user can authenticate using login screen', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $component = Volt::test('auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('user cannot authenticate with invalid password', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $component = Volt::test('auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('navigation menu can be rendered for active user', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

test('user can logout', function () {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $this->actingAs($user);

    $component = Volt::test('layout.navigation');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
