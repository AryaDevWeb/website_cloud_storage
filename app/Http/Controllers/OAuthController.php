<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Services\GoogleAccountService;

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

            $user = app(GoogleAccountService::class)->findOrCreateFromGoogleUser($googleUser);
            if (!$user) {
                return redirect('/login')->with('error', 'Akun Google belum terdaftar di Master Dapodik.');
            }

            // Login the user with "remember me" set to true
            Auth::login($user, true);

            // Redirect to dashboard
            return redirect('/dashboard/' . $user->id)->with('status', 'Login dengan Google berhasil!');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }

}
