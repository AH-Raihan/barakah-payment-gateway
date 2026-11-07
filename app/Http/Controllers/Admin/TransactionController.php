<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Transaction::with(['senderWallet.user', 'receiverWallet.user', 'adminDeposit.admin']);

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('senderWallet.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('receiverWallet.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_volume' => Transaction::where('status', 'completed')->sum('amount'),
            'total_count' => Transaction::count(),
            'transfers_volume' => Transaction::where('type', 'send_money')->where('status', 'completed')->sum('amount'),
            'deposits_volume' => Transaction::where('type', 'deposit')->where('status', 'completed')->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats', 'type', 'status', 'search'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['senderWallet.user', 'receiverWallet.user', 'logs.performer', 'adminDeposit.admin', 'paymentRequest']);
        return view('admin.transactions.show', compact('transaction'));
    }
}

