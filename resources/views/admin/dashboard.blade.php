@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Top Greeting & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                Institutional Dashboard <span class="font-arabic text-teal-700 text-xl">بركة</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Real-time overview of the Barakah institutional wallet network</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.deposits.create') }}" class="px-3.5 py-2 rounded-2xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Balance
            </a>
            <a href="{{ route('admin.users.bulk') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-slate-50 text-teal-800 text-xs font-bold border border-slate-200 shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Bulk Onboard
            </a>
        </div>
    </div>

    <!-- Registration Restriction Alert & Quick Toggle -->
    <div class="p-4 rounded-3xl bg-white border border-slate-200/90 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-lg {{ $registrationEnabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                @if($registrationEnabled)
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold text-slate-900">Public Registration Status</h3>
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $registrationEnabled ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $registrationEnabled ? 'OPEN' : 'RESTRICTED' }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $registrationEnabled ? 'New campus members can self-register on the landing page.' : 'Registration is restricted. Only admins can register new users or upload via CSV.' }}
                </p>
            </div>
        </div>

        <form action="{{ route('admin.settings.toggle-registration') }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Are you sure you want to {{ $registrationEnabled ? 'restrict' : 'enable' }} registration?')"
                    class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs {{ $registrationEnabled ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-700/20' }}">
                <span>{{ $registrationEnabled ? 'Restrict Registration' : 'Open Registration' }}</span>
            </button>
        </form>
    </div>

    <!-- Core Financial Stats Cards Grid (Clean White Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Circulating Balance -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-bold uppercase tracking-wider text-[11px]">Circulating Balance</span>
                <span class="p-1.5 rounded-xl bg-teal-50 text-teal-700 font-bold">৳</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono">
                ৳ {{ number_format($totalSystemBalance, 2) }}
            </div>
            <p class="text-[11px] text-teal-700 font-bold mt-1">Total funds held in wallets</p>
        </div>

        <!-- Total Admin Injected Balance -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-bold uppercase tracking-wider text-[11px]">Injected Funds</span>
                <span class="p-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold">+৳</span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-700 font-mono">
                ৳ {{ number_format($totalDepositedAmount, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">{{ $totalDepositsCount }} admin deposit events</p>
        </div>

        <!-- Total Tx Volume -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-bold uppercase tracking-wider text-[11px]">Total Tx Volume</span>
                <span class="p-1.5 rounded-xl bg-sky-50 text-sky-700 font-bold">⚡</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono">
                ৳ {{ number_format($totalTxVolume, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">{{ number_format($totalTxCount) }} completed transactions</p>
        </div>

        <!-- Total Users -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-bold uppercase tracking-wider text-[11px]">Institutional Users</span>
                <span class="p-1.5 rounded-xl bg-indigo-50 text-indigo-700 font-bold">👥</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono">
                {{ $totalUsers }}
            </div>
            <p class="text-[11px] text-emerald-700 font-bold mt-1">{{ $activeUsers }} Active • {{ $totalAgents }} Agents</p>
        </div>
    </div>

    <!-- 7-Day Activity Chart Visualization -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">7-Day Transfer Volume</h3>
                <p class="text-xs text-slate-500">Daily transaction totals across the institution</p>
            </div>
            <div class="text-xs text-slate-600">
                Today: <strong class="text-emerald-700 font-mono font-extrabold">৳ {{ number_format($todayTxVolume, 2) }}</strong> ({{ $todayTxCount }} txns)
            </div>
        </div>

        <div class="grid grid-cols-7 gap-2 pt-4 items-end h-44 border-b border-slate-100 pb-2">
            @php $maxVol = max(1, $days->max('volume')); @endphp
            @foreach($days as $item)
                @php $heightPercent = min(100, max(8, ($item['volume'] / $maxVol) * 100)); @endphp
                <div class="flex flex-col items-center h-full justify-end group">
                    <span class="text-[10px] font-mono text-slate-500 mb-1 opacity-0 group-hover:opacity-100 transition">
                        ৳{{ number_format($item['volume'], 0) }}
                    </span>
                    <div class="w-full max-w-[40px] rounded-t-xl bg-gradient-to-t from-teal-700 to-emerald-500 group-hover:to-teal-500 transition-all duration-300 shadow-xs"
                         style="height: {{ $heightPercent }}%;">
                    </div>
                    <span class="text-[10px] text-slate-600 mt-2 font-bold truncate w-full text-center">
                        {{ explode(',', $item['day'])[0] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Dual Split Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Admin Deposits -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Recent Admin Deposits</h3>
                    <p class="text-xs text-slate-500">Tracked balance injections</p>
                </div>
                <a href="{{ route('admin.deposits.index') }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">View All &rarr;</a>
            </div>

            @if($recentDeposits->isEmpty())
            <p class="text-xs text-slate-400 py-6 text-center">No admin deposits recorded yet.</p>
            @else
            <div class="space-y-2 text-xs">
                @foreach($recentDeposits as $dep)
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">To: {{ $dep->wallet?->user?->name ?? 'User' }}</p>
                        <p class="text-[11px] text-slate-500 font-mono">By {{ $dep->admin?->name }} • {{ $dep->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-extrabold font-mono text-emerald-700 text-sm">+৳ {{ number_format($dep->amount, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Recent Transactions Feed -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Recent Transactions</h3>
                    <p class="text-xs text-slate-500">All institutional movements</p>
                </div>
                <a href="{{ route('admin.transactions.index') }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">View All &rarr;</a>
            </div>

            @if($recentTransactions->isEmpty())
            <p class="text-xs text-slate-400 py-6 text-center">No transactions recorded yet.</p>
            @else
            <div class="space-y-2 text-xs">
                @foreach($recentTransactions as $tx)
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $tx->type) }}</p>
                        <p class="text-[11px] text-slate-500 font-mono">{{ $tx->reference }} • {{ $tx->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-extrabold font-mono text-slate-900 text-sm">৳ {{ number_format($tx->amount, 2) }}</span>
                        <span class="block text-[10px] text-emerald-700 font-bold uppercase">{{ $tx->status }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
