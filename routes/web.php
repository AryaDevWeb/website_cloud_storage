<?php

use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::view('/', 'welcome');

// Informational page for PENDING accounts (accessible without full authentication)
Route::view('/account/pending', 'auth.account-pending')->name('auth.pending');

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
