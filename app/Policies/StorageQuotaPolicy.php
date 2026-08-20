<?php

namespace App\Policies;

use App\Models\StorageQuota;
use App\Models\User;

class StorageQuotaPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, StorageQuota $quota): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, StorageQuota $quota): bool { return false; }
    public function delete(User $user, StorageQuota $quota): bool { return false; }
}
