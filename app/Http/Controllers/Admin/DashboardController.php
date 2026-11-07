<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminDeposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total stats
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $totalAgents = User::where('role', 'agent')->count();

        // Total circulating institutional balance
        $totalSystemBalance = Wallet::sum('balance');

        // Total Admin deposits ever added
        $totalDepositedAmount = AdminDeposit::sum('amount');
        $totalDepositsCount = AdminDeposit::count();

        // Transaction stats
        $totalTxCount = Transaction::where('status', 'completed')->count();
        $totalTxVolume = Transaction::where('status', 'completed')->sum('amount');

        // Today's stats
        $todayStart = now()->startOfDay();
        $todayTxCount = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $todayStart)
            ->count();
        $todayTxVolume = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $todayStart)
            ->sum('amount');

        // Recent transactions
        $recentTransactions = Transaction::with(['senderWallet.user', 'receiverWallet.user'])
            ->latest()
            ->take(8)
            ->get();

        // Recent admin deposits
        $recentDeposits = AdminDeposit::with(['admin', 'wallet.user'])
            ->latest()
            ->take(5)
            ->get();

        // 7-day transaction chart data
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $volume = Transaction::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('amount');
            $count = Transaction::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->count();

            return [
                'day' => now()->subDays($daysAgo)->format('D, M j'),
                'volume' => (float) $volume,
                'count' => $count,
            ];
        });

        $registrationEnabled = \App\Models\SystemSetting::isRegistrationEnabled();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'totalAgents',
            'totalSystemBalance',
            'totalDepositedAmount',
            'totalDepositsCount',
            'totalTxCount',
            'totalTxVolume',
            'todayTxCount',
            'todayTxVolume',
            'recentTransactions',
            'recentDeposits',
            'days',
            'registrationEnabled'
        ));
    }
}

