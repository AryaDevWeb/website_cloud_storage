<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller;
use App\Models\Gallery;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use App\Services\FileArchiveService;
use App\Services\FileUploadService;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Filesystem\FilesystemAdapter;


class Beranda extends Controller
{
    /**
     * Generate safe filename menggunakan UUID + ekstensi asli
     * Mencegah path traversal dan filename prediction
     */
    private function generateSafeFilename($file): string
    {
        $extension = $file->extension() ?: 'bin';
        return Str::uuid()->toString() . '.' . $extension;
    }

    /**
     * Sanitasi nama file asli untuk display (tidak untuk storage)
     */
    private function sanitizeDisplayName($file): string
    {
        $name = basename($file->getClientOriginalName());
        return mb_substr($name, 0, 255);
    }

    public function dashboard($id)
    {
        abort_unless((int) $id === (int) auth()->id(), 403);
        $user = User::findOrFail($id);
        
        $quota = (int) $user->storage_quota;
        $used = (int) $user->storage_used;
        $totalFiles = $user->galleries()->count();
        $totalFolders = $user->folders()->count();
        $usedMB = number_format($user->storage_used / 1024 / 1024, 1);
        $remainingMB = number_format(max(0, $user->storage_quota - $user->storage_used) / 1024 / 1024, 1);
        $totalMB = number_format(max(0, $user->storage_quota) / 1024 / 1024, 0);
        $percentage = $quota > 0
            ? min(100, ($used / $quota) * 100)
            : 0.0;
        $recentFiles = $user->galleries()->latest()->take(5)->get();

        // ── Real storage breakdown ───────────────────────────
        $statsRaw = $user->galleries()->selectRaw('file, sum(ukuran) as total')
            ->groupBy('file') // This is a bit rough, but better aggregate by extension
            ->get();
        
        $breakdown = ['Images'=>0, 'Videos'=>0, 'PDFs'=>0, 'Docs'=>0, 'Others'=>0];
        foreach ($user->galleries as $f) {
            $ext = strtolower($f->extension ?: pathinfo($f->nama_tampilan ?: $f->file, PATHINFO_EXTENSION));
            $cat = match(true) {
                in_array($ext, ['jpg','jpeg','png','gif','svg','webp']) => 'Images',
                in_array($ext, ['mp4','webm','mov','avi'])             => 'Videos',
                $ext === 'pdf'                                          => 'PDFs',
                in_array($ext, ['doc','docx','xls','xlsx','ppt','pptx','txt']) => 'Docs',
                default                                                 => 'Others',
            };
            $breakdown[$cat] += $f->ukuran;
        }

        return view('dashboard', compact(
            'user', 'totalFiles', 'totalFolders', 'usedMB', 'remainingMB', 'totalMB', 'percentage', 'recentFiles', 'breakdown'
        ));
    }

    public function akun($id)
    {
        $user = User::findOrFail($id);
        $folders = \App\Services\RbacScopeService::getRootFolders($user)->get();
        $file = Gallery::whereNull('folder_id')->whereNull('deleted_at')->where('user_id', $user->id)->get();

        return view('beranda', compact('user', 'folders', 'file'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'upload' => 'required|file|mimes:jpg,jpeg,png,gif,svg,webp,pdf,mp4,webm,mov,avi,mp3,wav,ogg,flac,txt,md,json,doc,docx,xls,xlsx,ppt,pptx|max:102400',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $user = auth()->user();
            $folder_id = $request->input('folder_id');
            try {
                $gallery = app(FileUploadService::class)->store(
                    $user,
                    $file,
                    $folder_id ? (int) $folder_id : null,
                    $this->sanitizeDisplayName($file)
                );
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return back()->with('error', $e->getMessage());
            }

            return back()->with('nama_tampil', $gallery->nama_tampilan);
        }
        return back()->with('error', 'Gagal upload file');
    }

    public function hapus_file($id)
    {
        $user = auth()->user();
        $file = Gallery::where('user_id',$id);
        
        // Quota is NOT recovered on soft delete anymore (Standard Policy)
        $file->delete();

        return back()->with('status_file', 'File berhasil dipindahkan ke tempat sampah');
    }

    public function folder(Request $request)
    {
        $request->validate([
            'nama' => [
                'required',
                'min:3',
                Rule::unique('folders', 'nama_folder')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                })
            ],
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        $user = auth()->user();
        $user_id = $user->id;
        $nama_folder = $request->nama; // Allow spaces
        $parent_id = $request->parent_id;

        if ($parent_id) {
            $parentFolder = Folder::findOrFail($parent_id);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $parentFolder)) {
                return back()->with('error', 'Anda tidak memiliki akses menulis di folder ini.');
            }
        }

        Folder::create([
            'nama_folder' => $nama_folder,
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'permission' => 1,
            'path' => '' // Path is no longer physically used
        ]);

        Wallet::firstOrCreate(['user_id' => $user_id], ['koin' => 0])->increment('koin', 10);

        return back()->with('notif', 'Folder berhasil ditambahkan!');
    }

    public function new_folder($id)
    {
        $isi_folder = Folder::with(['children' => function($q) {
            $q->whereNull('deleted_at');
        }, 'user', 'files' => function($q) {
            $q->whereNull('deleted_at');
        }])->findOrFail($id);
        
        if ($isi_folder->trashed()) {
            abort(404, 'Folder is in Trash');
        }

        if (!\App\Services\RbacScopeService::canAccessFolder(auth()->user(), $isi_folder)) {
            abort(403, 'Maaf Anda tidak memiliki izin');
        }

        return view('isi', compact('isi_folder'));
    }

    public function pencarian(Request $request)
    {
        $kunci = $request->cari;
        $user = auth()->user();

        if ($kunci) {
            $accessibleFolderIds = \App\Services\RbacScopeService::getAccessibleFolderIds($user);
            $folders = Folder::whereIn('id', $accessibleFolderIds)
                ->where('nama_folder', 'LIKE', '%' . $kunci . '%')
                ->whereNull('deleted_at')
                ->get();
            $files = Gallery::where(function($q) use ($user, $accessibleFolderIds) {
                    $q->where('user_id', $user->id)
                      ->orWhereIn('folder_id', $accessibleFolderIds);
                })
                ->where('nama_tampilan', 'LIKE', '%' . $kunci . '%')
                ->whereNull('deleted_at')
                ->get();
        } else {
            $folders = \App\Services\RbacScopeService::getRootFolders($user)->get();
            $files = Gallery::whereNull('folder_id')
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->get();
        }

        return view('beranda', compact('user', 'folders', 'files'));
    }

    public function hapus_folder($id)
    {
        $user = auth()->user();
        $folder = Folder::findOrFail($id);

        if (!\App\Services\RbacScopeService::canWriteFolder($user, $folder)) {
            abort(403, 'Unauthorized.');
        }

        // Soft delete recursively
        $this->trashFolderAndContents($folder);

        return redirect()->route('beranda', $user->id)->with('folder_status', "Folder " . $folder->nama_folder . " berhasil dipindahkan ke tempat sampah");
    }

    private function trashFolderAndContents($folder)
    {
        // Quota is NOT recovered on soft delete
        foreach ($folder->files as $file) {
            $file->delete();
        }

        foreach ($folder->children as $subfolder) {
            $this->trashFolderAndContents($subfolder);
        }

        $folder->delete();
    }

    public function izin_file($id)
    {
        $isi_file = Gallery::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFile(auth()->user(), $isi_file)) {
            abort(403, 'Unauthorized.');
        }
        return view('permission', compact('isi_file'));
    }

    public function ubah_izin(Request $request, $id)
    {
        $file = Gallery::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFile(auth()->user(), $file)) {
            abort(403, 'Unauthorized.');
        }
        $request->validate(['izin' => 'required|in:0,1']);

        // Simplified: Permission is now handled by application-level scoping, not on-disk encryption.
        $file->update(['izin' => $request->izin]);

        return back()->with('status', 'Izin file menjadi ' . ($request->izin == 1 ? 'Public' : 'Private'));
    }

    public function masuk_izin($id)
    {
        $izin_folder = Folder::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFolder(auth()->user(), $izin_folder)) {
            abort(403, 'Unauthorized.');
        }
        return view('izin_folder', compact('izin_folder'));
    }

    public function folder_permission(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFolder(auth()->user(), $folder)) {
            abort(403, 'Unauthorized.');
        }
        $request->validate(['izin' => 'required|in:0,1']);
        $folder->update(['permission' => $request->izin]);

        return back()->with('status', 'Izin folder menjadi ' . ($request->izin == 1 ? 'Public' : 'Private'));
    }

    public function lihat_akun($id)
    {
        $lihat_akun = User::findOrFail($id);
        if ($lihat_akun->id != auth()->id()) abort(403);
        return view('akun', compact('lihat_akun'));
    }

    public function hapus_akun($id)
    {
        $user = User::findOrFail($id);
        if ($user->id != auth()->id()) abort(403);

        $path = 'users/' . $user->id;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->deleteDirectory($path);
        }
        
        // Also clean thumbnails
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->deleteDirectory($path);
        }

        Auth::logout();
        $user->delete();

        return view('register', ['pesan' => 'Akun berhasil dihapus']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function download_file($id)
    {
        $user = auth()->user();
        
        // SECURITY: Hardened access layer - filter by user_id + izin + exclude trashed
        $file = Gallery::whereNull('deleted_at')->findOrFail($id);
        if (!\App\Services\RbacScopeService::canAccessFile($user, $file)) {
            abort(403, 'Unauthorized.');
        }
        
        $zipAbsolutePath = Storage::disk('local')->path($file->path);
        if (!file_exists($zipAbsolutePath)) {
            abort(404, 'File tidak ditemukan di penyimpanan.');
        }

        try {
            $extracted = FileArchiveService::extractFirstFileToTemp($zipAbsolutePath);
            return response()->streamDownload(function () use ($extracted) {
                $stream = fopen($extracted['path'], 'rb');
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
                @unlink($extracted['path']);
            }, $file->nama_tampilan);
        } catch (\RuntimeException $e) {
            abort(500, 'Gagal membuka file arsip.');
        }
    }

    public function pindah($id)
    {
        $ubah_nama = Gallery::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFile(auth()->user(), $ubah_nama)) {
            abort(403, 'Unauthorized.');
        }
        return view('rename', compact('ubah_nama'));
    }

    public function rename(Request $request, $id)
    {
        $file = Gallery::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFile(auth()->user(), $file)) {
            abort(403, 'Unauthorized.');
        }
        $request->validate(['ubah_nama' => 'required']);

        $nama_baru = $request->ubah_nama;
        $file->update(['nama_tampilan' => $nama_baru]);

        return redirect()->route('beranda', auth()->id())->with('status', 'File berhasil di-rename!');
    }

    public function pindah_rename($id)
    {
        $cari_folder = Folder::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFolder(auth()->user(), $cari_folder)) {
            abort(403, 'Unauthorized.');
        }
        return view('rename_folder', compact('cari_folder'));
    }

    public function rename_f(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFolder(auth()->user(), $folder)) {
            abort(403, 'Unauthorized.');
        }
        $request->validate(['rename' => 'required']);

        $nama_baru = $request->rename; // Allow spaces
        $folder->update(['nama_folder' => $nama_baru]);

        return back()->with('status', 'Folder berhasil di-rename');
    }
    

    public function open_file($id)
    {
        $user = auth()->user();
        $file = Gallery::whereNull('deleted_at')->findOrFail($id);
        if (!\App\Services\RbacScopeService::canAccessFile($user, $file)) {
            abort(403, 'Unauthorized.');
        }

        // Gunakan path dari database, jangan reconstruct
        $path = Storage::disk('local')->path($file->path);
        $waktu = is_null($file->riwayat) ? 'belum pernah dilihat' : $file->riwayat->diffForHumans();

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        $extension = strtolower($file->extension ?: pathinfo($file->nama_tampilan ?: $file->file, PATHINFO_EXTENSION));
        
        // Auto-assign preview_type if not set
        if (!$file->preview_type) {
            $file->preview_type = $this->mapPreviewType($extension);
        }

        return view('lihat', [
            'file' => $file,
            'extension' => $extension,
            'waktu' => $waktu,
            'preview_type' => $file->preview_type,
            'conversion_status' => $file->conversion_status ?? 'done',
        ]);
    }

    private function mapPreviewType($ext): string
    {
        return match(true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico', 'tiff']) => 'image',
            in_array($ext, ['mp4', 'webm', 'mov', 'avi']) => 'video',
            in_array($ext, ['mp3', 'wav', 'ogg', 'flac']) => 'audio',
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['txt', 'md', 'json', 'js', 'php', 'py', 'css', 'html', 'sh', 'sql']) => 'text/code',
            in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']) => 'office',
            default => 'unknown',
        };
    }

    // ==========================================
    // JSON API METHODS (for Javascript SPA)
    // ==========================================

    public function getFilesJson(Request $request)
    {
        $q = $request->query('q');
        $user = auth()->user();
        
        $folderIdInput = $request->query('folder_id', '');
        $folderId = ltrim($folderIdInput, 'f');
        if (empty($folderId) || $folderId === 'null' || $folderId === 'undefined') {
            $folderId = null;
        }

        $accessibleFolderIds = \App\Services\RbacScopeService::getAccessibleFolderIds($user);

        // SCOPE ALL QUERIES BY ACCESSIBLE FOLDERS OR OWNERSHIP
        $foldersQuery = Folder::whereIn('id', $accessibleFolderIds)
            ->with(['user'])
            ->withCount(['children', 'files']);

        $filesQuery = Gallery::where(function($sq) use ($user, $accessibleFolderIds) {
            $sq->where('user_id', $user->id)
              ->orWhereIn('folder_id', $accessibleFolderIds);
        });

        // ── VERIFY ACCESS TO THE TARGET FOLDER ──────────────────────────
        if ($folderId) {
            $targetFolder = Folder::findOrFail($folderId);
            if (!\App\Services\RbacScopeService::canAccessFolder($user, $targetFolder)) {
                abort(403, 'Unauthorized.');
            }
        }

        if ($q) {
            $foldersQuery->where('nama_folder', 'LIKE', '%' . $q . '%');
            $filesQuery->where('nama_tampilan', 'LIKE', '%' . $q . '%');
        } else {
            if ($folderId) {
                $foldersQuery->where('parent_id', $folderId);
                $filesQuery->where('folder_id', $folderId);
            } else {
                // For root level, show root folders that are accessible and root files owned by user
                $rootFolderIds = \App\Services\RbacScopeService::getRootFolders($user)->pluck('id')->toArray();
                $foldersQuery->whereIn('id', $rootFolderIds);
                $filesQuery->whereNull('folder_id')->where('user_id', $user->id);
            }
        }

        $folders = $foldersQuery->get()->map(function($f) use ($user) {
            return [
                'id' => 'f' . $f->id,
                'type' => 'folder',
                'name' => $f->nama_folder,
                'items' => $f->children_count + $f->files_count,
                'modified' => $f->updated_at ? $f->updated_at->toIso8601String() : now()->toIso8601String(),
                'owner' => $f->user_id === $user->id ? 'You' : ($f->user->name ?? 'Shared')
            ];
        });

        $files = $filesQuery->get()->map(fn ($f) => $this->mapFile($f));

        $items = collect()->merge($folders)->merge($files);

        // Sorting (In-memory for heterogeneous types, usually acceptable for per-folder views)
        $sort = $request->query('sort', 'name');
        if ($sort === 'name') {
            $items = $items->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        } elseif ($sort === 'date') {
            $items = $items->sortByDesc('modified')->values();
        } elseif ($sort === 'size') {
            $items = $items->sortByDesc('size')->values();
        }

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 100);

        $total = $items->count();
        $paged = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paged,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => ceil($total / max(1, $perPage))
        ]);
    }

    public function getFileJson($id)
    {
        $file = Gallery::withTrashed()->findOrFail($id);
        if (!\App\Services\RbacScopeService::canAccessFile(auth()->user(), $file)) {
            abort(403, 'Unauthorized.');
        }
        return response()->json($this->mapFile($file));
    }

    public function folderAjax(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable'
        ]);

        $user = auth()->user();
        $user_id = $user->id;
        $nama_folder = $request->name; // Allow spaces
        $parent_id = ltrim($request->parent_id, 'f');
        if (empty($parent_id) || $parent_id === 'null' || $parent_id === 'undefined') {
            $parent_id = null;
        }

        if ($parent_id) {
            $parentFolder = Folder::findOrFail($parent_id);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $parentFolder)) {
                return response()->json(['message' => 'Anda tidak memiliki akses menulis di folder ini.'], 403);
            }
        }

        // Master check for folder uniqueness in the same parent folder
        $exists = Folder::where('parent_id', $parent_id)
            ->where('nama_folder', $nama_folder)
            ->whereNull('deleted_at')
            ->exists();
            
        if ($exists) {
            return response()->json(['message' => 'Folder already exists'], 422);
        }

        $folder = Folder::create([
            'nama_folder' => $nama_folder,
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'permission' => 1,
            'path' => '' // Path is no longer physically used
        ]);

        Wallet::firstOrCreate(['user_id' => $user_id], ['koin' => 0])->increment('koin', 10);

        return response()->json([
            'id' => 'f' . $folder->id,
            'type' => 'folder',
            'name' => $folder->nama_folder,
            'items' => 0,
            'modified' => $folder->updated_at->toIso8601String(),
            'owner' => 'You'
        ]);
    }

    public function uploadAjax(Request $request)
    {
        try {
            // SECURITY: Validasi yang lebih ketat dengan allowlist
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,gif,svg,webp,pdf,mp4,webm,mov,avi,mp3,wav,ogg,flac,txt,md,json,doc,docx,xls,xlsx,ppt,pptx|max:102400',
                'folder_id' => 'nullable'
            ]);

            $file = $request->file('file');
            $user = auth()->user();
            $folder_id = ltrim($request->input('folder_id') ?? '', 'f');
            if (empty($folder_id) || $folder_id === 'null' || $folder_id === 'undefined') {
                $folder_id = null;
            }

            $gallery = app(FileUploadService::class)->store(
                $user,
                $file,
                $folder_id ? (int) $folder_id : null,
                $this->sanitizeDisplayName($file)
            );

            return response()->json([
                'id' => (string)$gallery->id,
                'type' => 'file',
                'name' => $gallery->nama_tampilan,
                'ext' => $gallery->extension,
                'size' => $gallery->ukuran,
                'modified' => $gallery->updated_at->toIso8601String(),
                'owner' => 'You'
            ]);
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                throw $e;
            }
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function renameAjax(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'
        ]);
        
        $user = auth()->user();
        $isFolder = str_starts_with($id, 'f');
        $realId = ltrim($id, 'f');

        if ($isFolder) {
            $folder = Folder::findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $folder)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $folder->update(['nama_folder' => $request->name]);

            // Descendant physical paths update is no longer needed since folders are logical.

            return response()->json([
                'id' => 'f' . $folder->id,
                'type' => 'folder',
                'name' => $folder->nama_folder
            ]);
        } else {
            $file = Gallery::findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFile($user, $file)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $file->update(['nama_tampilan' => ltrim($request->name, '/')]);
            return response()->json([
                'id' => (string)$file->id,
                'type' => 'file',
                'name' => $file->nama_tampilan
            ]);
        }
    }

    public function deleteAjax($id)
    {
        $user = auth()->user();
        $isFolder = str_starts_with($id, 'f');
        $realId = ltrim($id, 'f');

        if ($isFolder) {
            $folder = Folder::findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $folder)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $this->trashFolderAndContents($folder);
        } else {
            $file = Gallery::findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFile($user, $file)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $file->delete();
            // Storage used is NOT recovered on soft delete
        }

        return response()->json(['success' => true]);
    }

    public function shareAjax($id)
    {
        $realId = ltrim($id, 'f');
        $file = Gallery::findOrFail($realId);
        if (!\App\Services\RbacScopeService::canAccessFile(auth()->user(), $file)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        return response()->json([
            'url' => url('/open_file/' . $file->id)
        ]);
    }

    public function notificationsAjax()
    {
        return response()->json([
            ['id' => 1, 'text' => 'Welcome to your Cloud Storage!', 'time' => 'Just now', 'read' => false]
        ]);
    }

    // ──────────────────────────────────────────────────
    // RECENT / STARRED / SHARED / TRASH  (JSON)
    // ──────────────────────────────────────────────────

    public function recentFiles()
    {
        $user = auth()->user();
        $accessibleFolderIds = \App\Services\RbacScopeService::getAccessibleFolderIds($user);
        $files = Gallery::where(function($q) use ($user, $accessibleFolderIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('folder_id', $accessibleFolderIds);
            })
            ->latest()
            ->take(30)
            ->get()
            ->map(fn($f) => $this->mapFile($f));
        return response()->json(['data' => $files, 'total' => $files->count(), 'page' => 1, 'perPage' => 30, 'lastPage' => 1]);
    }

    public function starredFiles()
    {
        $user = auth()->user();
        $accessibleFolderIds = \App\Services\RbacScopeService::getAccessibleFolderIds($user);
        $files = Gallery::where('starred', true)
            ->where(function($q) use ($user, $accessibleFolderIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('folder_id', $accessibleFolderIds);
            })
            ->latest()
            ->get()
            ->map(fn($f) => $this->mapFile($f));
        return response()->json(['data' => $files, 'total' => $files->count(), 'page' => 1, 'perPage' => 100, 'lastPage' => 1]);
    }

    public function sharedFiles()
    {
        $user = auth()->user();
        $accessibleFolderIds = \App\Services\RbacScopeService::getAccessibleFolderIds($user);
        $files = Gallery::where('user_id', '!=', $user->id)
            ->where(function($q) use ($accessibleFolderIds) {
                $q->where('izin', 1)
                  ->orWhereIn('folder_id', $accessibleFolderIds);
            })
            ->latest()
            ->get()
            ->map(fn($f) => $this->mapFile($f));
        return response()->json(['data' => $files, 'total' => $files->count(), 'page' => 1, 'perPage' => 100, 'lastPage' => 1]);
    }

    public function trashedFiles()
    {
        $user = auth()->user();
        $files = $user->galleries()->onlyTrashed()->latest('deleted_at')->get()->map(fn($f) => $this->mapFile($f));
        $folders = $user->folders()->onlyTrashed()->latest('deleted_at')->get()->map(fn($fo) => [
            'id'       => 'f' . $fo->id,
            'type'     => 'folder',
            'name'     => $fo->nama_folder,
            'items'    => 0,
            'modified' => optional($fo->deleted_at)->toIso8601String() ?? now()->toIso8601String(),
            'owner'    => 'You',
            'trashed'  => true,
        ]);
        $all = collect()->merge($folders)->merge($files);
        return response()->json(['data' => $all, 'total' => $all->count(), 'page' => 1, 'perPage' => 100, 'lastPage' => 1]);
    }

    private function mapFile(Gallery $f): array
    {
        $ext = strtolower($f->extension ?: pathinfo($f->nama_tampilan ?: $f->file, PATHINFO_EXTENSION));
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        
        return [
            'id'       => (string)$f->id,
            'type'     => 'file',
            'name'     => $f->nama_tampilan,
            'ext'      => $ext,
            'size'     => $f->ukuran,
            'modified' => optional($f->updated_at)->toIso8601String() ?? now()->toIso8601String(),
            'owner'    => 'You',
            'starred'  => (bool)$f->starred,
            'izin'     => (int)$f->izin,
            'trashed'  => (bool)$f->deleted_at,
            'conversion_status' => $f->conversion_status ?? 'done',
            'preview_type' => $f->preview_type ?: $this->mapPreviewType($ext),
            'preview_path' => $f->preview_path ? url("/open_file_stream/{$f->id}?source=preview") : null,
            'thumbnail_url' => $f->thumbnail_path ? $disk->url($f->thumbnail_path) : null,
        ];
    }

    // ──────────────────────────────────────────────────
    // STAR TOGGLE
    // ──────────────────────────────────────────────────

    public function starAjax($id)
    {
        $isFolder = str_starts_with($id, 'f');
        if ($isFolder) {
            return response()->json(['message' => 'Folders cannot be starred'], 422);
        }
        $file = Gallery::findOrFail($id);
        if (!\App\Services\RbacScopeService::canAccessFile(auth()->user(), $file)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $file->update(['starred' => !$file->starred]);
        return response()->json(['starred' => $file->starred]);
    }

    // ──────────────────────────────────────────────────
    // RESTORE FROM TRASH
    // ──────────────────────────────────────────────────

    public function restoreAjax($id)
    {
        $user = auth()->user();
        $isFolder = str_starts_with($id, 'f');
        $realId = ltrim($id, 'f');
        if ($isFolder) {
            $folder = Folder::onlyTrashed()->findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $folder)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $folder->restore();
        } else {
            $file = Gallery::onlyTrashed()->findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFile($user, $file)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $file->restore();
        }
        return response()->json(['success' => true]);
    }

    // ──────────────────────────────────────────────────
    // PERMANENT DELETE (from trash)
    // ──────────────────────────────────────────────────

    public function forceDeleteAjax($id)
    {
        $user = auth()->user();
        $isFolder = str_starts_with($id, 'f');
        $realId = ltrim($id, 'f');

        if ($isFolder) {
            $folder = Folder::withTrashed()->findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $folder)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            // RECURSIVE PERMANENT DELETE
            $this->permanentDeleteFolder($folder, $user);
        } else {
            $file = Gallery::withTrashed()->findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFile($user, $file)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            if (Storage::disk('local')->exists($file->path)) Storage::disk('local')->delete($file->path);
            
            // Delete preview and thumbnail if they exist
            if ($file->preview_path && Storage::disk('local')->exists($file->preview_path)) Storage::disk('local')->delete($file->preview_path);
            if ($file->thumbnail_path && Storage::disk('public')->exists($file->thumbnail_path)) Storage::disk('public')->delete($file->thumbnail_path);

            $fileOwner = $file->user;
            if ($fileOwner) {
                $fileOwner->decrement('storage_used_bytes', $file->ukuran);
            }
            $file->forceDelete();
        }
        return response()->json(['success' => true]);
    }

    private function permanentDeleteFolder($folder, $user)
    {
        foreach ($folder->files()->withTrashed()->get() as $file) {
            if (Storage::disk('local')->exists($file->path)) Storage::disk('local')->delete($file->path);
            if ($file->preview_path && Storage::disk('local')->exists($file->preview_path)) Storage::disk('local')->delete($file->preview_path);
            if ($file->thumbnail_path && Storage::disk('public')->exists($file->thumbnail_path)) Storage::disk('public')->delete($file->thumbnail_path);

            $fileOwner = $file->user;
            if ($fileOwner) {
                $fileOwner->decrement('storage_used_bytes', $file->ukuran);
            }
            $file->forceDelete();
        }

        foreach ($folder->children()->withTrashed()->get() as $subfolder) {
            $this->permanentDeleteFolder($subfolder, $user);
        }

        if (Storage::exists($folder->path)) Storage::deleteDirectory($folder->path);
        $folder->forceDelete();
    }

    // ──────────────────────────────────────────────────
    // PERMISSION TOGGLE (public / private)
    // ──────────────────────────────────────────────────

    public function permissionAjax(Request $request, $id)
    {
        $user = auth()->user();
        $request->validate(['izin' => 'required|in:0,1']);
        $file = Gallery::findOrFail($id);
        if (!\App\Services\RbacScopeService::canWriteFile($user, $file)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $file->update(['izin' => $request->izin]);
        return response()->json([
            'izin' => $file->izin,
            'url'  => url('/open_file/' . $file->id),
        ]);
    }

    // ──────────────────────────────────────────────────
    // MOVE FILE to another folder
    // ──────────────────────────────────────────────────

    public function moveAjax(Request $request, $id)
    {
        $request->validate(['folder_id' => 'nullable']);
        $user = auth()->user();
        
        $isMovingFolder = str_starts_with($id, 'f');
        $realId = ltrim($id, 'f');

        $newFolderId = ltrim($request->folder_id ?? '', 'f');
        if (empty($newFolderId) || $newFolderId === 'null') $newFolderId = null;

        if ($newFolderId) {
            $destFolder = Folder::findOrFail($newFolderId);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $destFolder)) {
                return response()->json(['message' => 'Anda tidak memiliki akses menulis di folder tujuan.'], 403);
            }
        }

        if ($isMovingFolder) {
            $folderToMove = Folder::findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFolder($user, $folderToMove)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            // ── CIRCULAR MOVE PREVENTION ──────────────────────
            if ($newFolderId) {
                if ($newFolderId == $folderToMove->id) {
                    return response()->json(['message' => 'Cannot move folder into itself'], 422);
                }
                if ($this->isDescendant($newFolderId, $folderToMove)) {
                    return response()->json(['message' => 'Cannot move folder into its own subfolder'], 422);
                }
            }

            $folderToMove->update(['parent_id' => $newFolderId]);

        } else {
            $file = Gallery::findOrFail($realId);
            if (!\App\Services\RbacScopeService::canWriteFile($user, $file)) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $file->update(['folder_id' => $newFolderId]);
        }

        return response()->json(['success' => true]);
    }

    private function isDescendant($targetFolderId, $parentFolder)
    {
        foreach ($parentFolder->children as $child) {
            if ($child->id == $targetFolderId) return true;
            if ($this->isDescendant($targetFolderId, $child)) return true;
        }
        return false;
    }

    // ──────────────────────────────────────────────────
    // FOLDER TREE (for move picker)
    // ──────────────────────────────────────────────────

    public function folderTree()
    {
        $user = auth()->user();
        $accessibleFolderIds = \App\Services\RbacScopeService::getAccessibleFolderIds($user);
        $folders = Folder::whereIn('id', $accessibleFolderIds)
            ->whereNull('deleted_at')
            ->select('id','nama_folder','parent_id')
            ->get();

        $tree = $folders->map(fn($f) => [
            'id'        => 'f' . $f->id,
            'name'      => $f->nama_folder,
            'parent_id' => $f->parent_id ? 'f' . $f->parent_id : null,
        ]);

        return response()->json($tree);
    }
    // ──────────────────────────────────────────────────
    // STREAM FILE (for preview modal)
    // ──────────────────────────────────────────────────

    public function streamFile(Request $request, $id)
    {
        $user = auth()->user();
        $file = Gallery::whereNull('deleted_at')->findOrFail($id);
        if (!\App\Services\RbacScopeService::canAccessFile($user, $file)) {
            abort(403, 'Unauthorized.');
        }

        // If requesting preview version (e.g. WebP image or PDF of a docx)
        if ($request->query('source') === 'preview' && $file->preview_path) {
            $path = $file->preview_path;
            if (!Storage::disk('local')->exists($path)) {
                abort(404);
            }
            $fullPath = Storage::disk('local')->path($path);
            /** @var FilesystemAdapter $localDisk */
            $localDisk = Storage::disk('local');
            $mime = $localDisk->mimeType($path);
            return response()->file($fullPath, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
            ]);
        }

        // Serve original from ZIP archive
        $zipPath = $file->path;
        if (!Storage::disk('local')->exists($zipPath)) {
            abort(404);
        }

        $zipAbsolutePath = Storage::disk('local')->path($zipPath);
        try {
            $extracted = FileArchiveService::extractFirstFileToTemp($zipAbsolutePath);
        } catch (\RuntimeException $e) {
            abort(500, 'Gagal membuka file arsip.');
        }

        $mime = $file->mime_type ?: mime_content_type($extracted['path']);

        return response()->stream(function () use ($extracted) {
            $stream = fopen($extracted['path'], 'rb');
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
            @unlink($extracted['path']);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $file->nama_tampilan . '"'
        ]);
    }

    public function pindah_sampah()
    {
        // SECURITY: filter by user_id untuk mencegah data leak
        $file_sampah = Gallery::where('user_id', auth()->id())->onlyTrashed()->get();
        return view('sampah',compact('file_sampah'));
    }

    public function recent($id)
    {
        $file = Gallery::where("user_id",auth()->id())->latest("riwayat")->get();

        return view('recent',compact('file'));
    }
}
