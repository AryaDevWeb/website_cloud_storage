<?php

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    /**
     * Register a new user with the appropriate role and profile.
     *
     * Runs inside a database transaction to guarantee atomicity:
     * if profile creation or role assignment fails, the user record
     * is rolled back along with it.
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'status'   => UserStatus::PENDING,
            ]);

            if ($data['role'] === 'siswa') {
                $user->studentProfile()->create([
                    'nisn' => $data['nisn'],
                ]);
            } else {
                $user->teacherProfile()->create([
                    'nip' => $data['nip'],
                ]);
            }

            // Role must never be admin via public registration.
            // Validated upstream in RegisterForm, but enforced here as well.
            $allowedPublicRoles = ['siswa', 'guru'];
            $role = in_array($data['role'], $allowedPublicRoles, true)
                ? $data['role']
                : 'siswa';

            $user->assignRole($role);

            return $user;
        });
    }
}
