<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Payment Successful — Barakah</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col justify-between selection:bg-teal-600 selection:text-white p-4">

    <div class="max-w-md w-full mx-auto my-auto py-8">
        
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 text-center space-y-5">
            
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center font-bold text-3xl shadow-sm">
                ✓
            </div>

            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Payment Complete!</h2>
                <p class="text-xs text-slate-500 mt-1">Paid to {{ $payment->apiClient->name ?? 'Merchant' }}</p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-xs text-slate-400 block mb-0.5 font-bold uppercase tracking-wider text-[10px]">Paid Amount</span>
                <div class="text-3xl font-extrabold text-emerald-700 font-mono">
                    ৳ {{ number_format($payment->amount, 2) }} BDT
                </div>
                @if($payment->transaction)
                <p class="text-[11px] font-mono text-slate-500 mt-1.5 font-bold">Ref: {{ $payment->transaction->reference }}</p>
                @endif
                <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    0% Gateway Fee
                </span>
            </div>

            @if($externalRedirect)
            <div class="pt-2">
                <a href="{{ $externalRedirect }}"
                   class="inline-block w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 text-white font-extrabold text-xs shadow-md shadow-teal-700/20 transition">
                    Return to Application &rarr;
                </a>
            </div>
            @else
            <div class="pt-2">
                <a href="{{ route('dashboard') }}"
                   class="inline-block w-full py-3.5 px-4 rounded-2xl bg-teal-700 hover:bg-teal-600 text-white font-extrabold text-xs shadow-sm transition">
                    Go to Barakah Dashboard
                </a>
            </div>
            @endif

        </div>

        <div class="mt-4 text-center text-[11px] text-slate-400">
            Powered by <span class="text-emerald-700 font-bold">Relic Software</span>
        </div>
    </div>

</body>
</html>
