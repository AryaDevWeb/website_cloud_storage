<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Beranda;


Route::get('/login', function () {
    if (auth()->check()) {
        return redirect('/dashboard/' . auth()->id());
    }
    return view('login');
})->name('login');

// Google OAuth Routes
Route::get('/auth/google', [App\Http\Controllers\OAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [App\Http\Controllers\OAuthController::class, 'handleGoogleCallback']);

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard/' . auth()->id());
    }
    return view('register');
});

// Localhost phase: authentication is Google-only. Keep password controllers
// available in code for the future username/password flow, but do not expose them.
Route::post('/register', fn () => abort(404));
Route::post('/login', fn () => abort(404));

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard/{id}', [Beranda::class, 'dashboard'])->name('dashboard');

    // Admin
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::patch('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');

    // Beranda & Core Actions
    Route::get('/beranda/{id}', [Beranda::class, 'akun'])->name('beranda');
    Route::post('/logout', [Beranda::class, 'logout']);
    Route::get('/pencarian', [Beranda::class, 'pencarian']);

    // ── New section pages ─────────────────────────────────────
    Route::get('/recent', fn() => view('recent'))->name('recent');
    Route::get('/starred', fn() => view('starred'))->name('starred');
    Route::get('/shared', fn() => view('shared'))->name('shared');
    Route::get('/trash', fn() => view('trash'))->name('trash');

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
    Route::get('/open_file_img/{id}', [Beranda::class, 'streamFile']);
    Route::get('/open_file_stream/{id}', [Beranda::class, 'streamFile']);


    // ── JSON API endpoints ─────────────────────────────────────
    Route::get('/api/files', [Beranda::class, 'getFilesJson']);
    Route::get('/api/files/recent', [Beranda::class, 'recentFiles']);
    Route::get('/api/files/starred', [Beranda::class, 'starredFiles']);
    Route::get('/api/files/shared', [Beranda::class, 'sharedFiles']);
    Route::get('/api/files/trash', [Beranda::class, 'trashedFiles']);
    Route::get('/api/files/{id}', [Beranda::class, 'getFileJson']);
    Route::get('/api/folders/tree', [Beranda::class, 'folderTree']);

    Route::post('/api/upload', [Beranda::class, 'uploadAjax']);
    Route::post('/api/folder', [Beranda::class, 'folderAjax']);

    Route::patch('/api/file/{id}', [Beranda::class, 'renameAjax']);
    Route::delete('/api/file/{id}', [Beranda::class, 'deleteAjax']);
    Route::post('/api/file/{id}/share', [Beranda::class, 'shareAjax']);
    Route::post('/api/file/{id}/star', [Beranda::class, 'starAjax']);
    Route::post('/api/file/{id}/restore', [Beranda::class, 'restoreAjax']);
    Route::delete('/api/file/{id}/permanent', [Beranda::class, 'forceDeleteAjax']);
    Route::patch('/api/file/{id}/permission', [Beranda::class, 'permissionAjax']);
    Route::post('/api/file/{id}/move', [Beranda::class, 'moveAjax']);

    Route::get('/api/notifications', [Beranda::class, 'notificationsAjax']);

    Route::get('/halaman_recent/{id}', [Beranda::class, 'recent']);

    // Account
    Route::get('/lihat_akun/{id}', [Beranda::class, 'lihat_akun']);
    Route::get('/hapus_akun/{id}', [Beranda::class, 'hapus_akun']);

    Route::get('/tempat_sampah', [Beranda::class, 'pindah_sampah']);
});

Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    Route::get('/download/{id}', [Beranda::class, 'download_file']);
});
