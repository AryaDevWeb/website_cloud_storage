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

            // Login the user
            Auth::login($user);

            // Redirect to dashboard
            return redirect('/dashboard/' . $user->id)->with('status', 'Login dengan Google berhasil!');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }
}