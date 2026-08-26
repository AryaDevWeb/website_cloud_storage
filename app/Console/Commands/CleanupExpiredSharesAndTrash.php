<?php

namespace App\Console\Commands;

use App\Models\FileShare;
use App\Models\Media;
use App\Services\StorageService;
use Illuminate\Console\Command;

class CleanupExpiredSharesAndTrash extends Command
{
    protected $signature = 'storage:cleanup-expired-shares-and-trash';

    protected $description = 'Remove expired file shares and permanently delete old recycled files.';

    public function handle(StorageService $storageService): int
    {
        $expiredShares = FileShare::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $deletedFiles = 0;
        Media::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->chunkById(100, function ($mediaItems) use ($storageService, &$deletedFiles): void {
                foreach ($mediaItems as $media) {
                    $storageService->permanentlyDelete($media);
                    $deletedFiles++;
                }
            });

        $this->info("Expired shares removed: {$expiredShares}; recycled files permanently deleted: {$deletedFiles}.");

        return self::SUCCESS;
    }
}
