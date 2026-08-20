<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileDownloadController extends Controller
{
    public function __invoke(Media $media): BinaryFileResponse
    {
        Gate::authorize('view', $media);

        abort_unless($media->disk === 'local' && is_file($media->getPath()), 404);

        return response()->download(
            $media->getPath(),
            $media->file_name,
            ['Content-Type' => $media->mime_type ?: 'application/octet-stream'],
        );
    }
}
