@extends('layouts.app')

@section('title', 'About Barakah')

@section('content')
<div class="py-4 space-y-4 max-w-md mx-auto">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Profile
        </a>
    </div>

    <!-- About Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-5 text-center">
        
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 font-bold text-2xl mx-auto flex items-center justify-center shadow-lg shadow-teal-500/20">
            ب
        </div>

        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center justify-center gap-2">
                Barakah <span class="font-arabic text-emerald-600 text-lg">بركة</span>
            </h2>
            <p class="text-xs font-semibold text-teal-700 mt-0.5">Your Institutional Financial Wallet</p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                Institutional Financial Wallet
            </span>
        </div>

        <div class="text-xs text-slate-600 leading-relaxed text-left space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <p>
                <strong>Barakah</strong> is designed specifically as a seamless institutional closed-loop financial wallet. Within our institutional campus, employees, faculty, students, and staff can hold balances, transfer money, cash out, and pay for services with total reliability.
            </p>
            <p>
                Built with financial database locking, idempotency safeguards against accidental double-clicks, and a mandatory 5-digit PIN on every launch, Barakah delivers rock-solid institutional security.
            </p>
        </div>

        <!-- System Architecture Highlights -->
        <div class="grid grid-cols-2 gap-2 text-left text-xs">
            <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-100">
                <p class="font-bold text-teal-900">Instant Transfers</p>
                <p class="text-[11px] text-teal-700 mt-0.5">Fast peer-to-peer campus wallet transfers</p>
            </div>
            <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-100">
                <p class="font-bold text-teal-900">Payment Gateway</p>
                <p class="text-[11px] text-teal-700 mt-0.5">API integration for campus projects & stores</p>
            </div>
            <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-100">
                <p class="font-bold text-teal-900">Dual-Layer Auth</p>
                <p class="text-[11px] text-teal-700 mt-0.5">Password + 5-digit PIN + optional 2FA</p>
            </div>
            <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-100">
                <p class="font-bold text-teal-900">Idempotency Guard</p>
                <p class="text-[11px] text-teal-700 mt-0.5">Network glitch duplicate protection</p>
            </div>
        </div>

        <!-- Subtle Relic Software Attribution -->
        <div class="pt-4 border-t border-slate-100 text-center">
            <p class="text-[11px] text-slate-400">
                Developed and engineered with precision
            </p>
            <p class="text-xs font-medium text-slate-600 mt-0.5">
                Powered by <strong class="text-emerald-700 font-bold">Relic Software</strong>
            </p>
        </div>

    </div>

</div>
@endsection

