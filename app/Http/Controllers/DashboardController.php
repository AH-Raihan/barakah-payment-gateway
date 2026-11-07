<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Fetch recent 5 transactions
        $recentTransactions = Transaction::where(function ($query) use ($wallet) {
                $query->where('sender_wallet_id', $wallet->id)
                      ->orWhere('receiver_wallet_id', $wallet->id);
            })
            ->with(['senderWallet.user', 'receiverWallet.user'])
            ->latest()
            ->take(6)
            ->get();

        // Calculate this month's stats
        $startOfMonth = now()->startOfMonth();

        $monthlySent = Transaction::where('sender_wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        $monthlyReceived = Transaction::where('receiver_wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        // Estimate zero fee savings (e.g. 1.85% typical MFS charge)
        $feesSaved = ($monthlySent + $monthlyReceived) * 0.0185;

        return view('dashboard.index', compact(
            'user',
            'wallet',
            'recentTransactions',
            'monthlySent',
            'monthlyReceived',
            'feesSaved'
        ));
    }
}

