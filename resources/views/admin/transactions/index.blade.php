@extends('layouts.admin')

@section('title', 'Global Transactions Audit')

@section('content')
<div class="space-y-6">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                Transactions Audit <span class="font-arabic text-emerald-700 text-xl">بركة</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Immutable ledger of all institutional financial events</p>
        </div>
    </div>

    <!-- Quick Stats Cards (Clean Light Theme) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px] block">Total Volume</span>
            <span class="text-lg font-extrabold text-slate-900 font-mono mt-1 block">৳ {{ number_format($stats['total_volume'], 2) }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px] block">Transactions Count</span>
            <span class="text-lg font-extrabold text-teal-700 font-mono mt-1 block">{{ $stats['total_count'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px] block">Transfers (P2P)</span>
            <span class="text-lg font-extrabold text-emerald-700 font-mono mt-1 block">৳ {{ number_format($stats['transfers_volume'], 2) }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px] block">Admin Injections</span>
            <span class="text-lg font-extrabold text-sky-700 font-mono mt-1 block">৳ {{ number_format($stats['deposits_volume'], 2) }}</span>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-sm">
        <form action="{{ route('admin.transactions.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by reference, user name, phone, or account ID..."
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-teal-500 transition">
            </div>

            <div>
                <select name="type" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-800 focus:outline-none focus:border-teal-500 transition">
                    <option value="all">All Types</option>
                    <option value="send_money" {{ $type === 'send_money' ? 'selected' : '' }}>Send Money (P2P)</option>
                    <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Admin Deposit</option>
                    <option value="cash_out" {{ $type === 'cash_out' ? 'selected' : '' }}>Cash Out</option>
                    <option value="payment" {{ $type === 'payment' ? 'selected' : '' }}>Payment Gateway</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-800 focus:outline-none focus:border-teal-500 transition">
                    <option value="all">All Statuses</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-600 text-white font-bold shrink-0 shadow-sm transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Audit Table (Clean Light Mode) -->
    <div class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Reference & Date</th>
                        <th class="px-5 py-3.5">Type</th>
                        <th class="px-5 py-3.5">Sender & Receiver</th>
                        <th class="px-5 py-3.5">Amount</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5">
                            <span class="font-mono font-bold text-slate-900 block">{{ $tx->reference }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $tx->created_at->format('M d, Y • h:i:s A') }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $tx->type === 'deposit' ? 'bg-sky-50 text-sky-800 border-sky-200' : ($tx->type === 'cash_out' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200') }}">
                                {{ str_replace('_', ' ', $tx->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">
                            @if($tx->type === 'deposit')
                                <span class="text-sky-700 font-semibold">Admin Injection</span> &rarr; {{ $tx->receiverWallet?->user?->name ?? 'User' }}
                            @else
                                {{ $tx->senderWallet?->user?->name ?? 'External' }} &rarr; {{ $tx->receiverWallet?->user?->name ?? 'External' }}
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-900 text-sm">
                            ৳ {{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $tx->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $tx->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.transactions.show', $tx) }}" class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 font-bold text-[11px] border border-teal-200 transition">
                                Inspect
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                            No transaction records matched your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection
