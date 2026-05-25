<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Folder;
use App\Models\User;

class SharedDriveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an admin user to own the shared drives
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Pak Admin',
                'email' => 'admin@smk.sch.id',
                'role' => 'admin',
                'password' => bcrypt('password'),
                'storage_limit_bytes' => 100 * 1024 * 1024 * 1024, // 100GB
                'storage_used_bytes' => 0,
            ]);
        }

        Folder::whereIn('nama_folder', [
            'Shared Drive XI TKJ',
            'Shared Drive Tendik',
        ])->update([
            'is_shared_drive' => false,
            'scope_kelas' => null,
            'scope_jurusan' => null,
            'scope_tendik' => null,
        ]);

        foreach (['X RPL', 'XI RPL', 'XII RPL'] as $className) {
            $classDrive = Folder::updateOrCreate(
                [
                    'nama_folder' => "Shared Drive {$className}",
                    'parent_id' => null,
                ],
                [
                    'user_id' => $admin->id,
                    'is_shared_drive' => true,
                    'scope_kelas' => $className,
                    'scope_jurusan' => null,
                    'scope_tendik' => null,
                    'permission' => 1,
                    'path' => '',
                ]
            );

            Folder::updateOrCreate(
                [
                    'nama_folder' => "Materi {$className}",
                    'parent_id' => $classDrive->id,
                ],
                [
                    'user_id' => $admin->id,
                    'is_assignment_folder' => false,
                    'permission' => 1,
                    'path' => '',
                ]
            );

            Folder::updateOrCreate(
                [
                    'nama_folder' => "Pengumpulan Tugas {$className}",
                    'parent_id' => $classDrive->id,
                ],
                [
                    'user_id' => $admin->id,
                    'is_assignment_folder' => true,
                    'permission' => 1,
                    'path' => '',
                ]
            );
        }

        Folder::updateOrCreate([
            'nama_folder' => 'Shared Drive Jurusan RPL',
            'parent_id' => null,
        ], [
            'user_id' => $admin->id,
            'is_shared_drive' => true,
            'scope_kelas' => null,
            'scope_jurusan' => 'RPL',
            'scope_tendik' => null,
            'permission' => 1,
            'path' => '',
        ]);
    }
}
