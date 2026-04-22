<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Gallery;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * GET /api/v1/files
     * Lists files with optional folder_id, search query, sort, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user     = $request->user();
        $folderId = $this->resolveId($request->query('folder_id'));
        $q        = $request->query('q');
        $sort     = $request->query('sort', 'name');
        $perPage  = (int) $request->query('per_page', 20);

        $query = $user->galleries()->whereNull('deleted_at');

        if ($q) {
            $query->where('nama_tampilan', 'LIKE', "%{$q}%");
        } else {
            $query->where('folder_id', $folderId);
        }

        $files = $query->orderBy(
            match($sort) {
                'date' => 'updated_at',
                'size' => 'ukuran',
                default => 'nama_tampilan',
            },
            $sort === 'name' ? 'asc' : 'desc'
        )->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Files retrieved.',
            'data'    => $files->map(fn ($f) => $this->mapFile($f)),
            'meta'    => [
                'current_page' => $files->currentPage(),
                'last_page'    => $files->lastPage(),
                'per_page'     => $files->perPage(),
                'total'        => $files->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/files
     * Upload a file. Expects multipart/form-data with field "file".
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file'      => 'required|file|mimes:jpg,jpeg,png,gif,svg,webp,pdf,mp4,webm,mov,avi,mp3,wav,ogg,flac,txt,md,json,doc,docx,xls,xlsx,ppt,pptx|max:102400', // 100 MB
            'folder_id' => 'nullable|integer',
        ]);

        $user     = $request->user();
        $file     = $request->file('file');
        $fileSize = $file->getSize();

        if (($user->storage_used + $fileSize) > $user->storage_quota) {
            return response()->json([
                'success' => false,
                'message' => 'Storage quota exceeded.',
            ], 422);
        }

        $folderId    = $this->resolveId($request->input('folder_id'));
        
        $mime_type = $file->getMimeType();
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        
        $storagePath = "users/{$user->id}/original";

        // Simpan dengan nama UUID ke local storage
        Storage::disk('local')->putFileAs($storagePath, $file, $safeName);
        
        // Build full path untuk database
        $fullPath = $storagePath . '/' . $safeName;

        $preview_type = $this->mapPreviewType($extension);
        
        // Determine initial conversion status
        $needsConversion = in_array($preview_type, ['image', 'video', 'office']);
        $conversion_status = $needsConversion ? 'pending' : 'done';

        $gallery = Gallery::create([
            'user_id'       => $user->id,
            'folder_id'     => $folderId,
            'file'          => $safeName,
            'nama_tampilan' => $displayName,
            'ukuran'        => $fileSize,
            'izin'          => 1,
            'path'          => $fullPath,
            'mime_type'     => $mime_type,
            'extension'     => $extension,
            'preview_type'  => $preview_type,
            'conversion_status' => $conversion_status,
            'riwayat'       => now(),
        ]);

        if ($needsConversion) {
            \App\Jobs\ProcessFilePreview::dispatch($gallery->id);
        }

        $user->increment('storage_used', $fileSize);
        Wallet::firstOrCreate(['user_id' => $user->id], ['koin' => 0])->increment('koin', 10);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data'    => $this->mapFile($gallery),
        ], 201);
    }

    /**
     * GET /api/v1/files/{id}
     * Returns metadata for a single file.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $file = $request->user()->galleries()->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'File retrieved.',
            'data'    => $this->mapFile($file),
        ]);
    }

    /**
     * PATCH /api/v1/files/{id}
     * Rename a file.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate(['name' => 'required|string']);
        $file = $request->user()->galleries()->findOrFail($id);
        $file->update(['nama_tampilan' => ltrim($request->name, '/')]);

        return response()->json([
            'success' => true,
            'message' => 'File renamed.',
            'data'    => $this->mapFile($file->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/files/{id}
     * Soft-delete (move to trash).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $file = $request->user()->galleries()->findOrFail($id);
        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File moved to trash.',
        ]);
    }

    /**
     * GET /api/v1/files/{id}/download
     * Stream the file for download.
     */
    public function download(Request $request, $id)
    {
        $file = $request->user()->galleries()->findOrFail($id);

        if (! Storage::disk('local')->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on storage.',
            ], 404);
        }

        return Storage::disk('local')->download($file->path, $file->nama_tampilan);
    }

    /**
     * POST /api/v1/files/{id}/star
     * Toggle the starred status.
     */
    public function star(Request $request, $id): JsonResponse
    {
        $file = $request->user()->galleries()->findOrFail($id);
        $file->update(['starred' => ! $file->starred]);

        return response()->json([
            'success' => true,
            'message' => $file->starred ? 'File starred.' : 'File unstarred.',
            'data'    => ['starred' => (bool) $file->starred],
        ]);
    }

    /**
     * POST /api/v1/files/{id}/share
     * Returns a sharable public URL for the file.
     */
    public function share(Request $request, $id): JsonResponse
    {
        $file = $request->user()->galleries()->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Share URL generated.',
            'data'    => ['url' => url('/open_file/' . $file->id)],
        ]);
    }

    /**
     * POST /api/v1/files/{id}/move
     * Move file to a different folder.
     */
    public function move(Request $request, $id): JsonResponse
    {
        $request->validate(['folder_id' => 'nullable|integer']);
        $user     = $request->user();
        $file     = $user->galleries()->findOrFail($id);
        $folderId = $this->resolveId($request->input('folder_id'));

        if ($folderId) {
            $user->folders()->findOrFail($folderId); // ownership check
        }

        $file->update(['folder_id' => $folderId]);

        return response()->json([
            'success' => true,
            'message' => 'File moved.',
            'data'    => $this->mapFile($file->fresh()),
        ]);
    }

    /**
     * POST /api/v1/files/{id}/restore
     * Restore a soft-deleted file from trash.
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $file = $request->user()->galleries()->onlyTrashed()->findOrFail($id);
        $file->restore();

        return response()->json([
            'success' => true,
            'message' => 'File restored.',
        ]);
    }

    /**
     * DELETE /api/v1/files/{id}/permanent
     * Permanently delete file and free quota.
     */
    public function forceDelete(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $file = $user->galleries()->withTrashed()->findOrFail($id);

        if (Storage::disk('local')->exists($file->path)) {
            Storage::disk('local')->delete($file->path);
        }

        // Delete preview and thumbnail
        if ($file->preview_path && Storage::disk('local')->exists($file->preview_path)) {
            Storage::disk('local')->delete($file->preview_path);
        }
        if ($file->thumbnail_path && Storage::disk('public')->exists($file->thumbnail_path)) {
            Storage::disk('public')->delete($file->thumbnail_path);
        }

        $user->decrement('storage_used', $file->ukuran);
        $file->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'File permanently deleted.',
        ]);
    }

    /**
     * PATCH /api/v1/files/{id}/permission
     * Change file visibility (0 = private, 1 = public).
     */
    public function permission(Request $request, $id): JsonResponse
    {
        $request->validate(['izin' => 'required|in:0,1']);
        $file = $request->user()->galleries()->findOrFail($id);
        $file->update(['izin' => $request->izin]);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated.',
            'data'    => ['izin' => (int) $file->izin],
        ]);
    }

    // ── Filtered views ────────────────────────────────────────────────

    public function recent(Request $request): JsonResponse
    {
        $files = $request->user()->galleries()
            ->latest()
            ->take(30)
            ->get()
            ->map(fn ($f) => $this->mapFile($f));

        return $this->paginatedResponse($files, 1, 1, 30);
    }

    public function starred(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $files   = $request->user()->galleries()
            ->where('starred', true)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Starred files retrieved.',
            'data'    => $files->map(fn ($f) => $this->mapFile($f)),
            'meta'    => [
                'current_page' => $files->currentPage(),
                'last_page'    => $files->lastPage(),
                'per_page'     => $files->perPage(),
                'total'        => $files->total(),
            ],
        ]);
    }

    public function shared(Request $request): JsonResponse
    {
        $files = $request->user()->galleries()
            ->where('izin', 1)
            ->latest()
            ->get()
            ->map(fn ($f) => $this->mapFile($f));

        return $this->paginatedResponse($files, 1, 1, $files->count());
    }

    public function trash(Request $request): JsonResponse
    {
        $files = $request->user()->galleries()
            ->onlyTrashed()
            ->latest('deleted_at')
            ->get()
            ->map(fn ($f) => $this->mapFile($f, trashed: true));

        return $this->paginatedResponse($files, 1, 1, $files->count());
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function mapFile(Gallery $f, bool $trashed = false): array
    {
        $ext = strtolower(pathinfo($f->file, PATHINFO_EXTENSION));

        return [
            'id'           => (string) $f->id,
            'type'         => 'file',
            'name'         => $f->nama_tampilan,
            'original_name' => $f->file,
            'ext'          => $ext,
            'mime_type'    => $f->mime_type ?? $this->mimeForExt($ext),
            'size'         => (int) $f->ukuran,
            'folder_id'    => $f->folder_id,
            'is_starred'   => (bool) $f->starred,
            'is_shared'    => $f->izin == 1,
            'conversion_status' => $f->conversion_status ?? 'done',
            'preview_type' => $f->preview_type ?: $this->mapPreviewType($ext),
            'preview_path' => $f->preview_path,
            'thumbnail_url' => $f->thumbnail_path ? Storage::disk('public')->url($f->thumbnail_path) : null,
            'created_at'   => $f->created_at?->toIso8601String(),
            'updated_at'   => $f->updated_at?->toIso8601String(),
            'deleted_at'   => $f->deleted_at?->toIso8601String(),
        ];
    }

    private function resolveId(mixed $id): ?int
    {
        if (is_null($id) || $id === '' || $id === 'null') return null;
        return (int) $id;
    }

    private function mapPreviewType(string $ext): string
    {
        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']) => 'image',
            in_array($ext, ['mp4', 'webm', 'mov', 'avi'])                => 'video',
            in_array($ext, ['mp3', 'wav', 'ogg', 'flac'])                => 'audio',
            $ext === 'pdf'                                                => 'pdf',
            in_array($ext, ['txt', 'md', 'json', 'js', 'php', 'py', 'css', 'html', 'sql']) => 'text',
            in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']) => 'office',
            default => 'unknown',
        };
    }

    private function mimeForExt(string $ext): string
    {
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'mp4'  => 'video/mp4',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt'  => 'text/plain',
            default => 'application/octet-stream',
        };
    }

    private function paginatedResponse($data, int $page, int $lastPage, int $total): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Retrieved successfully.',
            'data'    => $data,
            'meta'    => [
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $total,
                'total'        => $total,
            ],
        ]);
    }
}
