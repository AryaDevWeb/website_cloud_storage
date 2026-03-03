<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'user_id',
        'folder_id',
        'file',
        'izin',
        'nama_tampilan',
        'ukuran'
    ];


    public function getUkuranFormatAttribute()
    {
        $byte = $this->ukuran;

        if ($byte <= 0) {
            return '0 B';
        }

        $unit = ['B','KB','MB','GB'];

        $i = floor(log($byte,1024));

        $formatUkuran = round($byte/ pow(1024,$i),2);

        return $formatUkuran . ' ' . $unit[$i];
      

    }
    //
}
