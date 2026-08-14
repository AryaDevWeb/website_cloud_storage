<?php

use App\Enums\UserStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeVolt('auth.register');
});

test('student registration succeeds with valid nisn', function () {
    $component = Volt::test('auth.register')
        ->set('form.name', 'Siswa Baru')
        ->set('form.email', 'siswa.baru@example.com')
        ->set('form.role', 'siswa')
        ->set('form.nisn', '0098765432')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('auth.pending'));

    $this->assertDatabaseHas('users', [
        'email'  => 'siswa.baru@example.com',
        'status' => UserStatus::PENDING->value,
    ]);

    $this->assertDatabaseHas('student_profiles', [
        'nisn' => '0098765432',
    ]);
});

test('teacher registration succeeds with valid nip', function () {
    $component = Volt::test('auth.register')
        ->set('form.name', 'Guru Baru')
        ->set('form.email', 'guru.baru@example.com')
        ->set('form.role', 'guru')
        ->set('form.nip', '198501012015011002')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('auth.pending'));

    $this->assertDatabaseHas('users', [
        'email'  => 'guru.baru@example.com',
        'status' => UserStatus::PENDING->value,
    ]);

    $this->assertDatabaseHas('teacher_profiles', [
        'nip' => '198501012015011002',
    ]);
});

test('admin role cannot be selected via public registration', function () {
    $component = Volt::test('auth.register')
        ->set('form.name', 'Fake Admin')
        ->set('form.email', 'admin.fake@example.com')
        ->set('form.role', 'admin')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertHasErrors(['form.role']);
});

test('duplicate email is rejected', function () {
    Volt::test('auth.register')
        ->set('form.name', 'Siswa 1')
        ->set('form.email', 'siswa.demo@example.com')
        ->set('form.role', 'siswa')
        ->set('form.nisn', '0011223344')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password')
        ->call('register');

    $component = Volt::test('auth.register')
        ->set('form.name', 'Siswa 2')
        ->set('form.email', 'siswa.demo@example.com')
        ->set('form.role', 'siswa')
        ->set('form.nisn', '0011223345')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertHasErrors(['form.email']);
});

test('duplicate nisn is rejected for student', function () {
    Volt::test('auth.register')
        ->set('form.name', 'Siswa 1')
        ->set('form.email', 'siswa1@example.com')
        ->set('form.role', 'siswa')
        ->set('form.nisn', '0011223344')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password')
        ->call('register');

    $component = Volt::test('auth.register')
        ->set('form.name', 'Siswa 2')
        ->set('form.email', 'siswa2@example.com')
        ->set('form.role', 'siswa')
        ->set('form.nisn', '0011223344')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertHasErrors(['form.nisn']);
});

test('duplicate nip is rejected for teacher', function () {
    Volt::test('auth.register')
        ->set('form.name', 'Guru 1')
        ->set('form.email', 'guru1@example.com')
        ->set('form.role', 'guru')
        ->set('form.nip', '198001012010011001')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password')
        ->call('register');

    $component = Volt::test('auth.register')
        ->set('form.name', 'Guru 2')
        ->set('form.email', 'guru2@example.com')
        ->set('form.role', 'guru')
        ->set('form.nip', '198001012010011001')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertHasErrors(['form.nip']);
});
