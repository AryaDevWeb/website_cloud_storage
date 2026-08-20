<?php

namespace App\Policies;

use App\Models\TeacherAssignment;
use App\Models\User;

class TeacherAssignmentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->isGuru(); }

    public function view(User $user, TeacherAssignment $assignment): bool
    {
        return $user->isGuru() && $assignment->user_id === $user->id;
    }

    public function create(User $user): bool { return false; }
    public function update(User $user, TeacherAssignment $assignment): bool { return false; }
    public function delete(User $user, TeacherAssignment $assignment): bool { return false; }
}
