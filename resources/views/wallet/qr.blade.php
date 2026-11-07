@extends('layouts.app')

@section('title', __('My Account QR'))

@section('content')
<div class="py-4 space-y-4 max-w-sm mx-auto text-center" x-data="{ copied: false }">
    
    <div>
        <h2 class="text-xl font-bold text-slate-800">{{ __('My Account QR') }}</h2>
        <p class="text-xs text-slate-500">Scan to pay or transfer money to this mobile number</p>
    </div>

    <!-- QR Card (Clean White with Green Islamic Accent) -->
    <div class="bg-white rounded-3xl p-6 border border-emerald-100 shadow-xl shadow-emerald-950/5 relative overflow-hidden">
        
        <!-- Header Banner with Green Logo -->
        <div class="mb-4">
            <img src="/images/logo.png" alt="Barakah" class="h-10 mx-auto object-contain rounded-xl">
        </div>

        <h3 class="font-extrabold text-slate-900 text-base">{{ $user->name }}</h3>
        <p class="text-xs font-bold text-teal-700 capitalize mt-0.5">{{ $user->role }} • Barakah Wallet</p>

        <!-- Real Scannable Vector QR Code with Mobile Number -->
        <div class="my-5 p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 inline-block shadow-inner">
            <div class="w-56 h-56 mx-auto flex items-center justify-center [&>svg]:w-full [&>svg]:h-full [&>svg]:rounded-lg">
                {!! $qrSvg !!}
            </div>
        </div>

        <!-- Phone Number Highlight -->
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 mb-3">
            <span class="text-[10px] text-emerald-800 font-bold uppercase tracking-wider block mb-0.5">{{ __('Scannable Mobile Number') }}</span>
            <span class="font-mono font-extrabold text-lg text-emerald-900 tracking-wider">
                {{ $qrData }}
            </span>
        </div>

        <!-- Account ID Strip -->
        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
            <div class="text-left">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">{{ __('Account ID') }}</span>
                <span class="font-mono font-bold text-xs text-slate-800">{{ $user->account_id }}</span>
            </div>
            <button @click="navigator.clipboard.writeText('{{ $user->account_id }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="py-1 px-3 rounded-xl bg-teal-700 text-white text-xs font-semibold hover:bg-teal-600 transition flex items-center gap-1.5 shadow-sm">
                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span x-text="copied ? 'Copied!' : 'Copy A/C'"></span>
            </button>
        </div>
    </div>

</div>
@endsection
