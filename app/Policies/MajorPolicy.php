<?php

namespace App\Policies;

use App\Models\Major;
use App\Models\User;

class MajorPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Major $major): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Major $major): bool { return false; }
    public function delete(User $user, Major $major): bool { return false; }
}
