<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class OAuthController extends Controller
{
    /**
     * Redirect to Google for authentication.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback()
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();

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
                        'role' => $this->roleForGoogleEmail($googleUser->email, $user->role),
                        'storage_limit_bytes' => $user->storage_limit_bytes ?: 1 * 1024 * 1024 * 1024,
                    ]);
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'role' => $this->roleForGoogleEmail($googleUser->email),
                        'storage_limit_bytes' => 1 * 1024 * 1024 * 1024, // 1 GB default
                        'storage_used_bytes' => 0,
                    ]);
                }
            }

            // Login the user with "remember me" set to true
            Auth::login($user, true);

            // Redirect to dashboard
            return redirect('/dashboard/' . $user->id)->with('status', 'Login dengan Google berhasil!');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }

    private function roleForGoogleEmail(string $email, ?string $currentRole = null): string
    {
        $adminEmails = collect(explode(',', (string) env('LOCAL_ADMIN_EMAILS', '')))
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter()
            ->all();

        if (in_array(strtolower($email), $adminEmails, true)) {
            return 'admin';
        }

        return $currentRole ?: 'siswa';
    }
}
