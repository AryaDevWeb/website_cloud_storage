<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'folder_id',
        'file',
        'izin',
        'nama_tampilan',
        'ukuran',
        'path',
        'riwayat',
        'starred',
        'status',
        'preview_type',
        'preview_path',
    ];

    protected $casts = [
        'riwayat'  => 'datetime',
        'starred'  => 'boolean',
        'izin'     => 'integer',
    ];

    public function getUkuranFormatAttribute()
    {
        $byte = $this->ukuran;
        if ($byte <= 0) return '0 B';
        $unit = ['B','KB','MB','GB'];
        $i = floor(log($byte, 1024));
        return round($byte / pow(1024, $i), 2) . ' ' . $unit[$i];
    }
}
