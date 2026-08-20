<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'major_id',
        'grade_level',
        'academic_year',
        'capacity',
        'room',
        'homeroom_teacher_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function studentRecords(): HasMany
    {
        return $this->hasMany(StudentRecord::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }
}
