<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($field, $login)
            ->orWhere('account_id', $login)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['These credentials do not match our records.'],
            ]);
        }

        if ($user->status === 'suspended') {
            throw ValidationException::withMessages([
                'login' => ['Your account has been suspended. Please contact institutional administration.'],
            ]);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled && $user->two_factor_secret) {
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->boolean('remember'));
            return redirect()->route('2fa.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Clear previous PIN verified status on fresh login
        $request->session()->forget(['pin_verified', 'pin_verified_at']);

        ActivityLogService::log($user->id, 'login', 'User logged in successfully');

        // Check if PIN has been set
        if (empty($user->pin)) {
            return redirect()->route('pin.setup');
        }

        // Redirect to PIN entry
        return redirect()->route('pin.show');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        if ($userId) {
            ActivityLogService::log($userId, 'logout', 'User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }
}

