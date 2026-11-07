<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminDeposit;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index(Request $request)
    {
        $search = $request->query('search');
        $adminId = $request->query('admin_id');

        $query = AdminDeposit::with(['admin', 'wallet.user', 'transaction']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('wallet.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        $deposits = $query->latest()->paginate(15)->withQueryString();

        $totalDepositedSum = AdminDeposit::sum('amount');
        $totalDepositedCount = AdminDeposit::count();

        return view('admin.deposits.index', compact('deposits', 'totalDepositedSum', 'totalDepositedCount', 'search'));
    }

    public function create(Request $request)
    {
        $selectedUserId = $request->query('user_id');
        $selectedUser = $selectedUserId ? User::with('wallet')->find($selectedUserId) : null;

        return view('admin.deposits.create', compact('selectedUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1|max:100000000',
            'note' => 'nullable|string|max:500',
        ]);

        $admin = Auth::user();
        $targetUser = User::with('wallet')->findOrFail($request->input('user_id'));

        if (!$targetUser->wallet) {
            return back()->withInput()->with('error', 'User has no active wallet.');
        }

        $amount = (float) $request->input('amount');
        $note = $request->input('note') ?? 'Institutional Fund Injection / Admin Deposit';

        try {
            $transaction = $this->walletService->deposit(
                $targetUser->wallet,
                $amount,
                $note,
                $admin->id,
                $request->ip()
            );

            ActivityLogService::log(
                $admin->id,
                'admin_deposit_added',
                "Admin added " . number_format($amount, 2) . " BDT to {$targetUser->name} ({$targetUser->account_id})",
                ['amount' => $amount, 'target_user_id' => $targetUser->id, 'reference' => $transaction->reference]
            );

            return redirect()->route('admin.deposits.index')
                ->with('success', "Successfully added " . number_format($amount, 2) . " BDT to {$targetUser->name}'s wallet. Reference: {$transaction->reference}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to add balance: ' . $e->getMessage());
        }
    }
}

