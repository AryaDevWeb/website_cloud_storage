<?php

use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::view('/', 'welcome');

// Informational page for PENDING accounts (accessible without full authentication)
Route::view('/account/pending', 'auth.account-pending')->name('auth.pending');

Route::middleware(['auth', 'signed', 'throttle:60,1'])
    ->get('/files/{media}/download', \App\Http\Controllers\FileDownloadController::class)
    ->name('files.download');

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
