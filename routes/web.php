<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::redirect('/', '/login');

// Informational page for PENDING accounts (accessible without full authentication)
Route::view('/account/pending', 'auth.account-pending')->name('auth.pending');

Route::middleware(['auth', 'signed', 'throttle:60,1'])
    ->get('/files/{media}/download', \App\Http\Controllers\FileDownloadController::class)
    ->name('files.download');

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Volt::route('storage/{section?}', 'storage.index')
        ->whereIn('section', ['files', 'shared', 'sent', 'trash', 'audit'])
        ->name('storage.index');
});

require __DIR__.'/auth.php';
