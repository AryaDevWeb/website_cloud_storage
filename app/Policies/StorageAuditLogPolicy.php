<?php

namespace App\Policies;

use App\Models\StorageAuditLog;
use App\Models\User;

class StorageAuditLogPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, StorageAuditLog $log): bool { return false; }
}
