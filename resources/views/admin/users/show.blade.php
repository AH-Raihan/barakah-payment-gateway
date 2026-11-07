@extends('layouts.admin')

@section('title', 'User Profile: ' . $user->name)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Users List
        </a>
    </div>

    <!-- User Header Card (Clean Light Theme) -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-200 text-teal-800 font-extrabold text-xl flex items-center justify-center shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">{{ $user->name }}</h1>
                <p class="text-xs font-mono text-teal-700 font-bold">{{ $user->account_id }}</p>
                <div class="flex items-center gap-2 mt-1.5 text-xs text-slate-500">
                    <span>{{ $user->phone ?? 'No phone' }}</span> • <span>{{ $user->email }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.deposits.create', ['user_id' => $user->id]) }}"
               class="px-3.5 py-2 rounded-xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-sm transition">
                + Add Balance
            </a>
            @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $user->status === 'active' ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                    {{ $user->status === 'active' ? 'Suspend Account' : 'Activate Account' }}
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Wallet & Account Metrics (Clean Light Mode) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-xs text-slate-400 uppercase tracking-wider block font-bold mb-1">Current Balance</span>
            <span class="text-2xl font-extrabold text-slate-900 font-mono">৳ {{ number_format($user->wallet?->balance ?? 0, 2) }}</span>
            <p class="text-[11px] text-teal-700 font-semibold mt-1">Status: {{ ucfirst($user->wallet?->status ?? 'Active') }}</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-xs text-slate-400 uppercase tracking-wider block font-bold mb-1">Account Role</span>
            <span class="text-xl font-extrabold text-slate-900 capitalize">{{ str_replace('_', ' ', $user->role) }}</span>
            <p class="text-[11px] text-slate-500 mt-1">2FA: {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-xs text-slate-400 uppercase tracking-wider block font-bold mb-1">Joined Institutional</span>
            <span class="text-base font-extrabold text-slate-800 font-mono">{{ $user->created_at->format('M d, Y') }}</span>
            <p class="text-[11px] text-slate-400 mt-1">{{ $user->created_at->diffForHumans() }}</p>
        </div>
    </div>

    <!-- Admin Deposits History for This User -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-3">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Injected Admin Deposits</h3>
        @if($deposits->isEmpty())
        <p class="text-xs text-slate-400 py-3">No direct admin deposits have been added to this wallet.</p>
        @else
        <div class="space-y-2 text-xs">
            @foreach($deposits as $dep)
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="font-bold text-emerald-700 font-mono text-sm">+৳ {{ number_format($dep->amount, 2) }}</span>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $dep->note ?? 'Admin Deposit' }} • Added by {{ $dep->admin?->name }}</p>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">{{ $dep->created_at->format('M d, Y • h:i A') }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Recent Transactions -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-3">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Transaction Activity</h3>
        @if($transactions->isEmpty())
        <p class="text-xs text-slate-400 py-3">No transaction history recorded yet.</p>
        @else
        <div class="space-y-2 text-xs">
            @foreach($transactions as $tx)
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $tx->type) }}</p>
                    <p class="text-[11px] text-slate-400 font-mono">{{ $tx->reference }} • {{ $tx->created_at->format('M d, Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="font-bold font-mono text-slate-900 text-sm">৳ {{ number_format($tx->amount, 2) }}</span>
                    <span class="block text-[10px] text-emerald-700 font-bold uppercase">{{ $tx->status }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
