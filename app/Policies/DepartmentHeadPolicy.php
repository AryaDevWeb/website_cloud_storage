<?php

namespace App\Policies;

use App\Models\DepartmentHead;
use App\Models\User;

class DepartmentHeadPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, DepartmentHead $departmentHead): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, DepartmentHead $departmentHead): bool { return false; }
    public function delete(User $user, DepartmentHead $departmentHead): bool { return false; }
}
