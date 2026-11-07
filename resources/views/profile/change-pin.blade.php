@extends('layouts.app')

@section('title', 'Change 5-Digit PIN')

@section('content')
<div class="py-4 space-y-4 max-w-md mx-auto">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Profile
        </a>
    </div>

    <div>
        <h2 class="text-lg font-bold text-slate-800">Change 5-Digit PIN</h2>
        <p class="text-xs text-slate-500">Your security PIN authorizes app access & transactions</p>
    </div>

    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
        
        <form action="{{ route('profile.update-pin') }}" method="POST" class="space-y-4" x-data="submitOnce" @submit="handleSubmit($event)">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Current 5-Digit PIN</label>
                <input type="password" name="current_pin" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" required autofocus
                       placeholder="•••••"
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-center tracking-widest text-xl font-mono text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">New 5-Digit PIN</label>
                <input type="password" name="pin" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" required
                       placeholder="•••••"
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-center tracking-widest text-xl font-mono text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
                <p class="text-[11px] text-slate-400 mt-1">Must be 5 numbers and different from your current PIN.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Confirm New 5-Digit PIN</label>
                <input type="password" name="pin_confirmation" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" required
                       placeholder="•••••"
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-center tracking-widest text-xl font-mono text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div class="pt-2">
                <button type="submit" :disabled="submitting"
                        class="w-full py-3.5 px-4 rounded-xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-md shadow-teal-700/20 transition">
                    Update Security PIN
                </button>
            </div>
        </form>

    </div>

</div>
@endsection

