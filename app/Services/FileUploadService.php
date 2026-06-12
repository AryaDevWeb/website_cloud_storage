<?php

namespace App\Services;

use App\Jobs\ProcessFilePreview;
use App\Models\Folder;
use App\Models\Gallery;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileUploadService
{
    public function store(User $user, UploadedFile $file, ?int $folderId = null, ?string $displayName = null): Gallery
    {
        $fileSize = (int) $file->getSize();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        if (in_array($extension, ['exe', 'iso'], true)) {
            throw ValidationException::withMessages([
                'file' => 'Ekstensi file .exe dan .iso diblokir untuk alasan keamanan.',
            ]);
        }

        if ($extension === 'mp4' && $fileSize > (50 * 1024 * 1024)) {
            throw ValidationException::withMessages([
                'file' => 'File MP4 tidak boleh lebih dari 50 MB.',
            ]);
        }

        if (!$user->hasAvailableStorage($fileSize)) {
            throw ValidationException::withMessages([
                'file' => 'Storage quota exceeded.',
            ]);
        }

        if ($folderId) {
            $targetFolder = Folder::findOrFail($folderId);
            if (!RbacScopeService::canWriteFolder($user, $targetFolder)) {
                throw new AuthorizationException('Anda tidak memiliki akses menulis di folder ini.');
            }
        }

        $safeName = Str::uuid()->toString() . '.zip';
        $displayName = mb_substr(basename($displayName ?: $file->getClientOriginalName()), 0, 255);
        $storagePath = "users/{$user->id}/original";

        Storage::disk('local')->makeDirectory($storagePath);

        $tempZipPath = FileArchiveService::createZipFromUpload($file, $displayName);
        $compressedSize = filesize($tempZipPath);

        Storage::disk('local')->putFileAs($storagePath, new \Illuminate\Http\File($tempZipPath), $safeName);
        @unlink($tempZipPath);

        $previewType = $this->mapPreviewType($extension);
        $needsConversion = in_array($previewType, ['image', 'video', 'office', 'pdf'], true);

        $gallery = Gallery::create([
            'user_id' => $user->id,
            'folder_id' => $folderId,
            'file' => $safeName,
            'nama_tampilan' => $displayName,
            'ukuran' => $fileSize,
            'compressed_size' => $compressedSize,
            'izin' => 0,
            'path' => $storagePath . '/' . $safeName,
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'preview_type' => $previewType,
            'conversion_status' => $needsConversion ? 'pending' : 'done',
            'riwayat' => now(),
        ]);

        if ($needsConversion) {
            ProcessFilePreview::dispatch($gallery->id);
        }

        $user->increment('storage_used_bytes', $fileSize);
        Wallet::firstOrCreate(['user_id' => $user->id], ['koin' => 0])->increment('koin', 10);

        return $gallery;
    }

    private function mapPreviewType(string $ext): string
    {
        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico', 'tiff'], true) => 'image',
            in_array($ext, ['mp4', 'webm', 'mov', 'avi'], true) => 'video',
            in_array($ext, ['mp3', 'wav', 'ogg', 'flac'], true) => 'audio',
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['txt', 'md', 'json', 'js', 'php', 'py', 'css', 'html', 'sh', 'sql'], true) => 'text',
            in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'], true) => 'office',
            default => 'unknown',
        };
    }
}
