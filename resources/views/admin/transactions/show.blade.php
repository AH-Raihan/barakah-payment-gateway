@extends('layouts.admin')

@section('title', 'Inspect Transaction: ' . $transaction->reference)

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Transactions Audit
        </a>
    </div>

    <!-- Transaction Header Card (Clean Light Theme) -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Transaction Reference</span>
                <h1 class="text-xl font-extrabold text-slate-900 font-mono">{{ $transaction->reference }}</h1>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $transaction->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                {{ $transaction->status }}
            </span>
        </div>

        <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-100 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium">Total Transacted</span>
                <div class="text-2xl font-extrabold text-slate-900 font-mono">
                    ৳ {{ number_format($transaction->amount, 2) }} <span class="text-xs text-teal-700 font-sans">BDT</span>
                </div>
            </div>
            <div class="text-right text-xs">
                <span class="text-slate-500">Transaction Status:</span>
                <span class="font-bold text-emerald-700 block uppercase text-[11px]">{{ $transaction->status }}</span>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Transaction Type</span>
                <span class="font-bold text-slate-800 capitalize mt-0.5 block">{{ str_replace('_', ' ', $transaction->type) }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Timestamp</span>
                <span class="font-mono text-slate-700 mt-0.5 block">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Idempotency Key</span>
                <span class="font-mono text-slate-700 text-[11px] truncate mt-0.5 block">{{ $transaction->idempotency_key ?? 'N/A' }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">IP Address</span>
                <span class="font-mono text-slate-700 mt-0.5 block">{{ $transaction->ip_address ?? 'Unknown' }}</span>
            </div>
        </div>
    </div>

    <!-- Audit Event Logs -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Audit Log History</h3>
        <div class="space-y-2 text-xs">
            @forelse($transaction->logs as $log)
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="font-bold text-teal-800 uppercase text-[11px]">{{ $log->action }}</span>
                    @if($log->metadata)
                    <pre class="text-[10px] font-mono text-slate-600 mt-1">{{ json_encode($log->metadata) }}</pre>
                    @endif
                </div>
                <span class="text-[11px] font-mono text-slate-400">{{ $log->created_at->format('H:i:s') }}</span>
            </div>
            @empty
            <p class="text-slate-400 py-2">No secondary audit logs attached.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
