<?php

namespace App\Http\Controllers;

use Attribute;
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


class Beranda extends Controller {

    public function akun($id)
    {
        $user = User::find($id);
        $folders = $user->folders()->whereNull('parent_id')->get();
        $files = $user->galleries()->get();

        return view('beranda', compact('user', 'folders', 'files'));
    }
    public function upload(Request $upload)
    {
        $upload->validate([
            'upload' => 'required|file|mimetypes:image/jpeg,image/png,image/jpg,application/pdf,video/mp4|max:2048'

        ]);

        if ($upload->hasFile('upload')) {
            $file = $upload->file('upload');

            $nama_file =  $file->getClientOriginalName();

            $path = Storage::putFileAs('data_user/' . auth()->id(),$file,$nama_file);

            $ukuran_file = $file->getSize();

            

            Gallery:: create([
                'user_id' => auth()->id(),
                'file' => $nama_file,
                'nama_tampilan' => $nama_file,
                'ukuran' => $ukuran_file,
                'izin' => 1,
                'path' => $path

            ]);

            
            $nama_tampil = $nama_file;

            $koin_user = Wallet::firstOrCreate(
                ['user_id' => auth()->id()],
                ['koin' => 0]
                );

            $koin_user->increment('koin',amount: 10);

            return redirect('/beranda/'.auth()->id())->with('nama_tampil',$nama_tampil);
        
        }
    }
    public function hapus($id)
    {
        $hapus_file = Gallery::find($id);

        $akun = auth()->id();


        Storage::delete('data_user/' . $akun . '/' . $hapus_file->file);
        
        $hapus_file->delete();

        return redirect('/beranda/' . auth()->id())->with('hapus_sukses', 'data: ' . $hapus_file->file . ' berhasil dihapus' );
    }

    public function folder(Request $folderBaru)
    {
        $folderBaru->validate([
            'nama' => [
                'required',
                'min:3',
                Rule::unique('folders','nama_folder')->where(function ($query) use ($folderBaru) {
                    return $query->where('user_id', auth()->id())->where('parent_id', $folderBaru->parent_id);

                })
            ],

        ]);

        $user = auth()->id();

        $strip_andalan = str_replace(' ','_',$folderBaru->nama);

         if ($folderBaru->parent_id) {
            $folder_utama = Folder::find($folderBaru->parent_id);
            $tempat_direktori = 'data_user/' . $user . '/' . $folder_utama->nama_folder .'/' . $folderBaru->nama_folder;

        }else {
            $tempat_direktori = 'data_user/' . $user .'/' . $folderBaru->nama_folder;

        }

        if (!Storage::exists($tempat_direktori)) {
            $path = Storage::makeDirectory($tempat_direktori);
            
        }




        $folder_kedua =  Folder:: create([
            'nama_folder' => $strip_andalan,
            'user_id' => auth()->id(),
            'parent_id' => $folderBaru->input('parent_id'),
            'permission' => 1,
            'path' => $tempat_direktori

        ]);

        if ($folderBaru->nama == $folder_kedua->nama_folder) {
            return back()->with('status','maaf nama folder tidak boleh sama');
        }

  


        $tambah_koin = Wallet::firstOrCreate(
            ['user_id' => auth()->id()],
            ['koin' =>  0 ]
        );
        $tambah_koin->increment('koin', 10);

      
            

        return back()
        ->with('notif','folder berhasil ditambakan!');


    }

    public function new_folder($id)
    {
        $isi_folder = Folder::with(relations: ['children', 'user'])->findOrFail(id: $id);

        if ( $isi_folder->permission == 0) {
            abort(403, 'maaf anda tidak ada perizinan');
        }



        return view('isi', data: compact(var_name: 'isi_folder'));
    }

    public function pencarian(Request $cari)
    {
        $kunci = $cari->cari;
        
        $user = auth()->user();


        if($kunci) {
            $pemisah_str = str_replace(' ','_',$kunci);

            $folders = $user->folders()->where('nama_folder', 'LIKE' , '%' . $pemisah_str . '%')->get();
            $files = $user->galleries()->where('nama_tampilan', 'LIKE' , '%' . $pemisah_str . '%')->get();


        }else {
            $folders = $user->folders()->whereNull('parent_id')->get();
            $files = $user->galleries();
        }
     
        return view('beranda',compact('user','folders','files'));
        
    }

    public function hapus_folder($id)
    {
        $hapus_folder = Folder::find($id);

        $akun = auth()->id();


        $hapus_folder->delete();

        $tempat_hapus = 'data_user/' . $akun . '/' . $hapus_folder->nama_folder;

        Storage::deleteDirectory($tempat_hapus);
        $nama = $hapus_folder->nama_folder;
    



        return redirect('/beranda/' .auth()->id())->with('folder_status',"folder " . $nama ." berhasil dihapus");

    }
    public function hapus_subfolder($id)
    {
        $subfolder_hapus = Folder::find(id: $id);
        $parent_id = $subfolder_hapus->parent_id;

        $akun = auth()->id();


        if ($parent_id) {
            $folder_utama = Folder::find($parent_id);
            $tempat_subfolder = 'data_user/' . $akun . '/' . $folder_utama->nama_folder . '/' . $subfolder_hapus->nama_folder;
            
        }   
       
        if (Storage::exists($tempat_subfolder)) {
            Storage::deleteDirectory($tempat_subfolder);
        }

        



        $subfolder_hapus->delete();

        return redirect('/folder_open/' . $parent_id)->with('status_subfolder', 'Folder berhasil dihapus');
    }

    public function upload_subfolder(Request $upload_subfolder)
    {
        $upload_subfolder->validate(rules: [
            'upload' => 'required|file|mimetypes:image/jpeg,image/png,image/jpg,application/pdf|max:2048'

        ]);

        if ($upload_subfolder->hasFile('upload')) {
            $file = $upload_subfolder->file('upload');

            $nama =  $file->getClientOriginalName();

            $ukuran = $file->getSize();



            $folder_utama = Folder::find($upload_subfolder->input('folder_id'));

            $parent_id = $folder_utama->parent_id;
            $akun = auth()->id();

        




            if ($folder_utama->id) {
                $tempat_file = 'data_user/' . $akun . '/' . $folder_utama->nama_folder;
            }

            if ($parent_id) {
                $folder_kedua = Folder::find($parent_id);
                $tempat_file = 'data_user/' . $akun . '/' . $folder_kedua->nama_folder . '/' . $folder_utama->nama_folder;
            }

            $path = Storage::putFileAs($tempat_file,$file,$nama);

             Gallery::create([
                'user_id' => auth()->id(),
                'folder_id' => $upload_subfolder->input('folder_id'),
                'file' => $nama,
                'ukuran' => $ukuran,
                'nama_tampilan' => $nama,
                'path' => $path


            ]);

    
            return back()->with('nama_tampil' , $nama);
        }

    }

    public function hapus_file($id)
    {
        $hapus_isi = Gallery::find($id);
        $folder_utama = $hapus_isi->folder_id;

        $cari_folder = Folder::find($folder_utama);
        $parent_id = $cari_folder->parent_id;

        $akun = auth()->id();

        if ($cari_folder->id) {
            $tempat = 'data_user/' .$akun . '/' . $cari_folder->nama_folder . '/' . $hapus_isi->file;

        }

        if ($parent_id) {
            $folder_kedua = Folder::find($parent_id);
            $tempat = 'data_user/' . $akun . '/' . $folder_kedua->nama_folder . '/' . $cari_folder->nama_folder . '/' . $hapus_isi->file;
        }
 


        if (Storage::exists($tempat)) {
            Storage::delete($tempat);
        }

        $hapus_isi->delete();

        return back()->with('status_file', "File Berhasil dihapus");
    }

    public function izin_file($id)
    {
        $isi_file = Gallery::find($id);

        if ($isi_file->user_id != auth()->id() && $isi_file->izin == 0) {
            abort(403, 'Maaf File berisi private');
        }


        return view('permission', data: compact('isi_file'));
    }

    public function ubah_izin(Request $perizinan , $id)
    {
        $ubah = Gallery::findOrFail($id);

         $ubah->update([
            'izin' => $perizinan->izin
        ]);



        $tempat = storage_path('data_user/' . auth()->id() . '/' . $ubah->file);


        if (!file_exists($tempat)) {
            return back()->with('izin_error','file tidak ada');
        }

        $ambil_konten = file_get_contents($tempat);


        if ($perizinan->izin == 0 && $ubah->izin != 0 || is_null($perizinan->izin)) {
            $enrkipsi_file = encrypt($ambil_konten);
            file_put_contents($tempat,$enrkipsi_file);
        }

        if ($perizinan->izin == 1 && $ubah->izin == 0) {
            try {
                $dekripsi_file = decrypt($ambil_konten);
                file_put_contents($tempat,$dekripsi_file);
            }catch (Exception $e) {

            }
        }

    

        if ($ubah->izin == 1) {
            $pesan = 'public';
        }
        if ($ubah->izin == 0) {
            $pesan = 'private';
        }


        return back()->with('status', 'File '. $ubah->file . ' Menjadi ' . $pesan);
    }

    public function masuk_izin($id)
    {
        $izin_folder = Folder::find($id);

        return view('izin_folder',compact('izin_folder'));
    }

    public function folder_permission(Request $permission , $id)
    {
        $folder_izin = Folder::find($id);

        $folder_izin->update([
            'permission' => $permission->izin
        ]);

        if ($folder_izin->permission == 0) {
            $pesan = 'Private';
        }

        if ($folder_izin->permission == 1) {
            $pesan = 'Public';
        }

        return back()->with('status', 'File ' . $folder_izin->nama_folder . " menjadi " . $pesan );
    }

    public function hapus_akun($id)
    {
        $cari_akun = User::findOrFail($id);

        $tempat = 'data_user/' . auth()->id();


        if (Storage::exists($tempat)) {
            Storage::deleteDirectory($tempat);
        }
 
        $cari_akun->delete();

        $pesan = 'akun berhasil dihapus';

        return view('register',compact('pesan'));
    }

    public function lihat_akun($id)
    {
        $lihat_akun = User::find($id);

        return view('akun',compact('lihat_akun'));
    }

    public function logout(Request $keluar)
    {
        Auth::logout();

        $keluar->session()->invalidate();
        $keluar->session()->regenerateToken();


        return view('register');
    }


    public function download_file($id)
    {
        $file = Gallery::find($id);

        $user_sekarang = auth()->id();

       
        $tempat = 'data_user/' . $user_sekarang . '/' . $file->file;


    

        if ($file->user_id == $user_sekarang && $file->izin == 1) {
          
            return Storage::download($tempat);
        }

        return back()->with('status','maaf anda tidak bisa download file');

       
       



        
    }

    public function pindah($id)
    {
        $ubah_nama = Gallery::find($id);

        return view('rename',compact('ubah_nama'));
    }

    public function rename( Request $ubah , $id)
    {
        $cari_nama = Gallery::find($id);

        $ubah->validate([
            'ubah_nama' => 'required'

        ]);

        $nama_baru = str_replace(' ','_',$ubah->input('ubah_nama'));

        $cari_nama->update(
         
            ['nama_tampilan' => $nama_baru]
        );

        $status_rename = 'File berhasil direname!';  

        return redirect('/beranda/' . auth()->id())->with('status',$status_rename);

    }

    public function download_subfile($id)
    {
        $cari_file = Gallery::findOrFail($id);
        $id_folder = $cari_file->folder_id;

        $cari_folder = Folder::find($id_folder);
        $parent_id = $cari_folder->parent_id;

        $user = auth()->id();

        if ($cari_folder->id) {
            $tempat = 'data_user/' . $user . '/'.  $cari_folder->nama_folder .'/' . $cari_file->file;
        }

        if ($parent_id) {
            $folder_utama = Folder::find($parent_id);
            $tempat = 'data_user/' . $user. '/' . $folder_utama->nama_folder . '/' . $cari_folder->nama_folder . '/' . $cari_file->file; 

        }


        if ($cari_file->user_id != auth()->id()) {

            return back()->with('error','File tidak bisa didownload!');
        }


        return Storage::download($tempat);

    }

    public function pindah_rename($id)
    {
        $cari_folder = Folder::find($id);


        return view('rename_folder',compact('cari_folder'));
    }

    public function rename_f(Request $rename_folder, $id)
    {
        $ubah = Folder::find($id);

        $rename_folder->validate([
            'rename' => 'required'
        ]);

        $pemisah = str_replace(' ', '_', $rename_folder->input('rename'));

        $ubah->update([
            'nama_folder' => $pemisah
        ]);

        $pesan_berubah = 'File berhasil direname';


        return redirect('/folder')->with('status',$pesan_berubah);


    }

    public function pindah_renamesub($id)
    {
        $cari_file = Gallery::find($id);


        return view('rename_file',compact('cari_file'));
    }

    public function rename_sekarang(Request $ubah , $id)
    {
        $ubah_file = Gallery::find($id);


        $ubah->validate([
            'rename' => 'required'
        ]);

        $pemisah = str_replace(' ','_', $ubah->input('rename'));

        $ubah_file->update([
            'file' => $pemisah
        ]);
        $status = 'File berhasil direname';

        return view('isi',compact('status'));
    }

    public function pindah_subfolder($id)
    {
        $folder_izin = Folder::find($id);

        return view('izin_subfolder',compact('folder_izin'));
    }

    public function ubah_perizinan(Request $izinan,$id)
    {
        $izin_folder = Folder::find($id);

        $izin_folder->update([
            'permission' => $izinan->input('izin')
        ]);


        if ($izin_folder->permission == 1) {
            
            $pesan = 'public';
        }
        
        
        if ($izin_folder->permission == 0) {
            $pesan = 'private';
        }

        return back()->with('status','File '. $izin_folder->nama_folder . ' Menjadi ' . $pesan);
    }

    public function perizinan_subfile($id)
    {
        $cari_file = Gallery::find($id);


        return view('perizinan_subfile',compact('cari_file'));
    }


    public function izin_subfile(Request $izin_file, $id)
    {
        $file_perm = Gallery::find($id);

        $folder = Folder::find($file_perm->folder_id);

        $parent_id = $folder->parent_id;

    

        $file_perm->update([
            'izin' => $izin_file->input('izin')
        ]);

        if ($parent_id) {
            $folder_utama = Folder::find($parent_id);
            $tempat = storage_path('app/data_user/data_user/' . auth()->id() . '/' . $folder_utama->nama_folder . '/' . $folder->nama_folder . '/' . $file_perm->file );
    

        }else {
             
            $tempat = storage_path('app/data_user/data_user/' . auth()->id() . '/' . $folder->nama_folder . '/' . $file_perm->file);
       
        }

        $ambil_kontent = file_get_contents($tempat);    

      

        if ($izin_file->input('izin') == 0 || $izin_file->input('izin') != 0 || is_null($izin_file->izin)) {
            $pesan = 'Private';

            $enkripsi = encrypt($ambil_kontent);
            file_put_contents($tempat,$enkripsi);


        }

        if ($izin_file->input('izin') == 1 && $izin_file->izin == 1) {
            $pesan = 'Public';

            try {
                $dekripsi = decrypt($ambil_kontent);
                file_put_contents($tempat,$dekripsi);
            }catch (Exception $e) {

            }
        }


        return back()->with('status','File ' . $file_perm->file . ' Menjadi ' . $pesan);
    }

    public function rename_subfolder($id)
    {
        $cari_folder = Folder::find($id);


        return view('rename_subfolder',compact('cari_folder'));
    }

    public function renameFolder( Request $rename , $id)
    {
        $cari_folder = Folder::find($id);
        
        $rename->validate([
            'rename' => 'required'
        ]);

        $pemisah = str_replace(' ','_',$rename->input('rename'));

        $cari_folder->update([
            'nama_folder' => $pemisah
        ]);

        $berubah = 'File berhasil direname'; 

        return view('isi',compact('berubah'));
    }


    public function open_file($id)
    {
        $file = Gallery::find($id);

        $tempat = storage_path('app/data_user/data_user/' . auth()->id() . '/' . $file->file);
        $ambil_konten = file_get_contents($tempat);


        try {
            if ($file->izin == 0)  {
                $teks = base64_encode($ambil_konten);

                return view('lihat',compact('teks','file'));

            } 

        }catch (Exception $e) {
            

            }

       

        if (!file_exists($tempat)) {
            return back()->with('error', 'file tidak ada');
        }

        try {


            $parse = new \Smalot\PdfParser\Parser();
            $pdf = $parse->parseFile($tempat);

            $teks = $pdf->getText();

            return view('lihat',compact('teks','file'));

        } catch (\Exception $e ) {

            return back()->with('status',$e->getMessage());
        }


    }


    public function open_subfile($id)
    {
        $cari_file = Gallery::find($id);

        $folder = Folder::find($cari_file->folder_id);

        $parent_id = $folder->parent_id;

        if ($parent_id) {
            $folder_utama = Folder::find($parent_id);
            $tempat = storage_path('app/data_user/data_user/' . auth()->id() . '/' . $folder_utama->nama_folder . '/' . $folder->nama_folder . '/' . $cari_file->file );
            
        }else {
            
             $tempat = storage_path('app/data_user/data_user/' . auth()->id() . '/' . $folder->nama_folder . '/' . $cari_file->file);
        }

    

         $isi_konten = file_get_contents($tempat);
        try {
            if ($cari_file->izin == 0) {
                    $teks = base64_encode($isi_konten);

                    return view('lihat_subfile',compact('teks','cari_file'));
                }
            }catch (Exception $e) {
                return back()->with('status','gagal enkripsi file');
            }

            if (!file_exists($tempat)) {
                return back()->with('status','file tidak ada');
            }

            try {
                $parse = new \Smalot\PdfParser\Parser();
                $pdf = $parse->parseFile($tempat);
                $teks = $pdf->getText();

                return view('lihat_subfile',compact('teks','cari_file'));

            }catch (\Exception $e ) {

                return back()->with('status',$e->getMessage());
        }
    }


}
