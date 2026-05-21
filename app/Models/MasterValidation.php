<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterValidation extends Model
{
    protected $table = 'master_validations';

    protected $fillable = [
        'nama_lengkap',
        'nisn',
        'nik',
        'nip',
        'nuptk',
        'email',
        'role',
        'kelas',
        'jurusan',
        'jenis_ptk',
        'tugas_tambahan',
    ];
}
