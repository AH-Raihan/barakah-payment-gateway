<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (!SystemSetting::isRegistrationEnabled()) {
            return redirect()->route('login')->with('error', __('Public registration is currently disabled by institution administrators. Please contact campus administration to obtain an account.'));
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (!SystemSetting::isRegistrationEnabled()) {
            return redirect()->route('login')->with('error', __('Public registration is currently disabled by institution administrators.'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
                'status' => 'active',
            ]);

            // Create primary wallet
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0.0000,
                'currency' => 'BDT',
                'status' => 'active',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLogService::log($user->id, 'register', 'New institutional wallet user registered');

        return redirect()->route('pin.setup')
            ->with('success', 'Account created! Please set your 5-digit security PIN.');
    }
}

