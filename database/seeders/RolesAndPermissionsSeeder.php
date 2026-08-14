<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Permissions ──────────────────────────────────────────────────────
        // Created idempotently with firstOrCreate so re-running the seeder
        // does not produce duplicate entries.

        $permissions = [
            // File management
            'upload_files',
            'download_files',
            'delete_own_files',
            'manage_all_files',
            'share_files',
            'view_shared_files',

            // Administration
            'manage_users',
            'manage_settings',
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ─── Roles ────────────────────────────────────────────────────────────
        /** @var Role $admin */
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        /** @var Role $guru */
        $guru = Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);

        /** @var Role $siswa */
        $siswa = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        // ─── Role → Permission assignments ────────────────────────────────────
        $admin->syncPermissions(Permission::all());

        $guru->syncPermissions([
            'upload_files',
            'download_files',
            'delete_own_files',
            'share_files',
            'view_shared_files',
        ]);

        $siswa->syncPermissions([
            'download_files',
            'view_shared_files',
        ]);

        // ─── Demo / Seed Users ────────────────────────────────────────────────
        // Admin is provisioned here, NOT via public registration.
        /** @var User $adminUser */
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@smkn1lumajang.sch.id'],
            [
                'name'     => 'Administrator',
                'password' => bcrypt('password'),
                'status'   => UserStatus::ACTIVE,
            ]
        );
        $adminUser->syncRoles('admin');

        // Demo Guru
        /** @var User $guruUser */
        $guruUser = User::firstOrCreate(
            ['email' => 'guru@smkn1lumajang.sch.id'],
            [
                'name'     => 'Guru Demo',
                'password' => bcrypt('password'),
                'status'   => UserStatus::ACTIVE,
            ]
        );
        $guruUser->syncRoles('guru');
        $guruUser->teacherProfile()->firstOrCreate([
            'nip' => '198001012010011001',
        ]);

        // Demo Siswa
        /** @var User $siswaUser */
        $siswaUser = User::firstOrCreate(
            ['email' => 'siswa@smkn1lumajang.sch.id'],
            [
                'name'     => 'Siswa Demo',
                'password' => bcrypt('password'),
                'status'   => UserStatus::ACTIVE,
            ]
        );
        $siswaUser->syncRoles('siswa');
        $siswaUser->studentProfile()->firstOrCreate([
            'nisn' => '0012345678',
        ]);
    }
}