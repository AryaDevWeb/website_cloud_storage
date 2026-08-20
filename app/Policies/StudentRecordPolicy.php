<?php

namespace App\Policies;

use App\Models\StudentRecord;
use App\Models\User;

class StudentRecordPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->isSiswa(); }

    public function view(User $user, StudentRecord $record): bool
    {
        return $user->isSiswa() && $record->user_id === $user->id;
    }

    public function create(User $user): bool { return false; }
    public function update(User $user, StudentRecord $record): bool { return false; }
    public function delete(User $user, StudentRecord $record): bool { return false; }
}
