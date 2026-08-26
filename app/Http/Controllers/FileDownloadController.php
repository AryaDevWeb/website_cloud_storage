<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\StorageAuditLog;
use App\Services\FileSharingService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileDownloadController extends Controller
{
    public function __invoke(Media $media): BinaryFileResponse
    {
        $user = request()->user();
        abort_unless(app(FileSharingService::class)->canDownload($user, $media), 403);

        abort_unless($media->disk === 'local' && is_file($media->getPath()), 404);

        StorageAuditLog::log('download', $media);

        $headers = ['Content-Type' => $media->mime_type ?: 'application/octet-stream'];

        if (request()->boolean('preview') && in_array($media->mime_type, [
            'application/pdf',
            'image/png',
            'image/jpeg',
        ], true)) {
            return response()->file($media->getPath(), $headers);
        }

        return response()->download($media->getPath(), $media->file_name, $headers);
    }
}
