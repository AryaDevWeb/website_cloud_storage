<?php

use App\Enums\UserStatus;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\StudentRecord;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function academicUserWithRole(string $role): User
{
    $user = User::factory()->create([
        'status' => UserStatus::ACTIVE,
        'password' => Hash::make('password'),
    ]);
    $user->assignRole($role);

    return $user;
}

test('admin can create update and delete majors and classrooms', function () {
    $admin = academicUserWithRole('admin');
    $this->actingAs($admin);

    $major = Major::create([
        'code' => 'AKL', 'name' => 'Akuntansi dan Keuangan Lembaga',
        'short_name' => 'AKL', 'is_active' => true,
    ]);

    expect($admin->can('create', Major::class))->toBeTrue()
        ->and($admin->can('update', $major))->toBeTrue()
        ->and($admin->can('delete', $major))->toBeTrue();

    $major->update(['name' => 'Akuntansi Keuangan Lembaga']);
    expect($major->fresh()->name)->toBe('Akuntansi Keuangan Lembaga');

    $classroom = Classroom::create([
        'code' => 'X-AKL-1', 'name' => 'X AKL 1', 'major_id' => $major->id,
        'grade_level' => 10, 'academic_year' => '2026/2027', 'capacity' => 36,
        'is_active' => true,
    ]);

    expect($admin->can('create', Classroom::class))->toBeTrue()
        ->and($admin->can('update', $classroom))->toBeTrue()
        ->and($admin->can('delete', $classroom))->toBeTrue();

    $classroom->update(['name' => 'X AKL 1 Updated']);
    expect($classroom->fresh()->name)->toBe('X AKL 1 Updated');

    $classroom->delete();
    $major->delete();

    expect(Classroom::find($classroom->id))->toBeNull()
        ->and(Major::find($major->id))->toBeNull();
});

test('admin can open the academic Filament resources', function () {
    $admin = academicUserWithRole('admin');

    $this->actingAs($admin)->get('/admin/majors')->assertOk();
    $this->actingAs($admin)->get('/admin/classrooms')->assertOk();
});

test('non-admin users are denied access to academic Filament resources', function (string $role) {
    $user = academicUserWithRole($role);

    $this->actingAs($user)->get('/admin/majors')->assertStatus(403);
    $this->actingAs($user)->get('/admin/classrooms')->assertStatus(403);
})->with(['guru', 'siswa']);

test('classroom belongs to the expected major', function () {
    $major = Major::create([
        'code' => 'RPL-TEST', 'name' => 'Rekayasa Perangkat Lunak',
        'short_name' => 'RPL', 'is_active' => true,
    ]);
    $classroom = Classroom::create([
        'code' => 'X-RPL-TEST', 'name' => 'X RPL Test', 'major_id' => $major->id,
        'grade_level' => 10, 'academic_year' => '2026/2027', 'capacity' => 36,
        'is_active' => true,
    ]);

    expect($classroom->major)->toBeInstanceOf(Major::class)
        ->and($classroom->major->is($major))->toBeTrue();
});

test('student record policy exposes only the student own record', function () {
    $student = academicUserWithRole('siswa');
    $otherStudent = academicUserWithRole('siswa');
    $major = Major::create(['code' => 'POL', 'name' => 'Policy Test', 'short_name' => 'POL', 'is_active' => true]);
    $classroom = Classroom::create([
        'code' => 'POL-1', 'name' => 'Policy Class', 'major_id' => $major->id,
        'grade_level' => 10, 'academic_year' => '2026/2027', 'capacity' => 36,
        'is_active' => true,
    ]);
    $ownRecord = StudentRecord::create([
        'user_id' => $student->id, 'classroom_id' => $classroom->id,
        'academic_year' => '2026/2027', 'student_id' => 'POL-001',
        'status' => 'active', 'enrollment_date' => '2026-07-01',
    ]);
    $otherRecord = StudentRecord::create([
        'user_id' => $otherStudent->id, 'classroom_id' => $classroom->id,
        'academic_year' => '2026/2027', 'student_id' => 'POL-002',
        'status' => 'active', 'enrollment_date' => '2026-07-01',
    ]);

    expect($student->can('view', $ownRecord))->toBeTrue()
        ->and($student->can('view', $otherRecord))->toBeFalse();
});
