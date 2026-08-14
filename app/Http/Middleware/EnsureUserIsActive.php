<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * - ACTIVE: passes through normally.
     * - PENDING: redirects to the account-pending informational page.
     * - SUSPENDED: logs the user out and redirects to login with an error.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        return match ($user->status) {
            UserStatus::ACTIVE => $next($request),

            UserStatus::PENDING => redirect()->route('auth.pending'),

            UserStatus::SUSPENDED => $this->handleSuspended($request),
        };
    }

    private function handleSuspended(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator sekolah.']);
    }
}
