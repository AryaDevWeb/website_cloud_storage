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
        'role',
        'target_kelas',
        'target_jurusan',
        'storage_limit_bytes',
        'storage_used_bytes',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'storage_limit_bytes' => 'integer',
        'storage_used_bytes'  => 'integer',
    ];

    /**
     * Legacy column mapping for storage_quota (storage_limit_bytes)
     */
    public function getStorageQuotaAttribute()
    {
        return $this->storage_limit_bytes;
    }

    public function setStorageQuotaAttribute($value)
    {
        $this->attributes['storage_limit_bytes'] = $value;
    }

    /**
     * Legacy column mapping for storage_used (storage_used_bytes)
     */
    public function getStorageUsedAttribute()
    {
        return $this->storage_used_bytes;
    }

    public function setStorageUsedAttribute($value)
    {
        $this->attributes['storage_used_bytes'] = $value;
    }

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
