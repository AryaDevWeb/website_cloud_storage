<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->isGuru(); }

    public function view(User $user, Subject $subject): bool
    {
        return $user->isGuru() && $subject->teacherAssignments()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool { return false; }
    public function update(User $user, Subject $subject): bool { return false; }
    public function delete(User $user, Subject $subject): bool { return false; }
}
