<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorController extends Controller
{
    public function showChallenge()
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', compact('user'));
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->input('code'));

        if (!$valid) {
            return back()->with('error', 'Invalid verification code. Please check your authenticator app.');
        }

        $remember = session('2fa_remember', false);
        session()->forget(['2fa_user_id', '2fa_remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->forget(['pin_verified', 'pin_verified_at']);

        ActivityLogService::log($user->id, '2fa_verified', 'Two-factor authentication successful');

        return redirect()->route('pin.show');
    }

    public function showSetup()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        if (empty($user->two_factor_secret)) {
            $secretKey = $google2fa->generateSecretKey();
            session(['temp_2fa_secret' => $secretKey]);
        } else {
            $secretKey = $user->two_factor_secret;
        }

        $qrCodeInline = $google2fa->getQRCodeInline(
            config('app.name', 'Barakah Wallet'),
            $user->email,
            $secretKey
        );

        return view('profile.two-factor', compact('user', 'secretKey', 'qrCodeInline'));
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = Auth::user();
        $secretKey = session('temp_2fa_secret');

        if (!$secretKey) {
            return back()->with('error', 'Session expired. Please reload the page.');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secretKey, $request->input('code'));

        if (!$valid) {
            return back()->with('error', 'Invalid 2FA code. Please enter the current 6-digit code from your app.');
        }

        $user->update([
            'two_factor_secret' => $secretKey,
            'two_factor_enabled' => true,
        ]);

        session()->forget('temp_2fa_secret');
        ActivityLogService::log($user->id, '2fa_enabled', 'User enabled Two-Factor Authentication');

        return redirect()->route('profile')
            ->with('success', 'Two-Factor Authentication is now enabled for your account.');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->input('password'), $user->password)) {
            return back()->with('error', 'Incorrect account password.');
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);

        ActivityLogService::log($user->id, '2fa_disabled', 'User disabled Two-Factor Authentication');

        return redirect()->route('profile')
            ->with('info', 'Two-Factor Authentication has been disabled.');
    }
}

