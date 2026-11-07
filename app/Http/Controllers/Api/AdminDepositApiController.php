<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminDeposit;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDepositApiController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function deposit(Request $request)
    {
        $request->validate([
            'user' => 'required|string', // can be user_id, email, phone, or account_id
            'amount' => 'required|numeric|min:1|max:100000000',
            'note' => 'nullable|string|max:500',
        ]);

        $admin = Auth::user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin credentials required.',
            ], 403);
        }

        $identifier = $request->input('user');
        $targetUser = User::where('id', $identifier)
            ->orWhere('email', $identifier)
            ->orWhere('phone', $identifier)
            ->orWhere('account_id', $identifier)
            ->with('wallet')
            ->first();

        if (!$targetUser || !$targetUser->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Target user or wallet not found.',
            ], 404);
        }

        $amount = (float) $request->input('amount');
        $note = $request->input('note') ?? 'API Balance Credit';

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
                'api_deposit_added',
                "Admin added {$amount} BDT to {$targetUser->name} via API"
            );

            return response()->json([
                'success' => true,
                'message' => 'Balance successfully added to user account.',
                'data' => [
                    'recipient' => [
                        'name' => $targetUser->name,
                        'account_id' => $targetUser->account_id,
                        'phone' => $targetUser->phone,
                    ],
                    'amount_added' => $amount,
                    'new_balance' => (float) $targetUser->wallet->fresh()->balance,
                    'reference' => $transaction->reference,
                    'note' => $note,
                    'deposited_at' => $transaction->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $admin = Auth::user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $deposits = AdminDeposit::with(['admin:id,name,email', 'wallet.user:id,name,phone,account_id'])
            ->latest()
            ->paginate($request->input('per_page', 20));

        $totalAdded = AdminDeposit::sum('amount');
        $count = AdminDeposit::count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_amount_added' => (float) $totalAdded,
                'total_deposits_count' => $count,
                'currency' => 'BDT',
            ],
            'data' => $deposits,
        ]);
    }
}

