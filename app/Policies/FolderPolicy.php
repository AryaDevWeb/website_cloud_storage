<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->isActive(); }

    public function view(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id;
    }

    public function create(User $user): bool { return $user->isActive(); }

    public function update(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id;
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $folder->user_id === $user->id;
    }
}
