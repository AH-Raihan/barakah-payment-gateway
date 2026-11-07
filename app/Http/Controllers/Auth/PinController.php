<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PinService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PinController extends Controller
{
    public function __construct(protected PinService $pinService) {}

    public function show()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($user->pin)) {
            return redirect()->route('pin.setup');
        }

        // If already verified and not expired, redirect to dashboard
        $pinVerified = session('pin_verified', false);
        $pinVerifiedAt = session('pin_verified_at');
        if ($pinVerified && $pinVerifiedAt) {
            $diff = \Carbon\Carbon::parse($pinVerifiedAt)->diffInMinutes(now());
            if ($diff < 30) {
                return redirect()->intended(route('dashboard'));
            }
        }

        $isLocked = $this->pinService->isLockedOut($user);
        $lockMinutes = $isLocked ? $this->pinService->lockoutRemainingMinutes($user) : 0;

        return view('auth.pin', compact('user', 'isLocked', 'lockMinutes'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:5',
        ]);

        $user = Auth::user();

        try {
            $this->pinService->verify($user, $request->input('pin'));

            $request->session()->put('pin_verified', true);
            $request->session()->put('pin_verified_at', now()->toISOString());

            ActivityLogService::log($user->id, 'pin_verified', 'User verified 5-digit PIN');

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'redirect' => route('dashboard')]);
            }

            return redirect()->intended(route('dashboard'));
        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function showSetup()
    {
        $user = Auth::user();
        if (!empty($user->pin)) {
            return redirect()->route('dashboard');
        }

        return view('auth.setup-pin');
    }

    public function setup(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:5|confirmed',
        ]);

        $user = Auth::user();

        try {
            $this->pinService->setPin($user, $request->input('pin'));

            $request->session()->put('pin_verified', true);
            $request->session()->put('pin_verified_at', now()->toISOString());

            ActivityLogService::log($user->id, 'pin_setup', 'User configured their initial 5-digit PIN');

            return redirect()->route('dashboard')
                ->with('success', 'Security PIN successfully configured! Welcome to Barakah Wallet.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function showChange()
    {
        return view('profile.change-pin');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|digits:5',
            'pin' => 'required|digits:5|confirmed|different:current_pin',
        ]);

        $user = Auth::user();

        try {
            $this->pinService->verify($user, $request->input('current_pin'));
            $this->pinService->setPin($user, $request->input('pin'));

            ActivityLogService::log($user->id, 'pin_changed', 'User updated their security PIN');

            return redirect()->route('profile')
                ->with('success', 'Your 5-digit PIN has been updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

