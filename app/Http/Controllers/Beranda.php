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
        $folder = Folder::where("user_id",$id)->whereNull("parent_id")->get();
        $totalFiles = $user->galleries()->count();
        $totalFolders = $user->folders()->count();
        $usedMB = number_format($user->storage_used / 1024 / 1024, 1);
        $remainingMB = number_format(($user->storage_quota - $user->storage_used) / 1024 / 1024, 1);
        $totalMB = number_format($user->storage_quota / 1024 / 1024, 0);
        $percentage = ($user->storage_used / $user->storage_quota) * 100;
        $recentFiles = $user->galleries()->latest()->take(5)->get();
        $file = Gallery::where("user_id",$id)->whereNull("folder_id")->get();

        return view('dashboard', compact(
            'user', 'totalFiles', 'totalFolders', 'usedMB', 'remainingMB', 'totalMB', 'percentage', 'recentFiles','folder','file'
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
        'upload' => 'required|file|mimetypes:image/jpeg,image/png,image/jpg,application/pdf,video/mp4,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:5120',
        'folder_id' => 'nullable|exists:folders,id'
    ]);

    if ($request->hasFile('upload')) {
        $file = $request->file('upload');
        $fileSize = $file->getSize();
        $user = auth()->user();

        // Check storage quota
        if (($user->storage_used + $fileSize) > $user->storage_quota) {
            return back()->with('error', 'Penyimpanan penuh!');
        }

        $tipe_file = $file->getClientOriginalExtension();
        $nama_asli = $file->getClientOriginalName();
        $user_id = $user->id;
        $folder_id = $request->input('folder_id');

        // 1. Tentukan Base Storage Path
        $storage_path = 'data_user/' . $user_id; // Hasil: data_user/2
        if ($folder_id) {
            $folder = Folder::findOrFail($folder_id);
            $storage_path = $folder->path; 
        }

        $tempat_sementara = storage_path("app/temp/");
        if (!file_exists($tempat_sementara)) mkdir($tempat_sementara, 0777, true);

        // 2. Logika Konversi
        if (in_array($tipe_file, ['docx', 'pptx', 'xlsx'])) {
            $file_nama_murni = pathinfo($nama_asli, PATHINFO_FILENAME);
            $tempFile = $file->move($tempat_sementara, $nama_asli);

            // Perhatikan SPASI setelah --outdir
            $command = "libreoffice --headless --convert-to pdf --outdir " . escapeshellarg($tempat_sementara) . " " . escapeshellarg($tempFile);
            shell_exec($command);

            $nama_file_baru = $file_nama_murni . '.pdf';
            $path_pdf_hasil = $tempat_sementara . $nama_file_baru;

            if (file_exists($path_pdf_hasil)) {
                $path = Storage::putFileAs($storage_path, new \Illuminate\Http\File($path_pdf_hasil), $nama_file_baru);
                $fileSize = filesize($path_pdf_hasil);
                $nama_tampilan = $nama_file_baru;
                
                // Hapus file temp
                unlink($tempFile);
                unlink($path_pdf_hasil);
            } else {
                return back()->with('error', 'Gagal mengonversi file ke PDF.');
            }
        } else {
            // Upload Biasa
            $path = Storage::putFileAs($storage_path, $file, $nama_asli);
            $nama_tampilan = $nama_asli;
        }

        // 3. Simpan ke Database
        Gallery::create([
            'user_id' => $user_id,
            'folder_id' => $folder_id,
            'file' => $nama_tampilan,
            'nama_tampilan' => $nama_tampilan,
            'ukuran' => $fileSize,
            'izin' => 1,
            'path' => $path,
            'riwayat' => now()
        ]);

        $user->increment('storage_used', $fileSize);
        Wallet::firstOrCreate(['user_id' => $user_id], ['koin' => 0])->increment('koin', 10);

        return back()->with('nama_tampil', $nama_tampilan);
    }
    return back()->with('error', 'Gagal upload file');
}

    public function hapus_file($id)
    {
        $file = Gallery::findOrFail($id);
        $fileSize = $file->ukuran;
        $user = auth()->user();

        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }
        $file->delete();

        // Update storage used
        $user->decrement('storage_used', $fileSize);

        return back()->with('status_file', 'File berhasil dihapus');
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

        $user_id = auth()->id();
        $nama_sanitized = Str::slug($request->nama, '_');
        $parent_id = $request->parent_id;

        $parent_path = 'data_user/' . $user_id;
        if ($parent_id) {
            $parent = Folder::findOrFail($parent_id);
            $parent_path = $parent->path;
        }

        $folder_path = $parent_path . '/' . $nama_sanitized;

        if (!Storage::exists($folder_path)) {
            Storage::makeDirectory($folder_path);
        }

        Folder::create([
            'nama_folder' => $nama_sanitized,
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
        $isi_folder = Folder::with(['children', 'user', 'files'])->findOrFail($id);

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
            $pemisah_str = str_replace(' ', '_', $kunci);
            $folders = $user->folders()->where('nama_folder', 'LIKE', '%' . $pemisah_str . '%')->get();
            $files = $user->galleries()->where('nama_tampilan', 'LIKE', '%' . $pemisah_str . '%')->get();
        } else {
            $folders = $user->folders()->whereNull('parent_id')->get();
            $files = $user->galleries()->whereNull('folder_id')->get();
        }

        return view('beranda', compact('user', 'folders', 'files'));
    }

    public function hapus_folder($id)
    {
        $folder = Folder::findOrFail($id);
        $user = auth()->user();

        // Recursively find all nested files to update storage_used
        $this->deleteFolderAndContents($folder, $user);

        return redirect()->route('beranda', $user->id)->with('folder_status', "Folder " . $folder->nama_folder . " berhasil dihapus");
    }

    private function deleteFolderAndContents($folder, $user)
    {
        // Delete all files in this folder
        foreach ($folder->files as $file) {
            $user->decrement('storage_used', $file->ukuran);
            if (Storage::exists($file->path)) {
                Storage::delete($file->path);
            }
            $file->delete();
        }

        // Recursively delete subfolders
        foreach ($folder->children as $subfolder) {
            $this->deleteFolderAndContents($subfolder, $user);
        }

        // Delete the physical directory
        if (Storage::exists($folder->path)) {
            Storage::deleteDirectory($folder->path);
        }

        // Delete the folder record
        $folder->delete();
    }

    public function izin_file($id)
    {
        $isi_file = Gallery::findOrFail($id);
        if ($isi_file->user_id != auth()->id() && $isi_file->izin == 0) {
            abort(403, 'Maaf File ini bersifat private');
        }
        return view('permission', compact('isi_file'));
    }

    public function ubah_izin(Request $request, $id)
    {
        $file = Gallery::findOrFail($id);
        $request->validate(['izin' => 'required|in:0,1']);

        $old_izin = $file->izin;
        $file->update(['izin' => $request->izin]);

        $path = storage_path('app/' . $file->path);

        if (!file_exists($path)) {
            return back()->with('error', 'File fisik tidak ditemukan');
        }

        $content = file_get_contents($path);

        // Encryption logic:
        // If changing to private (0) from public (1)
        if ($request->izin == 0 && $old_izin == 1) {
            file_put_contents($path, encrypt($content));
        } 
        // If changing to public (1) from private (0)
        elseif ($request->izin == 1 && $old_izin == 0) {
            try {
                file_put_contents($path, decrypt($content));
            } catch (Exception $e) {
                // Already public or decryption failed
            }
        }

        return back()->with('status', 'Izin file menjadi ' . ($request->izin == 1 ? 'Public' : 'Private'));
    }

    public function masuk_izin($id)
    {
        $izin_folder = Folder::findOrFail($id);
        return view('izin_folder', compact('izin_folder'));
    }

    public function folder_permission(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);
        $request->validate(['izin' => 'required|in:0,1']);
        $folder->update(['permission' => $request->izin]);

        return back()->with('status', 'Izin folder menjadi ' . ($request->izin == 1 ? 'Public' : 'Private'));
    }

    public function lihat_akun($id)
    {
        $lihat_akun = User::findOrFail($id);
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

        $user->delete();
        Auth::logout();

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
        
        if ($file->user_id == auth()->id() || $file->izin == 1) {
            return Storage::download($file->path);
        }

        return back()->with('error', 'Maaf Anda tidak bisa mendownload file ini');
    }

    public function pindah($id)
    {
        $ubah_nama = Gallery::findOrFail($id);
        return view('rename', compact('ubah_nama'));
    }

    public function rename(Request $request, $id)
    {
        $file = Gallery::findOrFail($id);
        $request->validate(['ubah_nama' => 'required']);

        $nama_baru = Str::slug($request->ubah_nama, '_');
        $file->update(['nama_tampilan' => $nama_baru]);

        return redirect()->route('beranda', auth()->id())->with('status', 'File berhasil di-rename!');
    }

    public function pindah_rename($id)
    {
        $cari_folder = Folder::findOrFail($id);
        return view('rename_folder', compact('cari_folder'));
    }

    public function rename_f(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);
        $request->validate(['rename' => 'required']);

        $nama_baru = Str::slug($request->rename, '_');
        $folder->update(['nama_folder' => $nama_baru]);

        return back()->with('status', 'Folder berhasil di-rename');
    }
    

    public function open_file($id)
    {
        $file = Gallery::findOrFail($id);
        $path = storage_path('app/' . $file->path);

        $waktu = is_null($file->riwayat) ? 'belum pernah dilihat' : $file->riwayat->diffForHumans();

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        $extension = strtolower(pathinfo($file->file, PATHINFO_EXTENSION));
        $content = file_get_contents($path);

        // If it's private (izin == 0), it might be encrypted (legacy)
        // Note: New files won't be physicaly encrypted per the new plan, 
        // but we keep this for backward compatibility if needed.
        if ($file->izin == 0) {
            try {
                $decrypted = decrypt($content);
                $content = $decrypted;
            } catch (Exception $e) {
                // Not encrypted or wrong key
            }
        }

        $base64 = base64_encode($content);
        $mime = '';
        
        switch($extension) {
            case 'jpg': case 'jpeg': $mime = 'image/jpeg'; break;
            case 'png': $mime = 'image/png'; break;
            case 'gif': $mime = 'image/gif'; break;
            case 'pdf': $mime = 'application/pdf'; break;
            case 'mp4': $mime = 'video/mp4'; break;
            case 'webm': $mime = 'video/webm'; break;
            default: $mime = 'text/plain';
        }

        return view('lihat', compact('base64', 'file', 'extension', 'mime','waktu'));
    }
    

    public function pindah_sampah()
    {
        $file_sampah = Gallery::onlyTrashed()->get();
        return view('sampah',compact('file_sampah'));
    }

    public function recent($id)
    {
        $file = Gallery::where("user_id",auth()->id())->latest("riwayat")->get();

        return view('recent',compact('file'));
    }
}
