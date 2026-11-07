<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPin
{
    /**
     * PIN verification expiry in minutes.
     */
    const PIN_EXPIRY_MINUTES = 30;

    /**
     * Handle an incoming request.
     *
     * Checks if the user has verified their PIN in the current session.
     * PIN verification expires after 30 minutes of inactivity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for guests (not authenticated)
        if (!$request->user()) {
            return $next($request);
        }

        $pinVerified = $request->session()->get('pin_verified', false);
        $pinVerifiedAt = $request->session()->get('pin_verified_at');

        // Check if PIN was verified and hasn't expired
        if (!$pinVerified || !$pinVerifiedAt) {
            return $this->redirectToPin($request);
        }

        // Check if PIN verification has expired (30 minutes)
        $verifiedAt = \Carbon\Carbon::parse($pinVerifiedAt);
        if ($verifiedAt->diffInMinutes(now()) >= self::PIN_EXPIRY_MINUTES) {
            // Clear expired PIN session
            $request->session()->forget(['pin_verified', 'pin_verified_at']);
            return $this->redirectToPin($request);
        }

        // Refresh the verification timestamp on activity (sliding expiry)
        $request->session()->put('pin_verified_at', now()->toISOString());

        return $next($request);
    }

    /**
     * Redirect the user to the PIN verification screen.
     */
    private function redirectToPin(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'PIN verification required.',
                'pin_required' => true,
            ], 403);
        }

        return redirect()->route('pin.show')
            ->with('info', 'Please verify your PIN to continue.');
    }
}
