<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    /**
     * POST /api/v1/auth/google
     * Login or register using a Google OAuth access token.
     * 
     * Request body:
     * {
     *   "access_token": "google access token from frontend"
     * }
     */
    public function google(Request $request): JsonResponse
    {
        $request->validate([
            'access_token' => 'required_without:id_token|string',
            'id_token' => 'required_without:access_token|string',
        ]);

        try {
            $token = $request->input('access_token') ?: $request->input('id_token');
            $googleUser = Socialite::driver('google')->userFromToken($token);

            if (!$googleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token.',
                ], 401);
            }

            $user = app(GoogleAccountService::class)->findOrCreateFromGoogleUser($googleUser);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Google belum terdaftar di Master Dapodik.',
                ], 422);
            }

            // Revoke old mobile tokens
            $user->tokens()->where('name', 'mobile')->delete();

            // Create new token
            $token = $user->createToken('mobile')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google login successful.',
                'data' => [
                    'token' => $token,
                    'user' => $this->formatUser($user),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/auth/google/url
     * Returns the Google OAuth redirect URL for web-based flow.
     */
    public function googleRedirectUrl(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->redirectUrl(config('services.google.redirect'));

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
            ],
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'google_id' => $user->google_id,
            'role' => $user->role,
            'target_kelas' => $user->target_kelas,
            'target_jurusan' => $user->target_jurusan,
            'storage_used' => (int) $user->storage_used,
            'storage_quota' => (int) $user->storage_quota,
            'storage_used_mb' => round($user->storage_used / 1024 / 1024, 2),
            'storage_quota_mb' => round($user->storage_quota / 1024 / 1024, 2),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

}
