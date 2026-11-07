<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Campus Store — Barakah Payment Gateway Demo</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col justify-between selection:bg-teal-600 selection:text-white p-4">

    <!-- Top Navigation -->
    <header class="max-w-xl mx-auto w-full pt-2 flex items-center justify-between text-xs">
        <a href="{{ route('home') }}" class="font-bold text-teal-800 hover:text-teal-950 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Home
        </a>
        <div class="flex items-center space-x-2">
            <span class="px-2.5 py-1 rounded-full bg-teal-100 text-teal-800 font-bold text-[11px]">Demo Store</span>
        </div>
    </header>

    <!-- Main Container -->
    <div class="max-w-xl w-full mx-auto my-auto py-6 space-y-5">
        
        <!-- Store Brand Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm text-center">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-700 mx-auto flex items-center justify-center font-bold text-2xl mb-3 shadow-inner">
                ☕
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Campus Institutional Cafeteria</h1>
            <p class="text-xs text-slate-500 mt-1">External Project Integration with Barakah Payment Gateway</p>
        </div>

        @if(request()->query('paid') == 1)
        <div class="p-4 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold">✓</div>
                <div>
                    <p class="font-bold text-emerald-950">Payment Received Successfully!</p>
                    <p class="text-[11px] text-emerald-800 mt-0.5">Your order has been placed. Transaction verified via Barakah Gateway.</p>
                </div>
            </div>
            <a href="{{ route('payment.demo') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-950 underline shrink-0 ml-2">New Order</a>
        </div>
        @endif

        <!-- Menu / Order Selection -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Select Demo Order Items</h2>
            
            <form action="{{ route('payment.demo.create') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Presets -->
                <div class="space-y-2">
                    <label class="block p-3.5 rounded-2xl border border-slate-200 hover:border-teal-500 cursor-pointer transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="item_preset" value="combo1" checked
                                       onclick="document.getElementById('demo-amount').value = '180'; document.getElementById('demo-desc').value = 'Cafeteria Lunch Combo & Mineral Water'"
                                       class="text-teal-600 focus:ring-0">
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Cafeteria Lunch Combo & Water</p>
                                    <p class="text-[11px] text-slate-400">Chicken Biryani + Mineral Water</p>
                                </div>
                            </div>
                            <span class="font-extrabold text-sm font-mono text-slate-900">৳ 180.00</span>
                        </div>
                    </label>

                    <label class="block p-3.5 rounded-2xl border border-slate-200 hover:border-teal-500 cursor-pointer transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="item_preset" value="combo2"
                                       onclick="document.getElementById('demo-amount').value = '65'; document.getElementById('demo-desc').value = 'Coffee & Croissant Snack'"
                                       class="text-teal-600 focus:ring-0">
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Coffee & Croissant Snack</p>
                                    <p class="text-[11px] text-slate-400">Espresso + Fresh Pastry</p>
                                </div>
                            </div>
                            <span class="font-extrabold text-sm font-mono text-slate-900">৳ 65.00</span>
                        </div>
                    </label>

                    <label class="block p-3.5 rounded-2xl border border-slate-200 hover:border-teal-500 cursor-pointer transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="item_preset" value="combo3"
                                       onclick="document.getElementById('demo-amount').value = '350'; document.getElementById('demo-desc').value = 'Campus Library Semester Pass'"
                                       class="text-teal-600 focus:ring-0">
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Campus Library Semester Pass</p>
                                    <p class="text-[11px] text-slate-400">Unlimited Study Pass & Book Borrowing</p>
                                </div>
                            </div>
                            <span class="font-extrabold text-sm font-mono text-slate-900">৳ 350.00</span>
                        </div>
                    </label>
                </div>

                <!-- Custom Amount Inputs -->
                <div class="pt-2 border-t border-slate-100 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item Description</label>
                        <input type="text" id="demo-desc" name="description" value="Cafeteria Lunch Combo & Mineral Water" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payable Amount (BDT)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-2.5 text-base font-bold text-slate-400">৳</span>
                            <input type="number" id="demo-amount" step="any" min="1" name="amount" value="180" required
                                   class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm font-extrabold font-mono focus:outline-none focus:border-teal-500 focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <!-- Checkout CTA -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-md shadow-teal-800/20 transition transform active:scale-[0.98] flex items-center justify-center gap-2">
                        <img src="/images/logo-icon.png" alt="Barakah" class="h-5 w-auto">
                        <span>Pay with Barakah Wallet</span>
                    </button>
                    <p class="text-[11px] text-slate-400 text-center mt-2">Redirects to Barakah secure 5-digit PIN web checkout</p>
                </div>
            </form>
        </div>

    </div>

    <!-- Footer -->
    <footer class="max-w-xl mx-auto w-full text-center text-[11px] text-slate-400 pb-2">
        Powered by <strong class="text-emerald-700 font-bold">Relic Software</strong> • Barakah Payment Gateway
    </footer>

</body>
</html>
