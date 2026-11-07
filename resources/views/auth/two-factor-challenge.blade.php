<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Two-Factor Verification — Barakah</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @if(app()->getLocale() === 'bn')
            body, button, input, select, textarea { font-family: 'Hind Siliguri', sans-serif; }
        @elseif(app()->getLocale() === 'ar')
            body, button, input, select, textarea { font-family: 'Amiri', serif; }
        @else
            body, button, input, select, textarea { font-family: 'Plus Jakarta Sans', sans-serif; }
        @endif
        .font-arabic { font-family: 'Amiri', serif; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col justify-between selection:bg-teal-600 selection:text-white p-4">

    <div class="max-w-md w-full mx-auto my-auto py-8">
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="/images/logo.png" alt="Barakah" class="h-12 w-auto mx-auto object-contain rounded-2xl shadow-sm mb-2">
            </a>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Two-Factor Authentication</h1>
            <p class="text-xs text-slate-500 mt-0.5">Enter the 6-digit code from your authenticator app</p>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50">
            @if(session('error'))
            <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('2fa.challenge') }}" method="POST" class="space-y-4" x-data="submitOnce" @submit="handleSubmit($event)">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Authentication Code</label>
                    <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required autofocus
                           placeholder="••••••"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-center tracking-widest text-2xl font-mono focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition">
                </div>

                <div class="pt-3">
                    <button type="submit" :disabled="submitting"
                            class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-lg shadow-teal-700/20 transition transform active:scale-[0.98]">
                        <span x-show="!submitting">Verify & Continue</span>
                        <span x-show="submitting">Verifying...</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center text-[11px] text-slate-400">
            Powered by <strong class="text-emerald-700 font-bold">Relic Software</strong>
        </div>
    </div>

</body>
</html>