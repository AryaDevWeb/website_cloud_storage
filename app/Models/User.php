<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nisn', // Student ID
        'nip',  // Teacher ID
        'phone',
        'address',
        'birth_date',
        'gender',
        'profile_photo',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function studentRecords()
    {
        return $this->hasMany(StudentRecord::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function homeroomClassrooms()
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    public function departmentHeads()
    {
        return $this->hasMany(DepartmentHead::class);
    }

    // Helper methods
    public function getCurrentClassroom()
    {
        return $this->studentRecords()
            ->where('academic_year', now()->year . '/' . (now()->year + 1))
            ->first()
            ?->classroom;
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher') || $this->hasRole('homeroom_teacher') || $this->hasRole('department_head');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}