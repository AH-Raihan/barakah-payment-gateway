@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="py-4 space-y-5">
    
    <!-- Balance Card (Interactive Eye Toggle, Halal Teal Gradient) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-700 via-teal-800 to-emerald-950 p-6 text-white shadow-xl shadow-teal-900/20"
         x-data="{ showBalance: false }">
        <!-- Subtle Pattern Overlay -->
        <div class="absolute inset-0 bg-islamic-pattern opacity-10 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col justify-between h-full">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <img src="/images/logo-icon.png" alt="Barakah" class="h-6 w-auto object-contain">
                    <span class="text-xs font-bold text-teal-100 tracking-wide uppercase">{{ __('My Balance') }}</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    {{ $wallet->currency }}
                </span>
            </div>

            <!-- Amount Display with Tap-To-Reveal -->
            <div class="my-5 cursor-pointer select-none" @click="showBalance = !showBalance">
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl sm:text-4xl font-extrabold tracking-tight"
                          x-text="showBalance ? '{{ number_format($wallet->balance, 2) }}' : '••••••••'">
                    </span>
                    <span class="text-sm font-bold text-teal-200">{{ $wallet->currency }}</span>
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-teal-300/80 hover:text-white transition">
                    <svg x-show="!showBalance" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showBalance" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    <span x-text="showBalance ? '{{ __('Tap to hide balance') }}' : '{{ __('Tap to reveal balance') }}'"></span>
                </div>
            </div>

            <!-- Account Details Strip -->
            <div class="pt-3 border-t border-teal-600/40 flex items-center justify-between text-xs text-teal-200"
                 x-data="{ copied: false }">
                <div class="flex items-center space-x-1 font-mono">
                    <span>{{ __('Account ID') }}:</span>
                    <strong class="text-white">{{ $user->account_id }}</strong>
                    <button @click.stop="navigator.clipboard.writeText('{{ $user->account_id }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="ml-1 text-teal-300 hover:text-white p-0.5" title="Copy Account ID">
                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-show="copied" class="text-[10px] text-emerald-300 font-bold">Copied!</span>
                    </button>
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-bold text-emerald-300 uppercase text-[11px]">{{ $wallet->status }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Grid 4 items: Send, Cash Out, QR, History) -->
    <div class="grid grid-cols-4 gap-2.5 sm:gap-3 text-center">
        <!-- Send Money -->
        <a href="{{ route('wallet.send') }}" class="group flex flex-col items-center p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-teal-500 hover:shadow-md transition active:scale-95">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-400 text-white flex items-center justify-center shadow-md shadow-teal-500/20 mb-2 group-hover:scale-105 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-700 group-hover:text-teal-700">{{ __('Send Money') }}</span>
        </a>

        <!-- Cash Out -->
        <a href="{{ route('wallet.cashout') }}" class="group flex flex-col items-center p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-teal-500 hover:shadow-md transition active:scale-95">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-400 text-white flex items-center justify-center shadow-md shadow-amber-500/20 mb-2 group-hover:scale-105 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-700 group-hover:text-amber-700">{{ __('Cash Out') }}</span>
        </a>

        <!-- My QR -->
        <a href="{{ route('wallet.qr') }}" class="group flex flex-col items-center p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-teal-500 hover:shadow-md transition active:scale-95">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-500 text-white flex items-center justify-center shadow-md shadow-sky-500/20 mb-2 group-hover:scale-105 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-700 group-hover:text-indigo-700">{{ __('My QR') }}</span>
        </a>

        <!-- History -->
        <a href="{{ route('transactions.index') }}" class="group flex flex-col items-center p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-teal-500 hover:shadow-md transition active:scale-95">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 mb-2 group-hover:scale-105 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-700 group-hover:text-teal-700">{{ __('History') }}</span>
        </a>
    </div>

    <!-- Monthly Stats Summary -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-3 text-xs">
            <span class="font-bold text-slate-600 uppercase tracking-wider">{{ __("This Month's Activity") }}</span>
        </div>
        <div class="grid grid-cols-2 gap-3 text-center">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-[11px] text-slate-500 font-medium">{{ __('Sent') }}</p>
                <p class="text-sm sm:text-base font-extrabold text-slate-800 mt-0.5">{{ number_format($monthlySent, 0) }} <span class="text-xs font-normal text-slate-500">BDT</span></p>
            </div>
            <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100">
                <p class="text-[11px] text-emerald-800 font-medium">{{ __('Received') }}</p>
                <p class="text-sm sm:text-base font-extrabold text-emerald-700 mt-0.5">{{ number_format($monthlyReceived, 0) }} <span class="text-xs font-normal text-emerald-600">BDT</span></p>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Header -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-bold text-sm text-slate-800">{{ __('Recent Transactions') }}</h3>
            <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-900">{{ __('View All') }} &rarr;</a>
        </div>

        @if($recentTransactions->isEmpty())
        <div class="p-8 rounded-2xl bg-white border border-slate-200/80 text-center shadow-sm">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-semibold text-slate-600">{{ __('No transactions yet') }}</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($recentTransactions as $tx)
                @php
                    $isSender = $tx->sender_wallet_id === $wallet->id;
                    $counterUser = $isSender ? $tx->receiverWallet?->user : $tx->senderWallet?->user;
                @endphp
                <a href="{{ route('transactions.show', $tx->uuid) }}"
                   class="block p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-teal-500/50 hover:shadow-md transition active:scale-[0.99]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $isSender ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                @if($tx->type === 'deposit')
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                @elseif($isSender)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">
                                    @if($tx->type === 'deposit')
                                        Admin Fund Deposit
                                    @elseif($isSender)
                                        To: {{ $counterUser?->name ?? 'External' }}
                                    @else
                                        From: {{ $counterUser?->name ?? 'External' }}
                                    @endif
                                </p>
                                <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $tx->reference }} • {{ $tx->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="text-sm font-extrabold font-mono {{ $isSender ? 'text-slate-800' : 'text-emerald-600' }}">
                                {{ $isSender ? '-' : '+' }}{{ number_format($tx->amount, 2) }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
