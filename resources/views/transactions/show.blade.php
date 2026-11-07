@extends('layouts.app')

@section('title', 'Transaction Receipt')

@section('content')
<div class="py-4 space-y-4 max-w-md mx-auto" x-data="{ copied: false }">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Transactions
        </a>
        <button onclick="window.print()" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Receipt
        </button>
    </div>

    <!-- Digital Receipt Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xl relative overflow-hidden text-center">
        <!-- Top Colored Header Strip -->
        <div class="absolute top-0 inset-x-0 h-2.5 bg-gradient-to-r from-teal-500 via-emerald-400 to-teal-600"></div>

        <!-- Success Badge -->
        <div class="w-16 h-16 rounded-full bg-emerald-100 border-4 border-emerald-50 text-emerald-600 mx-auto flex items-center justify-center font-bold text-2xl shadow-sm mb-3">
            ✓
        </div>

        <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">{{ __('Transaction Successful') }}</h3>
        <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $transaction->created_at->format('d M Y, h:i:s A') }}</p>

        <!-- Big Amount -->
        <div class="my-6 py-4 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Total Transferred') }}</span>
            <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-mono tracking-tight">
                ৳ {{ number_format($transaction->amount, 2) }}
            </div>
        </div>

        <!-- Receipt Breakdown -->
        <div class="space-y-3 text-xs text-left divide-y divide-slate-100">
            
            <div class="flex items-center justify-between pt-3">
                <span class="text-slate-500">{{ __('Transaction ID') }}</span>
                <div class="flex items-center gap-1 font-mono font-bold text-slate-800">
                    <span>{{ $transaction->reference }}</span>
                    <button @click="navigator.clipboard.writeText('{{ $transaction->reference }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-teal-600 hover:text-teal-800 p-0.5">
                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-show="copied" class="text-[10px] text-emerald-600 font-bold">Copied!</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3">
                <span class="text-slate-500">Type</span>
                <span class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $transaction->type) }}</span>
            </div>

            @if($transaction->senderWallet)
            <div class="flex items-center justify-between pt-3">
                <span class="text-slate-500">Sender</span>
                <div class="text-right">
                    <p class="font-bold text-slate-800">{{ $transaction->senderWallet->user?->name ?? 'External' }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">{{ $transaction->senderWallet->user?->account_id }}</p>
                </div>
            </div>
            @endif

            @if($transaction->receiverWallet)
            <div class="flex items-center justify-between pt-3">
                <span class="text-slate-500">Receiver</span>
                <div class="text-right">
                    <p class="font-bold text-slate-800">{{ $transaction->receiverWallet->user?->name ?? 'External' }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">{{ $transaction->receiverWallet->user?->account_id }}</p>
                </div>
            </div>
            @endif

            @if($transaction->description)
            <div class="flex items-center justify-between pt-3">
                <span class="text-slate-500">Reference / Note</span>
                <span class="font-medium text-slate-800">{{ $transaction->description }}</span>
            </div>
            @endif



            <div class="flex items-center justify-between pt-3">
                <span class="text-slate-500">Status</span>
                <span class="font-extrabold text-emerald-700 uppercase tracking-wider text-[11px]">{{ $transaction->status }}</span>
            </div>
        </div>

        <!-- Receipt Watermark / Branding -->
        <div class="mt-8 pt-4 border-t border-dashed border-slate-200 text-center text-[11px] text-slate-400">
            <p class="font-semibold text-slate-600">Barakah Institutional Financial Wallet</p>
            <p class="mt-0.5">Powered by <span class="text-emerald-600 font-bold">Relic Software</span></p>
        </div>
    </div>

</div>
@endsection

