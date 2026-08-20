<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'major_id',
        'hours_per_week',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hours_per_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }
}
