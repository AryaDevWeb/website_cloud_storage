<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\FileShare;
use App\Models\Media;
use App\Models\StorageAuditLog;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class FileSharingService
{
    public function share(
        User $sharer,
        Media $media,
        ?int $sharedToUserId = null,
        ?string $sharedToRole = null,
        string $permission = 'view',
        ?CarbonInterface $expiresAt = null,
    ): FileShare {
        Gate::forUser($sharer)->authorize('share', $media);

        if (($sharedToUserId === null) === ($sharedToRole === null)) {
            throw ValidationException::withMessages([
                'recipient' => 'Tentukan tepat satu penerima user atau role.',
            ]);
        }

        if (! in_array($permission, ['view', 'download'], true)) {
            throw ValidationException::withMessages([
                'permission' => 'Permission sharing tidak valid.',
            ]);
        }

        if ($expiresAt?->isPast()) {
            throw ValidationException::withMessages([
                'expires_at' => 'Waktu kedaluwarsa harus berada di masa depan.',
            ]);
        }

        if ($sharedToUserId !== null) {
            $recipient = User::query()->find($sharedToUserId);

            if (! $recipient || $recipient->status !== UserStatus::ACTIVE) {
                throw ValidationException::withMessages([
                    'shared_to_user_id' => 'Penerima tidak ditemukan atau tidak aktif.',
                ]);
            }
        }

        if ($sharedToRole !== null) {
            if (! in_array($sharedToRole, ['guru', 'siswa'], true)
                || ! User::role($sharedToRole)->where('status', UserStatus::ACTIVE->value)->exists()) {
                throw ValidationException::withMessages([
                    'shared_to_role' => 'Role penerima tidak valid atau belum memiliki pengguna aktif.',
                ]);
            }
        }

        $share = FileShare::create([
            'media_id' => $media->id,
            'shared_by_user_id' => $sharer->id,
            'shared_to_user_id' => $sharedToUserId,
            'shared_to_role' => $sharedToRole,
            'permission' => $permission,
            'expires_at' => $expiresAt,
        ]);

        StorageAuditLog::log('share', $media, [
            'share_id' => $share->id,
            'shared_to_user_id' => $sharedToUserId,
            'shared_to_role' => $sharedToRole,
            'permission' => $permission,
            'expires_at' => $expiresAt?->toIso8601String(),
        ]);

        return $share;
    }

    public function canDownload(User $user, Media $media): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if (($media->model_type === User::class && (int) $media->model_id === $user->id)
            || $user->isAdmin()) {
            return true;
        }

        return FileShare::query()
            ->active()
            ->where('media_id', $media->id)
            ->where('permission', 'download')
            ->where(function ($query) use ($user): void {
                $query->where('shared_to_user_id', $user->id)
                    ->orWhereIn('shared_to_role', $user->getRoleNames());
            })
            ->exists();
    }
}
