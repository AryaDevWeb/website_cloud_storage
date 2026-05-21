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

        // 1. Shared Drive for XII RPL
        $rplDrive = Folder::create([
            'nama_folder' => 'Shared Drive XII RPL',
            'user_id' => $admin->id,
            'is_shared_drive' => true,
            'scope_kelas' => 'XII RPL',
            'permission' => 1,
            'path' => '',
        ]);

        // Subfolder assignment inside XII RPL
        Folder::create([
            'nama_folder' => 'Pengumpulan Tugas XII RPL',
            'user_id' => $admin->id,
            'parent_id' => $rplDrive->id,
            'is_assignment_folder' => true,
            'permission' => 1,
            'path' => '',
        ]);

        // 2. Shared Drive for XI TKJ
        $tkjDrive = Folder::create([
            'nama_folder' => 'Shared Drive XI TKJ',
            'user_id' => $admin->id,
            'is_shared_drive' => true,
            'scope_kelas' => 'XI TKJ',
            'permission' => 1,
            'path' => '',
        ]);

        // Subfolder assignment inside XI TKJ
        Folder::create([
            'nama_folder' => 'Pengumpulan Tugas XI TKJ',
            'user_id' => $admin->id,
            'parent_id' => $tkjDrive->id,
            'is_assignment_folder' => true,
            'permission' => 1,
            'path' => '',
        ]);

        // 3. Shared Drive for Jurusan RPL
        Folder::create([
            'nama_folder' => 'Shared Drive Jurusan RPL',
            'user_id' => $admin->id,
            'is_shared_drive' => true,
            'scope_jurusan' => 'RPL',
            'permission' => 1,
            'path' => '',
        ]);

        // 4. Shared Drive for Tendik
        Folder::create([
            'nama_folder' => 'Shared Drive Tendik',
            'user_id' => $admin->id,
            'is_shared_drive' => true,
            'scope_tendik' => 'tendik',
            'permission' => 1,
            'path' => '',
        ]);
    }
}
