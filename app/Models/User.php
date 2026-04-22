<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'storage_used',
        'storage_quota',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'storage_used'    => 'integer',
        'storage_quota'   => 'integer',
    ];

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    public function wallets()
    {
        return $this->hasOne(Wallet::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
}
