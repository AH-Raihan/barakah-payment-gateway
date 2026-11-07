@extends('layouts.admin')

@section('title', 'Payment Gateway API Integration')

@section('content')
<div class="space-y-6">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                Payment Gateway API <span class="font-arabic text-teal-700 text-xl">بركة</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Allow your other institutional web and mobile projects to accept payments via Barakah Wallet</p>
        </div>

        <a href="{{ route('admin.api-clients.create') }}" class="px-4 py-2.5 rounded-2xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Generate New API Key
        </a>
    </div>

    <!-- Quick Integration Guide (Clean Light Theme) -->
    <div class="p-5 rounded-3xl bg-teal-50 border border-teal-200 text-xs text-teal-950 space-y-3 shadow-xs">
        <div class="flex items-center justify-between">
            <span class="font-bold text-teal-950 uppercase tracking-wider text-[11px]">How External Projects Connect</span>
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-200/80 text-emerald-900 font-bold text-[10px]">REST API v1</span>
        </div>
        <ol class="list-decimal list-inside space-y-1.5 text-slate-700 leading-relaxed font-medium">
            <li>Your other application makes a <code class="text-teal-800 font-mono font-bold bg-white px-2 py-0.5 rounded border border-teal-200">POST /api/v1/payment/create</code> with header <code class="text-teal-800 font-mono font-bold bg-white px-2 py-0.5 rounded border border-teal-200">X-API-KEY: {key}</code> and amount.</li>
            <li>Barakah returns a <code class="text-teal-800 font-mono font-bold bg-white px-2 py-0.5 rounded border border-teal-200">checkout_url</code>. Your app redirects the user there.</li>
            <li>The user enters their 5-digit PIN to pay instantly from their wallet.</li>
            <li>Barakah sends a webhook notification to your callback URL with status: <code class="text-emerald-800 font-mono font-bold bg-white px-2 py-0.5 rounded border border-emerald-200">paid</code> and redirects back.</li>
        </ol>
        <div class="pt-1">
            <a href="{{ route('payment.demo') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-800 hover:text-teal-950 underline">
                <span>Try Live Demo Shop to Test Payment Gateway &rarr;</span>
            </a>
        </div>
    </div>

    <!-- API Clients Table -->
    <div class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Registered External Projects</h3>
            <span class="text-[11px] text-teal-700 font-bold">{{ $clients->total() }} clients active</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Application Name</th>
                        <th class="px-5 py-3.5">Webhook URL</th>
                        <th class="px-5 py-3.5">Rate Limit</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Created</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clients as $c)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $c->name }}</td>
                        <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500">{{ $c->webhook_url ?? 'None' }}</td>
                        <td class="px-5 py-3.5 font-mono">{{ $c->rate_limit }} req/min</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $c->status === 'active' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }}">
                                {{ $c->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-400 text-[11px]">{{ $c->created_at->format('M d, Y') }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <form action="{{ route('admin.api-clients.toggle', $c) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-xl text-[11px] font-bold transition {{ $c->status === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                    {{ $c->status === 'active' ? 'Revoke' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                            No external project API clients registered yet. Click "Generate New API Key" above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $clients->links() }}
        </div>
    </div>

</div>
@endsection
