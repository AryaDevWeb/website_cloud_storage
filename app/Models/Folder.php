<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Folder extends Model
{
    protected $fillable = [
        'user_id',
        'parent_id',
        'nama_folder',
        'permission'
    ];

    public function user()
    {
        return $this->belongsTo(User::class); 
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(Gallery::class,'folder_id');
    }
    //
}
