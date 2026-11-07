<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWalletActive
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has an active wallet before
     * proceeding with financial operations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $wallet = $user->wallet;

        // Check if wallet exists
        if (!$wallet) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No wallet found for your account. Please contact support.',
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'No wallet found for your account. Please contact support.');
        }

        // Check wallet status
        if ($wallet->status === 'frozen') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your wallet has been frozen. Please contact support for assistance.',
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Your wallet has been frozen. Please contact support for assistance.');
        }

        if ($wallet->status === 'closed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your wallet has been closed. Please contact support for assistance.',
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Your wallet has been closed. Please contact support for assistance.');
        }

        if ($wallet->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your wallet is not active. Current status: ' . $wallet->status,
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Your wallet is not active. Current status: ' . $wallet->status);
        }

        return $next($request);
    }
}
