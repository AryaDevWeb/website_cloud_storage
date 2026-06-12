<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MasterValidationSeeder::class,
            SharedDriveSeeder::class,
        ]);

        // Seed some demo users pre-registered for convenience:
        // 1. Siswa: Budi Santoso
        if (!\App\Models\User::where('username', 'budisantoso')->orWhere('email', 'budi.santoso@siswa.smk.sch.id')->exists()) {
            \App\Models\User::create([
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@siswa.smk.sch.id',
                'username' => 'budisantoso',
                'role' => 'siswa',
                'target_kelas' => 'XII RPL',
                'target_jurusan' => 'RPL',
                'storage_limit_bytes' => 2 * 1024 * 1024 * 1024,
                'storage_used_bytes' => 0,
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]);
        }

        // 2. Guru Wali Kelas: Heri Setiawan
        if (!\App\Models\User::where('username', 'herisetiawan')->orWhere('email', 'wali.rpl@smk.sch.id')->exists()) {
            \App\Models\User::create([
                'name' => 'Heri Setiawan',
                'email' => 'wali.rpl@smk.sch.id',
                'username' => 'herisetiawan',
                'role' => 'guru_wali',
                'target_kelas' => 'XII RPL',
                'target_jurusan' => 'RPL',
                'storage_limit_bytes' => 20 * 1024 * 1024 * 1024,
                'storage_used_bytes' => 0,
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]);
        }

        // 3. Admin: Pak Admin
        if (!\App\Models\User::where('username', 'pakadmin')->orWhere('email', 'admin@smk.sch.id')->exists()) {
            \App\Models\User::create([
                'name' => 'Pak Admin',
                'email' => 'admin@smk.sch.id',
                'username' => 'pakadmin',
                'role' => 'admin',
                'target_kelas' => null,
                'target_jurusan' => null,
                'storage_limit_bytes' => 100 * 1024 * 1024 * 1024,
                'storage_used_bytes' => 0,
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]);
        }
    }
}
