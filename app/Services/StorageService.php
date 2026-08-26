<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Media;
use App\Models\StorageQuota;
use App\Models\StorageAuditLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class StorageService
{
    /** @var array<string, list<string>> */
    private const ALLOWED_MIME_TYPES = [
        'pdf' => ['application/pdf'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
    ];

    public function upload(User $user, UploadedFile $file, ?Folder $folder = null): Media
    {
        $this->validateFile($file);

        if ($folder && $folder->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'folder' => 'Folder tujuan bukan milik pengguna ini.',
            ]);
        }

        $size = (int) $file->getSize();

        return DB::transaction(function () use ($user, $file, $folder, $size): Media {
            $quota = StorageQuota::query()->lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['max_bytes' => StorageQuota::defaultMaxBytesFor($user), 'used_bytes' => 0],
            );

            if (! $quota->hasAvailableSpace($size)) {
                throw ValidationException::withMessages([
                    'file' => 'Ukuran file melebihi sisa kuota penyimpanan Anda.',
                ]);
            }

            $media = $user
                ->addMedia($file->getRealPath())
                ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('files', 'local');

            $media->forceFill(['folder_id' => $folder?->id])->save();
            $quota->updateUsage($size);
            StorageAuditLog::log('upload', $media, ['size' => $size]);

            return $media;
        });
    }

    public function delete(Media $media, ?User $actor = null): void
    {
        $actor ??= auth()->user();
        if ($actor) {
            Gate::forUser($actor)->authorize('delete', $media);
        }

        DB::transaction(function () use ($media): void {
            if ($media->model_type === User::class) {
                $quota = StorageQuota::query()->where('user_id', $media->model_id)->lockForUpdate()->first();
                $quota?->updateUsage(-((int) $media->size));
            }

            $media->delete();
            StorageAuditLog::log('delete', $media, ['size' => $media->size]);
        });
    }

    public function restore(Media $media, ?User $actor = null): void
    {
        $actor ??= auth()->user();
        if ($actor) {
            Gate::forUser($actor)->authorize('restore', $media);
        }

        DB::transaction(function () use ($media): void {
            if (! $media->trashed()) {
                return;
            }

            $quota = StorageQuota::query()->where('user_id', $media->model_id)->lockForUpdate()->first();

            if ($quota) {
                $quota->updateUsage((int) $media->size);
            }

            $media->restore();
            StorageAuditLog::log('restore', $media);
        });
    }

    public function permanentlyDelete(Media $media, ?User $actor = null): void
    {
        $actor ??= auth()->user();
        if ($actor) {
            Gate::forUser($actor)->authorize('forceDelete', $media);
        }

        $media->forceDelete();
    }

    private function validateFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        if (! array_key_exists($extension, self::ALLOWED_MIME_TYPES)
            || ! in_array($mimeType, self::ALLOWED_MIME_TYPES[$extension], true)) {
            throw ValidationException::withMessages([
                'file' => 'Jenis file tidak diizinkan. Gunakan PDF, DOCX, XLSX, PNG, JPG, atau ZIP.',
            ]);
        }
    }
}
