<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $recentActivities = \App\Models\ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        return view('profile.index', compact('user', 'wallet', 'recentActivities'));
    }

    public function security()
    {
        $user = Auth::user();
        return view('profile.security', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
        ]);

        $user->update($validated);
        ActivityLogService::log($user->id, 'profile_updated', 'User updated contact profile');

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->with('error', 'Your current password does not match.');
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        ActivityLogService::log($user->id, 'password_changed', 'User updated account password');

        return back()->with('success', 'Password successfully changed.');
    }

    public function about()
    {
        return view('profile.about');
    }
}

