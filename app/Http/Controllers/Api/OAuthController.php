<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OAuthController extends Controller
{
    /**
     * POST /api/v1/auth/google
     * Login or register using Google ID token.
     * 
     * Request body:
     * {
     *   "id_token": "google id token from frontend",
     *   "access_token": "optional google access token"
     * }
     */
    public function google(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // Verify the Google token
            $googleUser = Socialite::driver('google')->userFromToken($request->id_token);

            if (!$googleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token.',
                ], 401);
            }

            // Check if user exists by google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if (!$user) {
                // Check if user exists by email
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // Update existing user with google_id
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                    ]);
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'storage_quota' => 5 * 1024 * 1024 * 1024, // 5 GB default
                        'storage_used' => 0,
                    ]);
                }
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
            'storage_used' => (int) $user->storage_used,
            'storage_quota' => (int) $user->storage_quota,
            'storage_used_mb' => round($user->storage_used / 1024 / 1024, 2),
            'storage_quota_mb' => round($user->storage_quota / 1024 / 1024, 2),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}