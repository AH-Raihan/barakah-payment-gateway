<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Expired — Barakah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col justify-between p-4">
    <div class="max-w-md w-full mx-auto my-auto py-8">
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 text-center space-y-4 shadow-xl shadow-slate-200/50">
            <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 mx-auto flex items-center justify-center font-bold text-3xl border border-amber-200">
                ⏳
            </div>
            <h2 class="text-xl font-bold text-slate-900">Payment Request Expired</h2>
            <p class="text-xs text-slate-500">This payment session has timed out. Please initiate a new payment from the merchant application.</p>
            <div class="pt-2">
                <a href="{{ url('/') }}" class="inline-block py-2.5 px-6 rounded-2xl bg-teal-700 text-white text-xs font-bold hover:bg-teal-600 transition shadow-sm">
                    Return to Barakah Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>