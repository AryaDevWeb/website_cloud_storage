<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Register;
use App\Http\Controllers\Login;
use App\Http\Controllers\Beranda;




Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/masuk', [Login::class, 'login']);

Route::get('/register', [Register::class, 'tampil']);
Route::post('/register', [Register::class, 'register']);

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard/' . auth()->id());
    }
    return view('register');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard/{id}', [Beranda::class, 'dashboard'])->name('dashboard');

    // Beranda & Core Actions
    Route::get('/beranda/{id}', [Beranda::class, 'akun'])->name('beranda');
    Route::post('/logout', [Beranda::class, 'logout']);
    Route::get('/pencarian', [Beranda::class, 'pencarian']);

    // Folders
    Route::post('/folder', [Beranda::class, 'folder']);
    Route::get('/folder_open/{id}', [Beranda::class, 'new_folder'])->name('folder.show');
    Route::get('/hapus_folder/{id}', [Beranda::class, 'hapus_folder']);
    Route::get('/rename_folder/{id}', [Beranda::class, 'pindah_rename']);
    Route::post('/rename_f/{id}', [Beranda::class, 'rename_f']);
    Route::get('/permissions_folder/{id}', [Beranda::class, 'masuk_izin']);
    Route::post('/folder_permission/{id}', [Beranda::class, 'folder_permission']);

    // Files
    Route::post('/upload', [Beranda::class, 'upload']);
    Route::get('/hapus_file/{id}', [Beranda::class, 'hapus_file']);
    Route::get('/rename_file/{id}', [Beranda::class, 'pindah']);
    Route::post('/rename/{id}', [Beranda::class, 'rename']);
    Route::get('/permissions_file/{id}', [Beranda::class, 'izin_file']);
    Route::post('/ubah_perizinan/{id}', [Beranda::class, 'ubah_izin']);
    Route::get('/open_file/{id}', [Beranda::class, 'open_file']);

    // Account
    Route::get('/lihat_akun/{id}', [Beranda::class, 'lihat_akun']);
    Route::get('/hapus_akun/{id}', [Beranda::class, 'hapus_akun']);
});

Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    Route::get('/download/{id}', [Beranda::class, 'download_file']);
});

Route::get('/modern-dashboard', function () {
    return view('modern_dashboard');
})->middleware(['auth']);

Route::get('/cloud', function () {
    return view('cloud');
})->middleware(['auth']);





?>