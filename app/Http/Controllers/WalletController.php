<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PinService;
use App\Services\WalletService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected PinService $pinService
    ) {}

    public function sendView()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Recent transfer recipients for quick selection
        $recentRecipientIds = \App\Models\Transaction::where('sender_wallet_id', $wallet->id)
            ->whereNotNull('receiver_wallet_id')
            ->latest()
            ->take(8)
            ->with('receiverWallet.user')
            ->get()
            ->pluck('receiverWallet.user')
            ->filter()
            ->unique('id')
            ->values();

        return view('wallet.send', compact('user', 'wallet', 'recentRecipientIds'));
    }

    public function lookupRecipient(Request $request)
    {
        $query = trim($request->input('query', ''));
        if (strlen($query) < 3) {
            return response()->json(['found' => false]);
        }

        $recipient = User::where(function ($q) use ($query) {
                $q->where('phone', $query)
                  ->orWhere('email', $query)
                  ->orWhere('account_id', $query);
            })
            ->where('id', '!=', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$recipient || !$recipient->wallet) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'name' => $recipient->name,
            'phone' => $recipient->phone,
            'email' => $recipient->email,
            'account_id' => $recipient->account_id,
            'role' => $recipient->role,
        ]);
    }

    public function sendMoney(Request $request)
    {
        $request->validate([
            'recipient' => 'required|string',
            'amount' => 'required|numeric|min:1|max:500000',
            'pin' => 'required|string|size:5',
            'description' => 'nullable|string|max:200',
            'idempotency_key' => 'required|string|max:64',
        ]);

        $sender = Auth::user();
        $senderWallet = $sender->wallet;

        // 1. Verify 5-digit PIN
        try {
            $this->pinService->verify($sender, $request->input('pin'));
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // 2. Find recipient
        $recipientIdentifier = trim($request->input('recipient'));
        $recipient = User::where(function ($q) use ($recipientIdentifier) {
                $q->where('phone', $recipientIdentifier)
                  ->orWhere('email', $recipientIdentifier)
                  ->orWhere('account_id', $recipientIdentifier);
            })
            ->first();

        if (!$recipient || !$recipient->wallet) {
            return back()->withInput()->with('error', 'Recipient account not found. Please check phone number or Account ID.');
        }

        if ($recipient->id === $sender->id) {
            return back()->withInput()->with('error', 'You cannot send money to your own wallet.');
        }

        if ($recipient->status !== 'active') {
            return back()->withInput()->with('error', 'Recipient account is not active.');
        }

        $amount = (float) $request->input('amount');
        $idempotencyKey = $request->input('idempotency_key');
        $description = $request->input('description') ?? 'Institutional Wallet Transfer';

        // 3. Process transfer via WalletService (atomic, locking, safe)
        try {
            $transaction = $this->walletService->transfer(
                $senderWallet,
                $recipient->wallet,
                $amount,
                'send_money',
                $description,
                $idempotencyKey,
                $request->ip(),
                $request->userAgent()
            );

            ActivityLogService::log(
                $sender->id,
                'send_money',
                "Sent " . number_format($amount, 2) . " BDT to {$recipient->name} ({$recipient->account_id})",
                ['transaction_id' => $transaction->id, 'reference' => $transaction->reference]
            );

            return redirect()->route('transactions.show', $transaction->uuid)
                ->with('success', 'Money transferred successfully with 0 charge!');
        } catch (InsufficientBalanceException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function cashOutView()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Available agents for cashout
        $agents = User::where('role', 'agent')
            ->where('status', 'active')
            ->take(5)
            ->get();

        return view('wallet.cashout', compact('user', 'wallet', 'agents'));
    }

    public function cashOut(Request $request)
    {
        $request->validate([
            'agent_account' => 'required|string',
            'amount' => 'required|numeric|min:1|max:500000',
            'pin' => 'required|string|size:5',
            'idempotency_key' => 'required|string|max:64',
        ]);

        $user = Auth::user();
        $userWallet = $user->wallet;

        // 1. Verify PIN
        try {
            $this->pinService->verify($user, $request->input('pin'));
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // 2. Find Agent
        $agentIdentifier = trim($request->input('agent_account'));
        $agent = User::where(function ($q) use ($agentIdentifier) {
                $q->where('phone', $agentIdentifier)
                  ->orWhere('account_id', $agentIdentifier);
            })
            ->where('role', 'agent')
            ->first();

        if (!$agent || !$agent->wallet) {
            return back()->withInput()->with('error', 'Authorized agent not found. Please enter a valid Agent Account ID or Agent Phone.');
        }

        $amount = (float) $request->input('amount');

        // 3. Process cash out
        try {
            $transaction = $this->walletService->cashOut(
                $userWallet,
                $agent->wallet,
                $amount,
                "Cash Out via Agent {$agent->name}",
                $request->input('idempotency_key'),
                $request->ip(),
                $request->userAgent()
            );

            ActivityLogService::log(
                $user->id,
                'cash_out',
                "Cash out of " . number_format($amount, 2) . " BDT through Agent {$agent->name}",
                ['transaction_id' => $transaction->id]
            );

            return redirect()->route('transactions.show', $transaction->uuid)
                ->with('success', 'Cash out requested successfully! Collect cash from the counter/agent.');
        } catch (InsufficientBalanceException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Cash out failed: ' . $e->getMessage());
        }
    }

    public function qrView()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Generate real scannable QR code of the user's mobile number
        $qrData = !empty($user->phone) ? $user->phone : $user->account_id;

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(240, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrSvg = $writer->writeString($qrData);

        return view('wallet.qr', compact('user', 'wallet', 'qrSvg', 'qrData'));
    }
}

