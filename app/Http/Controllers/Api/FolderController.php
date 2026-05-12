<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    /**
     * GET /api/v1/folders
     * Returns root-level folders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $folders = $request->user()->folders()
            ->whereNull('parent_id')
            ->withCount(['children', 'files'])
            ->get()
            ->map(fn ($f) => $this->mapFolder($f));

        return response()->json([
            'success' => true,
            'message' => 'Folders retrieved.',
            'data'    => $folders,
            'meta'    => ['total' => $folders->count()],
        ]);
    }

    /**
     * POST /api/v1/folders
     * Create a new folder.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
        ]);

        $user     = $request->user();
        $parentId = $this->resolveId($request->input('parent_id'));

        // Duplicate name check within same parent
        $exists = $user->folders()
            ->where('parent_id', $parentId)
            ->where('nama_folder', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A folder with this name already exists here.',
            ], 422);
        }

        $folder = Folder::create([
            'nama_folder' => $request->name,
            'user_id'     => $user->id,
            'parent_id'   => $parentId,
            'permission'  => 1,
            'path'        => '', // Path is no longer physically used
        ]);

        Wallet::firstOrCreate(['user_id' => $user->id], ['koin' => 0])->increment('koin', 10);

        return response()->json([
            'success' => true,
            'message' => 'Folder created.',
            'data'    => $this->mapFolder($folder->loadCount(['children', 'files'])),
        ], 201);
    }

    /**
     * GET /api/v1/folders/{id}
     * Returns folder metadata + its direct children (subfolders + files).
     */
    public function show(Request $request, $id): JsonResponse
    {
        $folder = $request->user()->folders()
            ->with(['children.files', 'files'])
            ->withCount(['children', 'files'])
            ->findOrFail($id);

        if ($folder->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Folder is in trash.',
            ], 404);
        }

        $subfolders = $folder->children->map(fn ($f) => $this->mapFolder($f->loadCount(['children', 'files'])));
        $files = $folder->files->map(fn ($f) => $this->mapFileBrief($f));

        return response()->json([
            'success' => true,
            'message' => 'Folder contents retrieved.',
            'data'    => [
                'folder'  => $this->mapFolder($folder),
                'folders' => $subfolders,
                'files'   => $files,
            ],
        ]);
    }

    /**
     * PATCH /api/v1/folders/{id}
     * Rename a folder (also updates physical disk path + all descendants).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $folder  = $request->user()->folders()->findOrFail($id);
        $folder->update(['nama_folder' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Folder renamed.',
            'data'    => $this->mapFolder($folder->fresh()->loadCount(['children', 'files'])),
        ]);
    }

    /**
     * DELETE /api/v1/folders/{id}
     * Soft-delete folder and all its contents recursively.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $folder = $request->user()->folders()->findOrFail($id);
        $this->trashFolderAndContents($folder);

        return response()->json([
            'success' => true,
            'message' => 'Folder moved to trash.',
        ]);
    }

    /**
     * POST /api/v1/folders/{id}/restore
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $folder = $request->user()->folders()->onlyTrashed()->findOrFail($id);
        $folder->restore();

        return response()->json([
            'success' => true,
            'message' => 'Folder restored.',
        ]);
    }

    /**
     * DELETE /api/v1/folders/{id}/permanent
     */
    public function forceDelete(Request $request, $id): JsonResponse
    {
        $user   = $request->user();
        $folder = $user->folders()->withTrashed()->findOrFail($id);
        $this->permanentDeleteFolder($folder, $user);

        return response()->json([
            'success' => true,
            'message' => 'Folder permanently deleted.',
        ]);
    }

    /**
     * GET /api/v1/folders/tree
     * Returns the full nested folder tree for the user.
     */
    public function tree(Request $request): JsonResponse
    {
        $folders = $request->user()->folders()
            ->whereNull('parent_id')
            ->with('children.children') // 3 levels deep
            ->get();

        $tree = $folders->map(fn ($f) => $this->buildTreeNode($f));

        return response()->json([
            'success' => true,
            'message' => 'Folder tree retrieved.',
            'data'    => $tree,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function mapFolder(Folder $f): array
    {
        return [
            'id'          => $f->id,
            'type'        => 'folder',
            'name'        => $f->nama_folder,
            'parent_id'   => $f->parent_id,
            'items_count' => ($f->children_count ?? 0) + ($f->files_count ?? 0),
            'permission'  => (int) $f->permission,
            'created_at'  => $f->created_at?->toIso8601String(),
            'updated_at'  => $f->updated_at?->toIso8601String(),
        ];
    }

    private function mapFileBrief($f): array
    {
        $ext = strtolower(pathinfo($f->file, PATHINFO_EXTENSION));
        return [
            'id'   => (string) $f->id,
            'type' => 'file',
            'name' => $f->nama_tampilan,
            'ext'  => $ext,
            'size' => (int) $f->ukuran,
        ];
    }

    private function buildTreeNode(Folder $f): array
    {
        return [
            'id'       => $f->id,
            'name'     => $f->nama_folder,
            'children' => $f->children->map(fn ($c) => $this->buildTreeNode($c))->toArray(),
        ];
    }

    private function resolveId(mixed $id): ?int
    {
        if (is_null($id) || $id === '' || $id === 'null') return null;
        return (int) $id;
    }

    private function trashFolderAndContents(Folder $folder): void
    {
        foreach ($folder->files as $file) {
            $file->delete();
        }
        foreach ($folder->children as $sub) {
            $this->trashFolderAndContents($sub);
        }
        $folder->delete();
    }

    private function permanentDeleteFolder(Folder $folder, $user): void
    {
        foreach ($folder->files()->withTrashed()->get() as $file) {
            if (Storage::disk('local')->exists($file->path)) Storage::disk('local')->delete($file->path);
            if ($file->preview_path && Storage::disk('local')->exists($file->preview_path)) Storage::disk('local')->delete($file->preview_path);
            if ($file->thumbnail_path && Storage::disk('public')->exists($file->thumbnail_path)) Storage::disk('public')->delete($file->thumbnail_path);

            $user->decrement('storage_used', $file->ukuran);
            $file->forceDelete();
        }
        foreach ($folder->children()->withTrashed()->get() as $sub) {
            $this->permanentDeleteFolder($sub, $user);
        }
        $folder->forceDelete();
    }


}
