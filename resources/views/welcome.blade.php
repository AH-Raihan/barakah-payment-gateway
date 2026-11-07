<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0d9488">
    <meta name="description" content="Barakah — Institutional Financial Wallet & Merchant Payment Gateway powered by Relic Software">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/images/icons/icon-192x192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="application-name" content="Barakah">
    <meta name="apple-mobile-web-app-title" content="Barakah">
    <title>{{ __('Barakah') }} — {{ __('Your Institutional Financial Wallet') }}</title>
    
    <!-- Fonts -->
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
        .font-bangla { font-family: 'Hind Siliguri', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col justify-between selection:bg-teal-600 selection:text-white">

    <!-- Navigation Header -->
    <header class="max-w-5xl mx-auto w-full px-6 py-4 flex items-center justify-between border-b border-slate-200/80 bg-white shadow-xs">
        <a href="{{ route('home') }}" class="flex items-center space-x-2">
            <img src="/images/logo.png" alt="Barakah Logo" class="h-10 w-auto object-contain rounded-xl shadow-xs">
        </a>

        <div class="flex items-center space-x-3">
            <!-- Language Switcher Pill -->
            <div class="inline-flex items-center rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-bold">
                <a href="{{ route('locale.switch', 'en') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-teal-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">EN</a>
                <a href="{{ route('locale.switch', 'bn') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'bn' ? 'bg-teal-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">বাং</a>
                <a href="{{ route('locale.switch', 'ar') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'ar' ? 'bg-teal-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">عربي</a>
            </div>

            <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-teal-800 hover:text-teal-950 transition">{{ __('Sign In') }}</a>
            @if(\App\Models\SystemSetting::isRegistrationEnabled())
                <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-teal-700 hover:bg-teal-600 text-white shadow-sm transition">{{ __('Get Started') }}</a>
            @endif
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-6 py-12 text-center flex-1 flex flex-col justify-center items-center">
        
        <!-- Institutional Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-teal-50 border border-teal-200 text-teal-900 text-xs font-bold mb-5 shadow-xs">
            <span class="w-2 h-2 rounded-full bg-teal-600 animate-pulse"></span>
            {{ __('Your Institutional Financial Wallet') }}
        </div>

        <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
            {{ __('Barakah') }} <br class="hidden sm:inline">
            <span class="bg-gradient-to-r from-teal-700 via-emerald-600 to-teal-800 bg-clip-text text-transparent">
                {{ __('Your Institutional Financial Wallet') }}
            </span>
        </h1>

        <p class="mt-4 text-sm sm:text-base text-slate-600 max-w-xl mx-auto leading-relaxed">
            {{ __('A fast, modern institutional financial wallet designed for our campus. Secure transfers, 5-digit PIN protection, real scannable QR codes, and seamless digital transactions.') }}
        </p>

        <!-- CTA Buttons -->
        <div class="mt-7 flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 text-white font-extrabold text-sm shadow-md shadow-teal-800/20 hover:opacity-95 transition transform active:scale-95">
                {{ __('Open Barakah Wallet') }}
            </a>
            @if(\App\Models\SystemSetting::isRegistrationEnabled())
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-white border border-slate-300 text-slate-800 font-bold text-sm hover:bg-slate-50 shadow-xs transition">
                    {{ __('Create Account') }}
                </a>
            @endif
        </div>

        <!-- User-Helpful Feature Cards (Clean, No Tech/Admin Jargon) -->
        <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-3 w-full">
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs text-left">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold mb-2">⚡</div>
                <h4 class="font-extrabold text-slate-900 text-xs">{{ __('Instant Transfers') }}</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ __('Fast peer-to-peer campus sending') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs text-left">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold mb-2">🔒</div>
                <h4 class="font-extrabold text-slate-900 text-xs">{{ __('5-Digit PIN') }}</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ __('Strong security on open & verify') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs text-left">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold mb-2">📷</div>
                <h4 class="font-extrabold text-slate-900 text-xs">{{ __('Scan & Pay') }}</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ __('Easy camera QR code scanning') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs text-left">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold mb-2">📄</div>
                <h4 class="font-extrabold text-slate-900 text-xs">{{ __('Digital Receipts') }}</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ __('Clear transaction history & logs') }}</p>
            </div>
        </div>

    </main>

    <!-- Footer with Powered by Relic Software -->
    <footer class="border-t border-slate-200 bg-white py-5 text-center text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                © {{ date('Y') }} {{ __('Barakah') }} — {{ __('Your Institutional Financial Wallet') }}.
            </div>
            <div>
                {{ __('Powered by') }} <strong class="text-emerald-700 font-bold">Relic Software</strong>
            </div>
        </div>
    </footer>

    <!-- Service Worker Registration for PWABuilder & PWA installation -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => {
                        console.log('[Barakah PWA] Service Worker active:', reg.scope);
                    })
                    .catch((err) => {
                        console.warn('[Barakah PWA] Service Worker registration failed:', err);
                    });
            });
        }
    </script>
</body>
</html>