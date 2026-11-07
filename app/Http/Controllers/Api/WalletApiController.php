<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletApiController extends Controller
{
    public function balance(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not initialized.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'account_id' => $user->account_id,
                    'phone' => $user->phone,
                ],
                'wallet' => [
                    'balance' => (float) $wallet->balance,
                    'currency' => $wallet->currency,
                    'status' => $wallet->status,
                ],
            ],
        ]);
    }
}

