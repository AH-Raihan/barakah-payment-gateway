@extends('layouts.app')

@section('title', 'Profile & Settings')

@section('content')
<div class="py-4 space-y-4">
    
    <!-- Profile Card Header -->
    <div class="p-6 rounded-3xl bg-gradient-to-br from-teal-800 to-slate-950 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-400/40 flex items-center justify-center text-emerald-300 font-extrabold text-xl shadow-inner">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-bold text-white tracking-tight truncate">{{ $user->name }}</h2>
                <p class="text-xs font-mono text-emerald-400 mt-0.5">{{ $user->account_id }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-teal-500/30 text-teal-200 border border-teal-500/40">
                        {{ $user->role }}
                    </span>
                    <span class="text-xs text-slate-300">{{ $user->phone ?? $user->email }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Menu Navigation -->
    <div class="bg-white rounded-3xl p-3 border border-slate-200/80 shadow-sm divide-y divide-slate-100">
        
        <!-- Security Settings -->
        <a href="{{ route('profile.security') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Password & Security</h4>
                    <p class="text-[11px] text-slate-400">Change password, view authentication status</p>
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        <!-- Change PIN -->
        <a href="{{ route('profile.change-pin') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Change 5-Digit PIN</h4>
                    <p class="text-[11px] text-slate-400">Update your app unlock & transfer PIN</p>
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        <!-- Two Factor Authentication -->
        <a href="{{ route('2fa.setup') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Two-Factor Authentication (2FA)</h4>
                    <p class="text-[11px] text-slate-400">Google Authenticator TOTP protection</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $user->two_factor_enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                </span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>

        <!-- About Barakah -->
        <a href="{{ route('profile.about') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold">
                    <span class="font-arabic text-base">ب</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">About Barakah</h4>
                    <p class="text-[11px] text-slate-400">Halal institutional wallet • Relic Software</p>
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <!-- Update Personal Details Form -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Personal Contact Information</h3>
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2 rounded-xl bg-slate-50 border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile Phone (BD)</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                       class="w-full px-4 py-2 rounded-xl bg-slate-50 border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>
            <div class="pt-1">
                <button type="submit" class="py-2.5 px-4 rounded-xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-sm transition">
                    Update Details
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Activity Log -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Recent Account Activity</h3>
        <div class="space-y-2">
            @forelse($recentActivities as $act)
            <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100 last:border-0">
                <div>
                    <p class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $act->action) }}</p>
                    <p class="text-[11px] text-slate-500">{{ $act->description }}</p>
                </div>
                <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ $act->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-xs text-slate-400">No security logs recorded yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Log Out Button -->
    <div class="pt-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition text-center flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out of Barakah
            </button>
        </form>
    </div>

    <!-- Relic Software Brand Footer -->
    <div class="text-center pt-2 pb-4 text-[11px] text-slate-400">
        Barakah Wallet v1.0 • Powered by <span class="text-teal-700 font-bold">Relic Software</span>
    </div>

</div>
@endsection

