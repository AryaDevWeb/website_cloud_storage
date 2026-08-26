<?php

namespace App\Policies;

use App\Models\FileShare;
use App\Models\User;

class FileSharePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, FileShare $share): bool { return false; }
    public function delete(User $user, FileShare $share): bool { return false; }
}
