<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->isGuru(); }

    public function view(User $user, Classroom $classroom): bool
    {
        return $user->isGuru() && $classroom->teacherAssignments()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool { return false; }
    public function update(User $user, Classroom $classroom): bool { return false; }
    public function delete(User $user, Classroom $classroom): bool { return false; }
}
