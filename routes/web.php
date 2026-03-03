<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Register;
use App\Http\Controllers\Login;
use App\Http\Controllers\Beranda;




Route::post('/masuk',[Login::class, 'login']);


Route::get('/login',function () {
    return view('login');

});

Route::post('/register',[Register::class, 'register']);
Route::get('/register',[Register::class,'tampil']);

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/beranda/' . auth()->id());
    }

    return view('register');
 }
);


Route::middleware(['auth','verified'])->group( function () {

    Route::get('/beranda/{id}',[Beranda::class,'akun']);
    Route::post('/upload',[Beranda::class,'upload']);

    Route::get('/hapus/{id}',[Beranda::class,'hapus']);

    Route::post('/folder',[Beranda::class,'folder']);

    Route::get('/folder_open/{id}', [Beranda::class, 'new_folder']);

    Route::post('/logout', [Beranda::class, 'logout']);


    Route::get('/pencarian', [Beranda::class,'pencarian']);

    Route::get('/hapus_folder/{id}', [Beranda::class, 'hapus_folder']);

    Route::get('/hapus_subfolder/{id}', [Beranda::class, 'hapus_subfolder']);

    Route::post('/upload_subfolder', [Beranda::class,'upload_subfolder']);

    Route::get('/hapus_file/{id}',[Beranda::class, 'hapus_file']);

    Route::post('/izin_file/{id}', [Beranda::class,'izin_file']);
    Route::get('/izin_file/{id}',[Beranda::class,'izin_file']);

    Route::post('/ubah_perizinan/{id}',[Beranda::class,'ubah_izin']);
    Route::get('/ubah_perizinan/{id}',[Beranda::class,'ubah_izin']);

    Route::post('/masuk_izin/{id}',[Beranda::class,'masuk_izin']);
    Route::get('/masuk_izin/{id}',[Beranda::class,'masuk_izin']);

    Route::post('/folder_permission/{id}', [Beranda::class,'folder_permission']);
    Route::get('/folder_permission/{id}', [Beranda::class,'folder_permission']);

    Route::get('/lihat_akun/{id}', [Beranda::class,'lihat_akun' ]);
    Route::get('/hapus_akun/{id}', [Beranda::class,'hapus_akun']);

    Route::get('/rename_file/{id}', [Beranda::class, 'pindah']);
    Route::post('/rename/{id}', [Beranda::class,'rename' ]);
    Route::get('/rename/{id}', [Beranda::class, 'rename']);

    Route::get('/rename_folder/{id}', [Beranda::class, 'pindah_rename']);
    Route::post('/rename_f/{id}', [Beranda::class, 'rename_f']);
    Route::get('/rename_f/{id}', [Beranda::class, 'rename_f']);

    Route::get('/rename_subfile/{id}',[Beranda::class,'pindah_renamesub' ]);
    Route::post('/rename_sekarang/{id}', [Beranda::class, 'rename_sekarang']);
    Route::get('/rename_sekarang/{id}', [Beranda::class, 'rename_sekarang']);

    Route::get('/pindah_perizinan/{id}', [Beranda::class,'pindah_subfolder' ]);
    Route::post('/pindah_perizinan/{id}', [Beranda::class,'pindah_subfolder' ]);


    Route::post('/perizinan_subfolder/{id}',[Beranda::class, 'ubah_perizinan']);


    Route::get('/perizinan_subfile/{id}',[Beranda::class, 'perizinan_subfile']);
    Route::post('/perizinan_subfile/{id}',[Beranda::class, 'perizinan_subfile']);

    Route::post('/izin_subfile/{id}', [Beranda::class,'izin_subfile' ]);

    Route::get('/rename_subfolder/{id}', [Beranda::class, 'rename_subfolder']);

    Route::post('/subfolder_rename/{id}', [Beranda::class, 'renameFolder']);
    Route::get('/subfolder_rename/{id}', [Beranda::class, 'renameFolder']);

    Route::get('/open_file/{id}', [Beranda::class,'open_file' ]);

    
}

);


Route::middleware(['auth','throttle:10,1'])->group( function () {
    Route::get('/download/{id}', [Beranda::class, 'download_file' ]);
    Route::get('/download_subfile/{id}',[Beranda::class,'download_subfile' ]);
}

);




?>