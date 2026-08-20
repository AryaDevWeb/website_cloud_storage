<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'classroom_id',
        'academic_year',
        'role',
        'is_homeroom_teacher',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_homeroom_teacher' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
