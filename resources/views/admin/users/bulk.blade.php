@extends('layouts.admin')

@section('title', 'Bulk Employee Account Creation')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:text-teal-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Users List
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
            Bulk Employee Onboarding <span class="font-arabic text-teal-700 text-xl">بركة</span>
        </h1>
        <p class="text-xs text-slate-500 mt-1">Batch create institutional accounts, assign primary wallets, and credit initial balances</p>
    </div>

    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-6">
        
        <!-- Format Instructions & Sample -->
        <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 text-xs text-teal-950 space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-teal-950 uppercase tracking-wider text-[11px]">CSV / Text Format Specification</span>
                <span class="text-[10px] text-teal-700 font-mono font-bold">1 record per line</span>
            </div>
            <p class="text-slate-700 leading-relaxed">
                Provide lines separated by commas in this format: <br>
                <code class="text-teal-900 font-mono font-bold bg-white px-2 py-0.5 rounded border border-teal-200 text-[11px]">Name, Email, Phone, Initial Balance, Role</code>
            </p>
            <div class="p-3 bg-white rounded-xl font-mono text-[11px] text-slate-800 border border-teal-100 overflow-x-auto shadow-xs">
Abdul Karim, karim@institute.edu.bd, 01711000101, 5000, user<br>
Fatima Begum, fatima@institute.edu.bd, 01711000102, 3000, user<br>
Campus Canteen Agent, canteen@institute.edu.bd, 01711000103, 50000, agent
            </div>
        </div>

        <form action="{{ route('admin.users.bulk.process') }}" method="POST" enctype="multipart/form-data" class="space-y-5" x-data="submitOnce" @submit="handleSubmit($event)">
            @csrf

            <!-- Upload CSV File Option -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Option A: Upload CSV File
                </label>
                <input type="file" name="csv_file" accept=".csv,.txt"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-700 file:text-white hover:file:bg-teal-600 cursor-pointer bg-slate-50 border border-slate-300 rounded-2xl p-2 transition">
            </div>

            <div class="relative flex items-center justify-center">
                <div class="border-t border-slate-200 w-full"></div>
                <span class="bg-white px-3 text-[11px] text-slate-400 font-bold uppercase shrink-0">OR</span>
                <div class="border-t border-slate-200 w-full"></div>
            </div>

            <!-- Paste Lines Option -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Option B: Paste Raw Employee Records
                </label>
                <textarea name="bulk_data" rows="5"
                          placeholder="Name, Email, Phone, Initial Balance, Role&#10;Muhammad Ali, ali@institute.edu.bd, 01800000010, 2000, user"
                          class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-xs font-mono focus:outline-none focus:border-teal-500 focus:bg-white transition"></textarea>
            </div>

            <!-- Default Credentials for the batch -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Default Account Password
                    </label>
                    <input type="text" name="default_password" value="Barakah@123" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-mono focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Default 5-Digit PIN
                    </label>
                    <input type="text" name="default_pin" value="12345" maxlength="5" pattern="[0-9]{5}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-mono text-center tracking-widest focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" :disabled="submitting"
                        class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-md shadow-teal-700/20 transition transform active:scale-[0.98]">
                    <span x-show="!submitting">Process Batch & Create Accounts</span>
                    <span x-show="submitting">Processing Batch...</span>
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
