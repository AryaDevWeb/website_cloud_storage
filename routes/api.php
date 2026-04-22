<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Mobile (Flutter) v1
|--------------------------------------------------------------------------
| All routes use the /api/v1/ prefix (configured in bootstrap/app.php).
| Authentication: Laravel Sanctum (Bearer token).
*/

Route::prefix('v1')->group(function () {

    // ── Public routes (no token required) ──────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/login',    [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/google',   [App\Http\Controllers\Api\OAuthController::class, 'google']);
    });

    // ── Protected routes (requires valid Sanctum token) ─────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',      [AuthController::class, 'me']);

        // ── Files ──────────────────────────────────────────────────────
        Route::get('/files',                    [FileController::class, 'index']);       // List (paginated + filter)
        Route::post('/files',                   [FileController::class, 'store']);       // Upload
        Route::get('/files/recent',             [FileController::class, 'recent']);      // Recent 30 files
        Route::get('/files/starred',            [FileController::class, 'starred']);     // Starred files
        Route::get('/files/shared',             [FileController::class, 'shared']);      // Public (shared) files
        Route::get('/files/trash',              [FileController::class, 'trash']);       // Soft-deleted files

        Route::get('/files/{id}',               [FileController::class, 'show']);        // File metadata
        Route::patch('/files/{id}',             [FileController::class, 'update']);      // Rename
        Route::delete('/files/{id}',            [FileController::class, 'destroy']);     // Soft delete
        Route::get('/files/{id}/download',      [FileController::class, 'download']);    // Download stream

        Route::post('/files/{id}/star',         [FileController::class, 'star']);        // Toggle star
        Route::post('/files/{id}/share',        [FileController::class, 'share']);       // Get share URL
        Route::post('/files/{id}/move',         [FileController::class, 'move']);        // Move to folder
        Route::post('/files/{id}/restore',      [FileController::class, 'restore']);     // Restore from trash
        Route::delete('/files/{id}/permanent',  [FileController::class, 'forceDelete']); // Permanent delete
        Route::patch('/files/{id}/permission',  [FileController::class, 'permission']);  // Change permission

        // ── Folders ────────────────────────────────────────────────────
        Route::get('/folders',           [FolderController::class, 'index']);    // Root folders
        Route::post('/folders',          [FolderController::class, 'store']);    // Create folder
        Route::get('/folders/tree',      [FolderController::class, 'tree']);     // Full folder tree
        Route::get('/folders/{id}',      [FolderController::class, 'show']);     // Folder contents
        Route::patch('/folders/{id}',    [FolderController::class, 'update']);   // Rename folder
        Route::delete('/folders/{id}',   [FolderController::class, 'destroy']);  // Soft delete folder
        Route::post('/folders/{id}/restore',   [FolderController::class, 'restore']);  // Restore
        Route::delete('/folders/{id}/permanent', [FolderController::class, 'forceDelete']); // Force delete

        // ── User / Profile ─────────────────────────────────────────────
        Route::get('/user',             [UserController::class, 'show']);         // User profile
        Route::patch('/user',           [UserController::class, 'update']);       // Update profile
        Route::get('/user/storage',     [UserController::class, 'storageUsage']); // Storage stats
        Route::delete('/user',          [UserController::class, 'destroy']);      // Delete account
    });
});
