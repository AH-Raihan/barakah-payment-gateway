@extends('layouts.admin')

@section('title', 'Bulk Onboarding Results')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Users List
        </a>
    </div>

    <!-- Summary Banner (Clean Light Theme) -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Bulk Batch Completed</h1>
            <p class="text-xs text-slate-500 mt-1">Successfully created <strong class="text-emerald-700">{{ $createdCount }}</strong> accounts</p>
        </div>
        <div class="text-right text-xs text-slate-500">
            <span>Default PIN: <strong class="text-slate-900 font-mono">{{ $defaultPin }}</strong></span><br>
            <span>Default Pass: <strong class="text-slate-900 font-mono">{{ $defaultPassword }}</strong></span>
        </div>
    </div>

    <!-- Error Messages if Any -->
    @if(!empty($errors))
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
        <h4 class="font-bold text-rose-900">Encountered Issues ({{ count($errors) }}):</h4>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Created Accounts Table (Clean Light Mode) -->
    @if(!empty($createdList))
    <div class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Created Institutional Accounts</h3>
            <span class="text-[11px] text-teal-700 font-bold">{{ count($createdList) }} new members</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Account ID</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Initial Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($createdList as $acc)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3 font-bold text-slate-900">{{ $acc['name'] }}</td>
                        <td class="px-5 py-3 font-mono text-teal-700 font-bold">{{ $acc['account_id'] }}</td>
                        <td class="px-5 py-3 font-mono text-slate-500">{{ $acc['phone'] }} • {{ $acc['email'] }}</td>
                        <td class="px-5 py-3 font-mono text-emerald-700 font-bold">৳ {{ number_format($acc['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
