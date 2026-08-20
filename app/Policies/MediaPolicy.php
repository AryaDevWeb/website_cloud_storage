<?php

namespace App\Policies;

use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->isActive() ? true : null;
    }

    public function view(User $user, Media $media): bool
    {
        return $user->isActive()
            && $media->model_type === User::class
            && (int) $media->model_id === $user->id;
    }

    public function delete(User $user, Media $media): bool
    {
        return $this->view($user, $media);
    }
}
