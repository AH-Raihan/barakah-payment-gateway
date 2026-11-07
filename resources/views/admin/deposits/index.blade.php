@extends('layouts.admin')

@section('title', 'Admin Deposits & Balance Injections')

@section('content')
<div class="space-y-6">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                Balance Injections (Deposits) <span class="font-arabic text-teal-700 text-xl">بركة</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Audit log of all funds injected into member accounts</p>
        </div>

        <a href="{{ route('admin.deposits.create') }}" class="px-4 py-2.5 rounded-2xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add New Balance
        </a>
    </div>

    <!-- Summary Metrics (Clean Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block mb-1">Total Balance Injected</span>
            <span class="text-3xl font-extrabold text-emerald-700 font-mono">৳ {{ number_format($totalDepositedSum, 2) }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Cumulative funds deposited across institutional accounts</p>
        </div>
        <div class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block mb-1">Total Deposit Events</span>
            <span class="text-3xl font-extrabold text-slate-900 font-mono">{{ $totalDepositedCount }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Tracked through administrative portal & API</p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="p-4 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
        <form action="{{ route('admin.deposits.index') }}" method="GET" class="flex gap-3 text-xs">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by recipient name, Account ID, reference or note..."
                   class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            <button type="submit" class="px-5 py-2.5 rounded-2xl bg-teal-700 hover:bg-teal-600 text-white font-bold shrink-0 transition shadow-sm">
                Search
            </button>
        </form>
    </div>

    <!-- Deposits Audit Table -->
    <div class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Recipient</th>
                        <th class="px-5 py-3.5">Amount Added</th>
                        <th class="px-5 py-3.5">Authorized By</th>
                        <th class="px-5 py-3.5">Reference & Note</th>
                        <th class="px-5 py-3.5">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deposits as $dep)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5">
                            <p class="font-bold text-slate-900">{{ $dep->wallet?->user?->name ?? 'External' }}</p>
                            <p class="text-[11px] font-mono text-teal-700 font-bold">{{ $dep->wallet?->user?->phone ?? $dep->wallet?->user?->account_id }}</p>
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-emerald-700 text-sm">
                            +৳ {{ number_format($dep->amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-slate-800">{{ $dep->admin?->name ?? 'System' }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $dep->admin?->email }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-slate-700 font-semibold">{{ $dep->reference }}</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $dep->note ?? 'No note' }}</p>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-400 text-[11px]">
                            {{ $dep->created_at->format('M d, Y • h:i A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                            No deposit records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $deposits->links() }}
        </div>
    </div>

</div>
@endsection
