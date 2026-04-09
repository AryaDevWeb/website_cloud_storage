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
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use Illuminate\Validation\Rule;


class Beranda extends Controller
{
    public function dashboard($id)
    {
        $user = User::findOrFail($id);
        // #region agent log
        $quota = (int) $user->storage_quota;
        $used = (int) $user->storage_used;
        $pctRaw = $quota > 0 ? ($used / $quota) * 100 : null;
        $logLine = json_encode(['sessionId' => '18d670', 'runId' => 'post-fix', 'hypothesisId' => 'H2', 'location' => 'Beranda.php:dashboard', 'message' => 'storage metrics', 'data' => ['user_id' => $user->id, 'storage_used' => $used, 'storage_quota' => $quota, 'pct_raw' => $pctRaw, 'quota_zero' => $quota === 0], 'timestamp' => (int) round(microtime(true) * 1000)])."\n";
        try {
            file_put_contents(base_path('debug-18d670.log'), $logLine, FILE_APPEND | LOCK_EX);
            file_put_contents(storage_path('logs/debug-18d670.log'), $logLine, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
        }
        // #endregion
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
            $ext = strtolower(pathinfo($f->file, PATHINFO_EXTENSION));
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
        $folders = $user->folders()->whereNull('parent_id')->get();
        $files = $user->galleries()->whereNull('folder_id')->get();

        return view('beranda', compact('user', 'folders', 'files'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'upload' => 'required|file|mimetypes:image/jpeg,image/png,image/jpg,application/pdf,video/mp4|max:2048',
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $fileSize = $file->getSize();
            $user = auth()->user();

            // Check if user has enough storage
            if (($user->storage_used + $fileSize) > $user->storage_quota) {
                return back()->with('error', 'Penyimpanan Anda penuh! Sisa ruang: ' . number_format(($user->storage_quota - $user->storage_used) / 1024 / 1024, 2) . ' MB');
            }

            $nama_file = $file->getClientOriginalName();
            $folder_id = $request->input('folder_id');
            $user_id = $user->id;

            // Resolve path
            $storage_path = 'data_user/' . $user_id;
            if ($folder_id) {
                $folder = Folder::findOrFail($folder_id);
                $storage_path = $folder->path;
            }

            $path = Storage::putFileAs($storage_path, $file, $nama_file);

            Gallery::create([
                'user_id' => $user_id,
                'folder_id' => $folder_id,
                'file' => $nama_file,
                'nama_tampilan' => $nama_file,
                'ukuran' => $fileSize,
                'izin' => 1,
                'path' => $path
            ]);

            // Update storage used
            $user->increment('storage_used', $fileSize);

            Wallet::firstOrCreate(['user_id' => $user_id], ['koin' => 0])->increment('koin', 10);

            return back()->with('nama_tampil', $nama_file);
        }
        return back()->with('error', 'Gagal upload file');
    }

    public function hapus_file($id)
    {
        $user = auth()->user();
        $file = $user->galleries()->findOrFail($id);
        
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
                    return $query->where('user_id', auth()->id())->where('parent_id', $request->parent_id);
                })
            ],
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        $user = auth()->user();
        $user_id = $user->id;
        $nama_folder = $request->nama; // Allow spaces
        $parent_id = $request->parent_id;

        $parent_path = 'data_user/' . $user_id;
        if ($parent_id) {
            $parent = $user->folders()->findOrFail($parent_id);
            $parent_path = $parent->path;
        }

        $folder_path = $parent_path . '/' . $nama_folder;

        if (!Storage::exists($folder_path)) {
            Storage::makeDirectory($folder_path);
        }

        Folder::create([
            'nama_folder' => $nama_folder,
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'permission' => 1,
            'path' => $folder_path
        ]);

        Wallet::firstOrCreate(['user_id' => $user_id], ['koin' => 0])->increment('koin', 10);

        return back()->with('notif', 'Folder berhasil ditambahkan!');
    }

    public function new_folder($id)
    {
        // Enforce ownership and exclude TRASHED folders
        $isi_folder = auth()->user()->folders()->with(['children', 'user', 'files'])->findOrFail($id);
        
        if ($isi_folder->trashed()) {
            abort(404, 'Folder is in Trash');
        }

        if ($isi_folder->permission == 0 && $isi_folder->user_id != auth()->id()) {
            abort(403, 'Maaf Anda tidak memiliki izin');
        }

        return view('isi', compact('isi_folder'));
    }

    public function pencarian(Request $request)
    {
        $kunci = $request->cari;
        $user = auth()->user();

        if ($kunci) {
            $folders = $user->folders()->where('nama_folder', 'LIKE', '%' . $kunci . '%')->get();
            $files = $user->galleries()->where('nama_tampilan', 'LIKE', '%' . $kunci . '%')->get();
        } else {
            $folders = $user->folders()->whereNull('parent_id')->get();
            $files = $user->galleries()->whereNull('folder_id')->get();
        }

        return view('beranda', compact('user', 'folders', 'files'));
    }

    public function hapus_folder($id)
    {
        $user = auth()->user();
        $folder = $user->folders()->findOrFail($id);

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
        $isi_file = auth()->user()->galleries()->findOrFail($id);
        return view('permission', compact('isi_file'));
    }

    public function ubah_izin(Request $request, $id)
    {
        $file = auth()->user()->galleries()->findOrFail($id);
        $request->validate(['izin' => 'required|in:0,1']);

        // Simplified: Permission is now handled by application-level scoping, not on-disk encryption.
        $file->update(['izin' => $request->izin]);

        return back()->with('status', 'Izin file menjadi ' . ($request->izin == 1 ? 'Public' : 'Private'));
    }

    public function masuk_izin($id)
    {
        $izin_folder = auth()->user()->folders()->findOrFail($id);
        return view('izin_folder', compact('izin_folder'));
    }

    public function folder_permission(Request $request, $id)
    {
        $folder = auth()->user()->folders()->findOrFail($id);
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

        $path = 'data_user/' . $user->id;
        if (Storage::exists($path)) {
            Storage::deleteDirectory($path);
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
        $file = Gallery::findOrFail($id);
        
        // If owner or public
        if ($file->user_id == auth()->id() || $file->izin == 1) {
            return Storage::download($file->path, $file->nama_tampilan);
        }

        return back()->with('error', 'Maaf Anda tidak bisa mendownload file ini');
    }

    public function pindah($id)
    {
        $ubah_nama = auth()->user()->galleries()->findOrFail($id);
        return view('rename', compact('ubah_nama'));
    }

    public function rename(Request $request, $id)
    {
        $file = auth()->user()->galleries()->findOrFail($id);
        $request->validate(['ubah_nama' => 'required']);

        $nama_baru = $request->ubah_nama;
        $file->update(['nama_tampilan' => $nama_baru]);

        return redirect()->route('beranda', auth()->id())->with('status', 'File berhasil di-rename!');
    }

    public function pindah_rename($id)
    {
        $cari_folder = auth()->user()->folders()->findOrFail($id);
        return view('rename_folder', compact('cari_folder'));
    }

    public function rename_f(Request $request, $id)
    {
        $folder = auth()->user()->folders()->findOrFail($id);
        $request->validate(['rename' => 'required']);

        $nama_baru = $request->rename; // Allow spaces
        $folder->update(['nama_folder' => $nama_baru]);

        return back()->with('status', 'Folder berhasil di-rename');
    }

    public function open_file($id)
    {
        $file = Gallery::findOrFail($id);

        // Check ownership or if it's public (izin == 1)
        if ($file->user_id != auth()->id() && $file->izin == 0) {
            abort(403, 'Maaf File ini bersifat private');
        }

        $path = storage_path('app/' . $file->path);
        $waktu = is_null($file->riwayat) ? 'belum pernah dilihat' : $file->riwayat->diffForHumans();

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        $extension = strtolower(pathinfo($file->file, PATHINFO_EXTENSION));
        
        // Auto-assign preview_type if not set
        if (!$file->preview_type) {
            $file->preview_type = $this->mapPreviewType($extension);
        }

        return view('lihat', [
            'file' => $file,
            'extension' => $extension,
            'waktu' => $waktu,
            'preview_type' => $file->preview_type,
            'status' => $file->status ?? 'ready',
        ]);
    }

    private function mapPreviewType($ext): string
    {
        return match(true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']) => 'image',
            in_array($ext, ['mp4', 'webm', 'mov', 'avi']) => 'video',
            in_array($ext, ['mp3', 'wav', 'ogg', 'flac']) => 'audio',
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['txt', 'md', 'json', 'js', 'php', 'py', 'css', 'html', 'sh', 'sql']) => 'text/code',
            in_array($ext, ['docx', 'xlsx', 'pptx']) => 'office',
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

        // SCOPE ALL QUERIES BY USER
        $foldersQuery = $user->folders()->withCount(['children', 'files']);
        $filesQuery = $user->galleries();

        // ── VERIFY FOLDER OWNERSHIP ──────────────────────────
        if ($folderId) {
            $user->folders()->findOrFail($folderId); // 404 if not owned
        }

        if ($q) {
            $foldersQuery->where('nama_folder', 'LIKE', '%' . $q . '%');
            $filesQuery->where('nama_tampilan', 'LIKE', '%' . $q . '%');
        } else {
            if ($folderId) {
                $foldersQuery->where('parent_id', $folderId);
                $filesQuery->where('folder_id', $folderId);
            } else {
                $foldersQuery->whereNull('parent_id');
                $filesQuery->whereNull('folder_id');
            }
        }

        $folders = $foldersQuery->get()->map(function($f) {
            return [
                'id' => 'f' . $f->id,
                'type' => 'folder',
                'name' => $f->nama_folder,
                'items' => $f->children_count + $f->files_count,
                'modified' => $f->updated_at ? $f->updated_at->toIso8601String() : now()->toIso8601String(),
                'owner' => 'You'
            ];
        });

        $files = $filesQuery->get()->map(function($f) {
            $ext = strtolower(pathinfo($f->file, PATHINFO_EXTENSION));
            return [
                'id' => (string)$f->id,
                'type' => 'file',
                'name' => $f->nama_tampilan,
                'ext' => $ext,
                'size' => $f->ukuran,
                'modified' => $f->updated_at ? $f->updated_at->toIso8601String() : now()->toIso8601String(),
                'owner' => 'You',
                'status' => $f->status ?? 'ready',
                'preview_type' => $f->preview_type ?: $this->mapPreviewType($ext),
                'preview_path' => $f->preview_path,
            ];
        });

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

        $exists = $user->folders()
            ->where('parent_id', $parent_id)
            ->where('nama_folder', $nama_folder)
            ->exists();
            
        if ($exists) {
            return response()->json(['message' => 'Folder already exists'], 422);
        }

        $parent_path = 'data_user/' . $user_id;
        if ($parent_id) {
            $parent = $user->folders()->findOrFail($parent_id);
            $parent_path = $parent->path;
        }

        $folder_path = $parent_path . '/' . $nama_folder;

        if (!Storage::exists($folder_path)) {
            Storage::makeDirectory($folder_path);
        }

        $folder = Folder::create([
            'nama_folder' => $nama_folder,
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'permission' => 1,
            'path' => $folder_path
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
            $request->validate([
                'file' => 'required|file',
                'folder_id' => 'nullable'
            ]);
    
            $file = $request->file('file');
            $fileSize = $file->getSize();
            $user = auth()->user();
    
            if (($user->storage_used + $fileSize) > $user->storage_quota) {
                return response()->json(['message' => 'Penyimpanan penuh'], 422);
            }
    
            $nama_file = ltrim($file->getClientOriginalName(), '/');
            $folder_id = ltrim($request->input('folder_id'), 'f');
            if (empty($folder_id) || $folder_id === 'null' || $folder_id === 'undefined') {
                $folder_id = null;
            }
    
            $user_id = $user->id;
    
            $storage_path = 'data_user/' . $user_id;
            if ($folder_id) {
                $folder = Folder::findOrFail($folder_id);
                $storage_path = $folder->path;
            }
    
            $path = Storage::putFileAs($storage_path, $file, rtrim($nama_file));
    
            $gallery = Gallery::create([
                'user_id' => $user_id,
                'folder_id' => $folder_id,
                'file' => rtrim($nama_file),
                'nama_tampilan' => rtrim($nama_file),
                'ukuran' => $fileSize,
                'izin' => 1,
                'path' => $path
            ]);
    
            $user->increment('storage_used', $fileSize);
            Wallet::firstOrCreate(['user_id' => $user_id], ['koin' => 0])->increment('koin', 10);
    
            return response()->json([
                'id' => (string)$gallery->id,
                'type' => 'file',
                'name' => $gallery->nama_tampilan,
                'ext' => strtolower(pathinfo($gallery->file, PATHINFO_EXTENSION)),
                'size' => $gallery->ukuran,
                'modified' => $gallery->updated_at->toIso8601String(),
                'owner' => 'You'
            ]);
        } catch (\Exception $e) {
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
            $folder = $user->folders()->findOrFail($realId);

            // ── HANDLE PHYSICAL RENAME FOR FOLDERS ──────────────────
            $oldPath = $folder->path;
            $newPath = dirname($oldPath) . '/' . $request->name;

            if (Storage::exists($oldPath)) {
                Storage::move($oldPath, $newPath);
            }

            $folder->update(['nama_folder' => $request->name, 'path' => $newPath]);

            // ── RECURSIVELY UPDATE DESCENDANT PATHS ───────────
            $this->updateDescendantPaths($folder, $oldPath, $newPath);

            return response()->json([
                'id' => 'f' . $folder->id,
                'type' => 'folder',
                'name' => $folder->nama_folder
            ]);
        } else {
            $file = $user->galleries()->findOrFail($realId);
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
            $folder = $user->folders()->findOrFail($realId);
            $this->trashFolderAndContents($folder);
        } else {
            $file = $user->galleries()->findOrFail($realId);
            $file->delete();
            // Storage used is NOT recovered on soft delete
        }

        return response()->json(['success' => true]);
    }

    public function shareAjax($id)
    {
        $realId = ltrim($id, 'f');
        // Ensure user owns it
        $file = auth()->user()->galleries()->findOrFail($realId);
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
        $files = $user->galleries()->latest()->take(30)->get()->map(fn($f) => $this->mapFile($f));
        return response()->json(['data' => $files, 'total' => $files->count(), 'page' => 1, 'perPage' => 30, 'lastPage' => 1]);
    }

    public function starredFiles()
    {
        $user = auth()->user();
        $files = $user->galleries()->where('starred', true)->latest()->get()->map(fn($f) => $this->mapFile($f));
        return response()->json(['data' => $files, 'total' => $files->count(), 'page' => 1, 'perPage' => 100, 'lastPage' => 1]);
    }

    public function sharedFiles()
    {
        $user = auth()->user();
        $files = $user->galleries()->where('izin', 1)->latest()->get()->map(fn($f) => $this->mapFile($f));
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
        $ext = strtolower(pathinfo($f->file, PATHINFO_EXTENSION));
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
            'status'   => $f->status ?? 'ready',
            'preview_type' => $f->preview_type ?: $this->mapPreviewType($ext),
            'preview_path' => $f->preview_path,
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
        $file = auth()->user()->galleries()->findOrFail($id);
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
            $user->folders()->onlyTrashed()->findOrFail($realId)->restore();
        } else {
            $user->galleries()->onlyTrashed()->findOrFail($realId)->restore();
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
            $folder = $user->folders()->withTrashed()->findOrFail($realId);
            // RECURSIVE PERMANENT DELETE
            $this->permanentDeleteFolder($folder, $user);
        } else {
            $file = $user->galleries()->withTrashed()->findOrFail($realId);
            if (Storage::exists($file->path)) Storage::delete($file->path);
            $user->decrement('storage_used', $file->ukuran);
            $file->forceDelete();
        }
        return response()->json(['success' => true]);
    }

    private function permanentDeleteFolder($folder, $user)
    {
        foreach ($folder->files()->withTrashed()->get() as $file) {
            if (Storage::exists($file->path)) Storage::delete($file->path);
            $user->decrement('storage_used', $file->ukuran);
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
        $file = $user->galleries()->findOrFail($id);
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

        if ($isMovingFolder) {
            $folderToMove = $user->folders()->findOrFail($realId);

            // ── CIRCULAR MOVE PREVENTION ──────────────────────
            if ($newFolderId) {
                if ($newFolderId == $folderToMove->id) {
                    return response()->json(['message' => 'Cannot move folder into itself'], 422);
                }
                if ($this->isDescendant($newFolderId, $folderToMove)) {
                    return response()->json(['message' => 'Cannot move folder into its own subfolder'], 422);
                }
            }

            $oldPath = $folderToMove->path;
            $newParentPath = $newFolderId ? $user->folders()->findOrFail($newFolderId)->path : 'data_user/' . $user->id;
            $newPath = $newParentPath . '/' . $folderToMove->nama_folder;

            if (Storage::exists($oldPath)) {
                Storage::move($oldPath, $newPath);
            }

            $folderToMove->update(['parent_id' => $newFolderId, 'path' => $newPath]);
            $this->updateDescendantPaths($folderToMove, $oldPath, $newPath);

        } else {
            $file = $user->galleries()->findOrFail($realId);
            $newParentPath = $newFolderId ? $user->folders()->findOrFail($newFolderId)->path : 'data_user/' . $user->id;
            $newPath = $newParentPath . '/' . $file->file;

            if (Storage::exists($file->path)) {
                Storage::move($file->path, $newPath);
            }

            $file->update(['folder_id' => $newFolderId, 'path' => $newPath]);
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
        // Exclude trashed folders
        $folders = $user->folders()->whereNull('deleted_at')->select('id','nama_folder','parent_id')->get();

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
        // ENSURE SECURITY: User must own the file OR the file must be public (izin == 1)
        $file = Gallery::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('izin', 1);
        })->findOrFail($id);

        $path = $file->path;

        // If requesting preview version (e.g. PDF of a docx)
        if ($request->query('source') === 'preview' && $file->preview_path) {
            $path = $file->preview_path;
        }

        if (!Storage::exists($path)) {
            abort(404);
        }

        return response()->file(Storage::path($path));
    }

    private function updateDescendantPaths($folder, $oldPath, $newPath)
    {
        // Update direct files
        foreach ($folder->files as $file) {
            $fileRelativePath = str_replace($oldPath, $newPath, $file->path);
            $file->update(['path' => $fileRelativePath]);
        }

        // Update child folders and recurse
        foreach ($folder->children as $subfolder) {
            $subfolderOldPath = $subfolder->path;
            $subfolderNewPath = str_replace($oldPath, $newPath, $subfolderOldPath);
            $subfolder->update(['path' => $subfolderNewPath]);
            $this->updateDescendantPaths($subfolder, $subfolderOldPath, $subfolderNewPath);
        }
    }
}


