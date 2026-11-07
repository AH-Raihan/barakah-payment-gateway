<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'device_type' => 'nullable|string|in:web,android,ios',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $request->input('token'),
            ],
            [
                'device_type' => $request->input('device_type', 'web'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registration token saved successfully.',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();
        if ($user) {
            DeviceToken::where('user_id', $user->id)
                ->where('token', $request->input('token'))
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Device registration token removed successfully.',
        ]);
    }
}

