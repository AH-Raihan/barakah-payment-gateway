@extends('layouts.admin')

@section('title', 'Add Balance to User Account')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.deposits.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Deposit Logs
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Add Balance to Account</h1>
        <p class="text-xs text-slate-500 mt-1">Inject institutional funds directly into a recipient's Barakah wallet</p>
    </div>

    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
        
        <form action="{{ route('admin.deposits.store') }}" method="POST" class="space-y-4" x-data="submitOnce" @submit="handleSubmit($event)">
            @csrf

            <!-- Recipient Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Target User / Employee
                </label>
                
                @if($selectedUser)
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-extrabold text-slate-900 text-sm">{{ $selectedUser->name }}</p>
                            <p class="text-xs font-mono text-teal-700 font-bold mt-0.5">{{ $selectedUser->phone ?? $selectedUser->account_id }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-500 font-bold block uppercase">Current Balance:</span>
                            <span class="font-extrabold font-mono text-slate-900 text-sm">৳ {{ number_format($selectedUser->wallet?->balance ?? 0, 2) }}</span>
                        </div>
                    </div>
                @else
                    <select name="user_id" required class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                        <option value="">-- Select Member Account --</option>
                        @foreach(\App\Models\User::with('wallet')->orderBy('name')->get() as $u)
                        <option value="{{ $u->id }}">
                            {{ $u->name }} ({{ $u->phone ?? $u->account_id }}) — Current: ৳{{ number_format($u->wallet?->balance ?? 0, 2) }}
                        </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <!-- Amount -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Deposit Amount (BDT)
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-xl font-bold text-slate-400">৳</span>
                    <input type="number" step="any" min="1" max="100000000" name="amount" required placeholder="0.00"
                           class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-emerald-700 font-mono font-extrabold text-xl focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Audit Note / Reason -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Administrative Reason / Audit Note
                </label>
                <input type="text" name="note" required placeholder="Reason for balance deposit"
                       class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <!-- Guarantee Notice -->
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>Funds are credited instantly with row-level database locking and logged in the permanent audit trail.</span>
            </div>

            <div class="pt-2">
                <button type="submit" :disabled="submitting"
                        class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-md shadow-teal-700/20 transition transform active:scale-[0.98]">
                    <span x-show="!submitting">Confirm & Deposit Balance</span>
                    <span x-show="submitting">Processing Deposit...</span>
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
