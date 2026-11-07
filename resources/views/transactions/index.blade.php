@extends('layouts.app')

@section('title', 'Transaction History')

@section('content')
<div class="py-4 space-y-4">
    
    <!-- Top Header & Search -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Transaction History</h2>
            <p class="text-xs text-slate-500">All local institutional wallet activities</p>
        </div>

        <!-- Filter Chips -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs">
            <a href="{{ route('transactions.index', ['type' => 'all']) }}"
               class="px-3.5 py-1.5 rounded-full font-semibold transition shrink-0 {{ empty($type) || $type === 'all' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-teal-400' }}">
                All
            </a>
            <a href="{{ route('transactions.index', ['type' => 'sent']) }}"
               class="px-3.5 py-1.5 rounded-full font-semibold transition shrink-0 {{ $type === 'sent' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-teal-400' }}">
                Sent Money
            </a>
            <a href="{{ route('transactions.index', ['type' => 'received']) }}"
               class="px-3.5 py-1.5 rounded-full font-semibold transition shrink-0 {{ $type === 'received' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-teal-400' }}">
                Received
            </a>
            <a href="{{ route('transactions.index', ['type' => 'cash_out']) }}"
               class="px-3.5 py-1.5 rounded-full font-semibold transition shrink-0 {{ $type === 'cash_out' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-teal-400' }}">
                Cash Out
            </a>
            <a href="{{ route('transactions.index', ['type' => 'deposit']) }}"
               class="px-3.5 py-1.5 rounded-full font-semibold transition shrink-0 {{ $type === 'deposit' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-teal-400' }}">
                Deposits
            </a>
        </div>

        <!-- Search Input -->
        <form action="{{ route('transactions.index') }}" method="GET" class="relative">
            @if($type) <input type="hidden" name="type" value="{{ $type }}"> @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by TXN reference or note..."
                   class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 shadow-sm transition">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </form>
    </div>

    <!-- Transactions List -->
    @if($transactions->isEmpty())
    <div class="p-12 rounded-3xl bg-white border border-slate-200/80 text-center shadow-sm">
        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <h4 class="text-sm font-bold text-slate-700">No Transactions Found</h4>
        <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or search keywords.</p>
    </div>
    @else
    <div class="space-y-2.5">
        @foreach($transactions as $tx)
            @php
                $isSender = $tx->sender_wallet_id === $wallet->id;
                $counterUser = $isSender ? $tx->receiverWallet?->user : $tx->senderWallet?->user;
            @endphp
            <a href="{{ route('transactions.show', $tx->uuid) }}"
               class="block p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-teal-500 hover:shadow-md transition active:scale-[0.99]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 {{ $isSender ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                            @if($tx->type === 'deposit')
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            @elseif($tx->type === 'cash_out')
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @elseif($isSender)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">
                                @if($tx->type === 'deposit')
                                    Admin Balance Deposit
                                @elseif($isSender)
                                    Sent to: {{ $counterUser?->name ?? 'External Project' }}
                                @else
                                    Received from: {{ $counterUser?->name ?? 'External Project' }}
                                @endif
                            </p>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                {{ $tx->reference }}
                            </p>
                            <p class="text-[10px] text-slate-500 mt-0.5">
                                {{ $tx->created_at->format('M d, Y • h:i A') }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-sm font-extrabold font-mono {{ $isSender ? 'text-slate-900' : 'text-emerald-600' }}">
                            {{ $isSender ? '-' : '+' }}{{ number_format($tx->amount, 2) }} BDT
                        </p>
                        <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $tx->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($tx->status) }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach

        <div class="pt-3">
            {{ $transactions->links() }}
        </div>
    </div>
    @endif

</div>
@endsection

