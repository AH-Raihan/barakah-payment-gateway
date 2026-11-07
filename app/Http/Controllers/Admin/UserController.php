<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ActivityLogService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index(Request $request)
    {
        $role = $request->query('role');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = User::with('wallet');

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'role', 'status', 'search'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'role' => 'required|in:user,agent,admin,super_admin',
            'password' => 'required|string|min:6',
            'pin' => 'required|digits:5',
            'initial_balance' => 'nullable|numeric|min:0',
        ]);

        $admin = Auth::user();

        $user = DB::transaction(function () use ($validated, $admin) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'pin' => Hash::make($validated['pin']),
                'role' => $validated['role'],
                'status' => 'active',
            ]);

            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0.0000,
                'currency' => 'BDT',
                'status' => 'active',
            ]);

            $initial = (float) ($validated['initial_balance'] ?? 0);
            if ($initial > 0) {
                $this->walletService->deposit(
                    $wallet,
                    $initial,
                    'Initial balance upon account creation',
                    $admin->id
                );
            }

            return $user;
        });

        ActivityLogService::log($admin->id, 'admin_user_created', "Admin created user {$user->name} ({$user->account_id})");

        return redirect()->route('admin.users.index')
            ->with('success', "User account {$user->name} ({$user->account_id}) created successfully.");
    }

    public function show(User $user)
    {
        $user->load('wallet');

        $transactions = \App\Models\Transaction::where(function ($q) use ($user) {
                $q->where('sender_wallet_id', $user->wallet?->id)
                  ->orWhere('receiver_wallet_id', $user->wallet?->id);
            })
            ->latest()
            ->paginate(10);

        $deposits = \App\Models\AdminDeposit::where('wallet_id', $user->wallet?->id)
            ->with('admin')
            ->latest()
            ->get();

        return view('admin.users.show', compact('user', 'transactions', 'deposits'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own status.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        if ($user->wallet) {
            $user->wallet->update([
                'status' => $newStatus === 'active' ? 'active' : 'frozen',
            ]);
        }

        ActivityLogService::log(
            Auth::id(),
            'admin_user_status_changed',
            "Changed status of {$user->name} to {$newStatus}"
        );

        return back()->with('success', "Account {$user->name} status set to {$newStatus}.");
    }

    public function bulkCreateView()
    {
        return view('admin.users.bulk');
    }

    public function bulkCreateProcess(Request $request)
    {
        $request->validate([
            'bulk_data' => 'nullable|string',
            'csv_file' => 'nullable|file|mimes:csv,txt|max:2048',
            'default_pin' => 'required|digits:5',
            'default_password' => 'required|string|min:6',
        ]);

        $lines = [];

        // Check if CSV was uploaded
        if ($request->hasFile('csv_file')) {
            $content = file_get_contents($request->file('csv_file')->getRealPath());
            $lines = preg_split("/\r\n|\n|\r/", $content);
        } elseif ($request->filled('bulk_data')) {
            $lines = preg_split("/\r\n|\n|\r/", $request->input('bulk_data'));
        } else {
            return back()->with('error', 'Please provide either a CSV file or paste employee lines.');
        }

        $defaultPin = $request->input('default_pin', '12345');
        $defaultPassword = $request->input('default_password', 'Barakah@123');
        $admin = Auth::user();

        $createdCount = 0;
        $errors = [];
        $createdList = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with(strtolower($line), 'name')) {
                continue; // Skip empty lines and headers
            }

            // Expected format: Name, Email, Phone, Initial Balance, Role (optional)
            $parts = array_map('trim', str_getcsv($line));

            if (count($parts) < 3) {
                $errors[] = "Row #" . ($index + 1) . ": Insufficient columns. Minimum: Name, Email, Phone.";
                continue;
            }

            $name = $parts[0];
            $email = strtolower($parts[1]);
            $phone = $parts[2];
            $initialBalance = isset($parts[3]) && is_numeric($parts[3]) ? (float) $parts[3] : 0;
            $role = isset($parts[4]) && in_array(strtolower($parts[4]), ['user', 'agent', 'admin']) ? strtolower($parts[4]) : 'user';

            // Validate duplicate email/phone
            if (User::where('email', $email)->exists()) {
                $errors[] = "Row #" . ($index + 1) . " ({$name}): Email '{$email}' already exists.";
                continue;
            }
            if (User::where('phone', $phone)->exists()) {
                $errors[] = "Row #" . ($index + 1) . " ({$name}): Phone '{$phone}' already exists.";
                continue;
            }

            try {
                DB::transaction(function () use (
                    $name, $email, $phone, $role, $initialBalance,
                    $defaultPassword, $defaultPin, $admin,
                    &$createdList, &$createdCount
                ) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => Hash::make($defaultPassword),
                        'pin' => Hash::make($defaultPin),
                        'role' => $role,
                        'status' => 'active',
                    ]);

                    $wallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0.0000,
                        'currency' => 'BDT',
                        'status' => 'active',
                    ]);

                    if ($initialBalance > 0) {
                        $this->walletService->deposit(
                            $wallet,
                            $initialBalance,
                            'Bulk initial institutional balance',
                            $admin->id
                        );
                    }

                    $createdList[] = [
                        'name' => $user->name,
                        'account_id' => $user->account_id,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'balance' => $initialBalance,
                    ];
                    $createdCount++;
                });
            } catch (\Exception $e) {
                $errors[] = "Row #" . ($index + 1) . " ({$name}): " . $e->getMessage();
            }
        }

        ActivityLogService::log(
            $admin->id,
            'admin_bulk_user_create',
            "Admin created {$createdCount} employee accounts via bulk batch"
        );

        return view('admin.users.bulk_result', compact('createdCount', 'errors', 'createdList', 'defaultPin', 'defaultPassword'));
    }
}

