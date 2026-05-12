<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * GET /api/v1/user
     * Returns authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved.',
            'data'    => $this->formatUser($user),
        ]);
    }

    /**
     * PATCH /api/v1/user
     * Update name or password.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name'     => 'sometimes|string|min:3|unique:users,name,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($request->filled('name')) {
            $user->update(['name' => $request->name]);
        }

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'data'    => $this->formatUser($user->fresh()),
        ]);
    }

    /**
     * GET /api/v1/user/storage
     * Returns storage usage breakdown.
     */
    public function storageUsage(Request $request): JsonResponse
    {
        $user = $request->user();

        $breakdown = ['Images' => 0, 'Videos' => 0, 'PDFs' => 0, 'Docs' => 0, 'Others' => 0];

        foreach ($user->galleries as $f) {
            $ext = strtolower(pathinfo($f->file, PATHINFO_EXTENSION));
            $cat = match (true) {
                in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']) => 'Images',
                in_array($ext, ['mp4', 'webm', 'mov', 'avi'])                => 'Videos',
                $ext === 'pdf'                                                => 'PDFs',
                in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt']) => 'Docs',
                default => 'Others',
            };
            $breakdown[$cat] += $f->ukuran;
        }

        $quota = (int) $user->storage_quota;
        $used  = (int) $user->storage_used;

        return response()->json([
            'success' => true,
            'message' => 'Storage usage retrieved.',
            'data'    => [
                'used_bytes'     => $used,
                'quota_bytes'    => $quota,
                'free_bytes'     => max(0, $quota - $used),
                'used_mb'        => round($used / 1024 / 1024, 2),
                'quota_mb'       => round($quota / 1024 / 1024, 2),
                'percentage'     => $quota > 0 ? round(($used / $quota) * 100, 1) : 0,
                'breakdown'      => $breakdown,
                'total_files'    => $user->galleries()->count(),
                'total_folders'  => $user->folders()->count(),
            ],
        ]);
    }

    /**
     * DELETE /api/v1/user
     * Permanently delete account and all associated data.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $path = 'users/' . $user->id;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->deleteDirectory($path);
        }

        // Also clean thumbnails
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->deleteDirectory($path);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted.',
        ]);
    }

    private function formatUser($user): array
    {
        return [
            'id'               => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'storage_used'     => (int) $user->storage_used,
            'storage_quota'    => (int) $user->storage_quota,
            'storage_used_mb'  => round($user->storage_used / 1024 / 1024, 2),
            'storage_quota_mb' => round($user->storage_quota / 1024 / 1024, 2),
            'created_at'       => $user->created_at?->toIso8601String(),
        ];
    }
}
