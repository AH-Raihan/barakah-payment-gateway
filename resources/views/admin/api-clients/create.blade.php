@extends('layouts.admin')

@section('title', 'Register New External Project')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.api-clients.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to API Clients
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Register External Project</h1>
        <p class="text-xs text-slate-500 mt-1">Generate API credentials for your other website or application</p>
    </div>

    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-4">
        
        <form action="{{ route('admin.api-clients.store') }}" method="POST" class="space-y-4" x-data="submitOnce" @submit="handleSubmit($event)">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Project / App Name</label>
                <input type="text" name="name" required placeholder="Project or application name"
                       class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Webhook URL (Optional)</label>
                <input type="url" name="webhook_url" placeholder="https://myproject.com/api/barakah/webhook"
                       class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-mono focus:outline-none focus:border-teal-500 focus:bg-white transition">
                <p class="text-[11px] text-slate-400 mt-1">Barakah will dispatch payment event payloads to this URL.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Rate Limit (Requests per Minute)</label>
                <input type="number" name="rate_limit" value="60" min="10" max="1000" required
                       class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-mono font-bold focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <div class="pt-2">
                <button type="submit" :disabled="submitting"
                        class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-md shadow-teal-700/20 transition transform active:scale-[0.98]">
                    Generate API Credentials
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
