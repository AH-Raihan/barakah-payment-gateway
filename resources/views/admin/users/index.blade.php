@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Institutional Accounts</h1>
            <p class="text-xs text-slate-500 mt-1">Manage employees, agents, administrators, and balances</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.bulk') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-slate-50 text-teal-800 text-xs font-bold border border-slate-200 shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Bulk Onboard CSV
            </a>
            <a href="{{ route('admin.users.create') }}" class="px-3.5 py-2 rounded-2xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Create Account
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, email, phone, or Account ID..."
                       class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div>
                <select name="role" class="w-full px-3 py-2.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-800 focus:outline-none focus:border-teal-500 focus:bg-white transition">
                    <option value="all">All Roles</option>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>User / Employee</option>
                    <option value="agent" {{ $role === 'agent' ? 'selected' : '' }}>Agent</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ $role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3 py-2.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-800 focus:outline-none focus:border-teal-500 focus:bg-white transition">
                    <option value="all">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-2xl bg-teal-700 hover:bg-teal-600 text-white font-bold shrink-0 transition shadow-sm">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Account & User</th>
                        <th class="px-5 py-3.5">Contact</th>
                        <th class="px-5 py-3.5">Role</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Wallet Balance</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-2xl bg-teal-50 border border-teal-200 text-teal-800 font-bold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.show', $u) }}" class="font-bold text-slate-900 hover:text-teal-700 transition truncate block">
                                        {{ $u->name }}
                                    </a>
                                    <span class="text-[11px] font-mono text-teal-700 font-bold block">{{ $u->account_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 font-mono">
                            <p class="font-bold text-slate-900">{{ $u->phone ?? '—' }}</p>
                            <p class="text-[10px] text-slate-400">{{ $u->email }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $u->role === 'super_admin' ? 'bg-purple-50 text-purple-800 border-purple-200' : ($u->role === 'agent' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-teal-50 text-teal-800 border-teal-200') }}">
                                {{ str_replace('_', ' ', $u->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->status === 'active' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }}">
                                {{ $u->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-slate-900">
                            ৳ {{ number_format($u->wallet?->balance ?? 0, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-right space-x-1.5">
                            <a href="{{ route('admin.deposits.create', ['user_id' => $u->id]) }}" class="px-2.5 py-1 rounded-xl bg-teal-50 text-teal-800 hover:bg-teal-100 transition font-bold text-[11px]" title="Add Balance">
                                + Add ৳
                            </a>
                            <a href="{{ route('admin.users.show', $u) }}" class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition font-bold text-[11px]">
                                View
                            </a>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-xl transition font-bold text-[11px] {{ $u->status === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                    {{ $u->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                            No institutional accounts matched your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
