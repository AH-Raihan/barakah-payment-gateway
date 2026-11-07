@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="py-4 space-y-4 max-w-md mx-auto" x-data="{ copied: false }">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Profile
        </a>
    </div>

    <div>
        <h2 class="text-lg font-bold text-slate-800">Two-Factor Authentication (2FA)</h2>
        <p class="text-xs text-slate-500">Enhanced protection with Google Authenticator or Authy</p>
    </div>

    @if(!$user->two_factor_enabled)
    <!-- Setup 2FA Card -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4 text-center">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 mx-auto flex items-center justify-center font-bold text-lg">
            🛡️
        </div>

        <div>
            <h3 class="text-sm font-extrabold text-slate-800">Step 1: Scan QR Code</h3>
            <p class="text-xs text-slate-500 mt-0.5">Open Google Authenticator and scan this QR code</p>
        </div>

        <!-- Inline QR code -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 inline-block">
            {!! $qrCodeInline !!}
        </div>

        <!-- Or Enter Secret Key Manually -->
        <div class="text-xs text-slate-500">
            <span>Or enter key manually:</span>
            <div class="mt-1 p-2 rounded-xl bg-slate-100 border border-slate-200 font-mono font-bold text-slate-800 flex items-center justify-between">
                <span class="truncate">{{ $secretKey }}</span>
                <button type="button" @click="navigator.clipboard.writeText('{{ $secretKey }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="text-teal-700 font-sans hover:text-teal-900 ml-2 shrink-0">
                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                </button>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Step 2: Confirm 6-digit code -->
        <div class="text-left">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Step 2: Enter Verification Code</h3>
            <form action="{{ route('2fa.enable') }}" method="POST" class="space-y-3" x-data="submitOnce" @submit="handleSubmit($event)">
                @csrf
                <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required
                       placeholder="123456"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-center tracking-widest text-xl font-mono text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">

                <button type="submit" :disabled="submitting"
                        class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                    Activate Two-Factor Authentication
                </button>
            </form>
        </div>
    </div>
    @else
    <!-- 2FA is Currently Active -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm text-center space-y-4">
        <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center font-bold text-2xl">
            ✓
        </div>
        <div>
            <h3 class="text-base font-extrabold text-slate-800">2FA is Enabled</h3>
            <p class="text-xs text-slate-500 mt-1">Your account is protected by Google Authenticator time-based one-time passwords.</p>
        </div>

        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-left">
            <h4 class="text-xs font-bold text-rose-800 mb-1">Disable Two-Factor Authentication</h4>
            <p class="text-[11px] text-rose-600 mb-3">To turn off 2FA, confirm your account password below:</p>

            <form action="{{ route('2fa.disable') }}" method="POST" class="space-y-2">
                @csrf
                <input type="password" name="password" required placeholder="Account Password"
                       class="w-full px-3 py-2 rounded-xl bg-white border border-rose-200 text-xs text-slate-800 focus:outline-none focus:border-rose-400">
                <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition">
                    Disable 2FA Protection
                </button>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

