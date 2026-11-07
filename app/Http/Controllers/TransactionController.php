<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $type = $request->query('type');
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = Transaction::where(function ($q) use ($wallet) {
                $q->where('sender_wallet_id', $wallet->id)
                  ->orWhere('receiver_wallet_id', $wallet->id);
            })
            ->with(['senderWallet.user', 'receiverWallet.user']);

        if ($type && $type !== 'all') {
            if ($type === 'sent') {
                $query->where('sender_wallet_id', $wallet->id);
            } elseif ($type === 'received') {
                $query->where('receiver_wallet_id', $wallet->id);
            } else {
                $query->where('type', $type);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions', 'wallet', 'type', 'search', 'dateFrom', 'dateTo'));
    }

    public function show($uuid)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $transaction = Transaction::where('uuid', $uuid)
            ->where(function ($q) use ($wallet, $user) {
                // If user is admin, allow viewing any transaction
                if ($user->isAdmin()) {
                    return;
                }
                $q->where('sender_wallet_id', $wallet->id)
                  ->orWhere('receiver_wallet_id', $wallet->id);
            })
            ->with(['senderWallet.user', 'receiverWallet.user', 'adminDeposit.admin'])
            ->firstOrFail();

        return view('transactions.show', compact('transaction', 'wallet', 'user'));
    }
}

