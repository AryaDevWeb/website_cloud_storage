<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterValidation;

class MasterValidationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterValidation::truncate();

        $data = [
            // --- Siswa ---
            [
                'nama_lengkap' => 'Budi Santoso',
                'nisn' => '1234567890',
                'nik' => '3201010101010001',
                'nip' => null,
                'nuptk' => null,
                'email' => 'budi.santoso@siswa.smk.sch.id',
                'role' => 'siswa',
                'kelas' => 'XII RPL',
                'jurusan' => 'RPL',
                'jenis_ptk' => null,
                'tugas_tambahan' => null,
            ],
            [
                'nama_lengkap' => 'Siti Aminah',
                'nisn' => '0987654321',
                'nik' => '3201010101010002',
                'nip' => null,
                'nuptk' => null,
                'email' => 'siti.aminah@siswa.smk.sch.id',
                'role' => 'siswa',
                'kelas' => 'XII RPL',
                'jurusan' => 'RPL',
                'jenis_ptk' => null,
                'tugas_tambahan' => null,
            ],
            [
                'nama_lengkap' => 'Andi Wijaya',
                'nisn' => '1122334455',
                'nik' => '3201010101010003',
                'nip' => null,
                'nuptk' => null,
                'email' => 'andi.wijaya@siswa.smk.sch.id',
                'role' => 'siswa',
                'kelas' => 'XI TKJ',
                'jurusan' => 'TKJ',
                'jenis_ptk' => null,
                'tugas_tambahan' => null,
            ],

            // --- Guru Wali Kelas ---
            [
                'nama_lengkap' => 'Heri Setiawan',
                'nisn' => null,
                'nik' => null,
                'nip' => '198001012005011001',
                'nuptk' => '8899889988998899',
                'email' => 'wali.rpl@smk.sch.id',
                'role' => 'guru',
                'kelas' => 'XII RPL',
                'jurusan' => 'RPL',
                'jenis_ptk' => 'Guru Umum',
                'tugas_tambahan' => 'Wali Kelas XII RPL',
            ],

            // --- Guru Jurusan ---
            [
                'nama_lengkap' => 'Dewi Lestari',
                'nisn' => null,
                'nik' => null,
                'nip' => '198505122010022002',
                'nuptk' => '7766776677667766',
                'email' => 'guru.rpl@smk.sch.id',
                'role' => 'guru',
                'kelas' => null,
                'jurusan' => 'RPL',
                'jenis_ptk' => 'Guru Produktif RPL',
                'tugas_tambahan' => 'Kepala Bengkel RPL',
            ],

            // --- Tendik (Laboran / Staf) ---
            [
                'nama_lengkap' => 'Agus Riyadi',
                'nisn' => null,
                'nik' => null,
                'nip' => '199003152015031003',
                'nuptk' => '5544554455445544',
                'email' => 'laboran.komputer@smk.sch.id',
                'role' => 'tendik',
                'kelas' => null,
                'jurusan' => 'RPL',
                'jenis_ptk' => 'Laboran',
                'tugas_tambahan' => 'Pengelola Laboratorium Komputer',
            ],

            // --- Admin ---
            [
                'nama_lengkap' => 'Pak Admin',
                'nisn' => null,
                'nik' => null,
                'nip' => '197508202000031001',
                'nuptk' => '1122112211221122',
                'email' => 'admin@smk.sch.id',
                'role' => 'admin',
                'kelas' => null,
                'jurusan' => null,
                'jenis_ptk' => 'Kepala Tata Usaha',
                'tugas_tambahan' => 'Admin Utama',
            ],
        ];

        foreach ($data as $item) {
            MasterValidation::create($item);
        }
    }
}
