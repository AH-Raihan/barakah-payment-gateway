@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="py-4 space-y-4 max-w-md mx-auto">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Profile
        </a>
    </div>

    <div>
        <h2 class="text-lg font-bold text-slate-800">Password & Security</h2>
        <p class="text-xs text-slate-500">Manage your institutional account protection</p>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Change Account Password</h3>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-3.5" x-data="submitOnce" @submit="handleSubmit($event)">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Current Password</label>
                <input type="password" name="current_password" required placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">New Password</label>
                <input type="password" name="password" required placeholder="Min 6 characters"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required placeholder="Repeat new password"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div class="pt-2">
                <button type="submit" :disabled="submitting"
                        class="w-full py-3 px-4 rounded-xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-md shadow-teal-700/20 transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Shortcuts Card -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-3 text-xs">
        <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Other Security Controls</h3>
        <div class="flex items-center justify-between py-2 border-b border-slate-100">
            <div>
                <p class="font-bold text-slate-800">5-Digit PIN</p>
                <p class="text-[11px] text-slate-400">Required on every app unlock</p>
            </div>
            <a href="{{ route('profile.change-pin') }}" class="px-3 py-1 rounded-lg bg-teal-50 text-teal-800 font-bold hover:bg-teal-100 transition">Change PIN</a>
        </div>
        <div class="flex items-center justify-between py-2">
            <div>
                <p class="font-bold text-slate-800">2-Factor Auth (TOTP)</p>
                <p class="text-[11px] text-slate-400">Google Authenticator protection</p>
            </div>
            <a href="{{ route('2fa.setup') }}" class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-800 font-bold hover:bg-indigo-100 transition">Configure 2FA</a>
        </div>
    </div>

</div>
@endsection

