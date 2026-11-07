@extends('layouts.admin')

@section('title', 'API Credentials Generated')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto" x-data="{ copiedKey: false, copiedSecret: false }">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.api-clients.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to API Clients
        </a>
    </div>

    <!-- Alert Banner -->
    <div class="p-5 rounded-3xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1.5">
        <h3 class="font-extrabold text-amber-950 text-sm flex items-center gap-2">
            ⚠️ Save Credentials Immediately
        </h3>
        <p class="text-amber-800 leading-relaxed font-medium">
            These API credentials will never be shown again in plain text. Please copy and store them securely in your external project's config.
        </p>
    </div>

    <!-- Credentials Card -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
        <h2 class="text-base font-extrabold text-slate-900">Project: {{ $client->name }}</h2>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">API Key (X-API-KEY)</label>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between font-mono text-xs">
                <span class="text-teal-800 font-bold select-all truncate">{{ $plainApiKey }}</span>
                <button type="button" @click="navigator.clipboard.writeText('{{ $plainApiKey }}'); copiedKey = true; setTimeout(() => copiedKey = false, 2000)"
                        class="px-3.5 py-1.5 rounded-xl bg-teal-700 hover:bg-teal-600 text-white font-sans text-xs font-bold transition ml-2 shrink-0 shadow-xs">
                    <span x-text="copiedKey ? 'Copied!' : 'Copy Key'"></span>
                </button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">API Secret</label>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between font-mono text-xs">
                <span class="text-emerald-800 font-bold select-all truncate">{{ $plainApiSecret }}</span>
                <button type="button" @click="navigator.clipboard.writeText('{{ $plainApiSecret }}'); copiedSecret = true; setTimeout(() => copiedSecret = false, 2000)"
                        class="px-3.5 py-1.5 rounded-xl bg-teal-700 hover:bg-teal-600 text-white font-sans text-xs font-bold transition ml-2 shrink-0 shadow-xs">
                    <span x-text="copiedSecret ? 'Copied!' : 'Copy Secret'"></span>
                </button>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Sample Integration Code Snippet -->
        <div>
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Example Integration Request</h3>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] font-mono text-slate-800 overflow-x-auto space-y-1 shadow-xs">
                <p class="text-slate-500"># External project initiates a payment request:</p>
                <p class="text-teal-800 font-bold">curl -X POST "{{ url('/api/v1/payment/create') }}" \</p>
                <p class="pl-4 text-slate-700">-H "X-API-KEY: {{ $plainApiKey }}" \</p>
                <p class="pl-4 text-slate-700">-H "Content-Type: application/json" \</p>
                <p class="pl-4 text-slate-700">-d '{"amount": 250.00, "description": "Semester Fee / Order #1001", "callback_url": "https://myproject.com/payment/callback"}'</p>
            </div>
        </div>

        <div class="pt-2">
            <a href="{{ route('admin.api-clients.index') }}"
               class="inline-block w-full py-3.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold text-center transition">
                I Have Safely Saved These Credentials
            </a>
        </div>
    </div>

</div>
@endsection
