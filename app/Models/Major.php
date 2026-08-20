<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function departmentHeads(): HasMany
    {
        return $this->hasMany(DepartmentHead::class);
    }
}
