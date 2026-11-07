@extends('layouts.admin')

@section('title', 'Create User Account')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Users List
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Create Account</h1>
        <p class="text-xs text-slate-500 mt-1">Register a new employee, student, or agent with optional initial balance</p>
    </div>

    <!-- Create Card (Clean Light Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm">
        
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4" x-data="submitOnce" @submit="handleSubmit($event)">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Member full name"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Institutional Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="member@institute.edu"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Phone (BD)</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="01712345678"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Role</label>
                    <select name="role" required class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                        <option value="user">User / Member</option>
                        <option value="agent">Agent (Cash Out Point)</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                    <input type="text" name="password" value="Barakah@123" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">5-Digit PIN</label>
                    <input type="text" name="pin" value="12345" maxlength="5" pattern="[0-9]{5}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-mono tracking-widest text-center focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Initial Balance (BDT) — Optional
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold">৳</span>
                    <input type="number" step="any" min="0" name="initial_balance" value="0"
                           class="w-full pl-8 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-emerald-700 font-mono font-bold text-sm focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">If > 0, an initial admin deposit will be credited immediately and logged.</p>
            </div>

            <div class="pt-3">
                <button type="submit" :disabled="submitting"
                        class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-xs shadow-md shadow-teal-800/20 transition transform active:scale-[0.98]">
                    Create User Account & Generate Wallet
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
