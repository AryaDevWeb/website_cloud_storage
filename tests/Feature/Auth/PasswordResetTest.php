<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response
        ->assertOk()
        ->assertSeeVolt('auth.forgot-password');
});

test('reset password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->get('/reset-password/test-token?email='.$user->email);

    $response
        ->assertOk()
        ->assertSeeVolt('auth.reset-password');
});
