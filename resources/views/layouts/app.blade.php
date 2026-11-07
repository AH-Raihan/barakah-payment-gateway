<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d9488">
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', __('Barakah')) — {{ __('Your Institutional Financial Wallet') }}</title>
    
    <!-- Fonts (Hind Siliguri for Bengali, Amiri for Arabic, Plus Jakarta Sans for English) -->
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
<body class="h-full text-slate-800 antialiased selection:bg-teal-600 selection:text-white bg-slate-100/50">
    
    <!-- Desktop & Mobile Responsive Frame -->
    <div class="min-h-full flex flex-col lg:flex-row">
        
        <!-- Desktop Sidebar (Visible on LG screens and up) -->
        <aside class="hidden lg:flex lg:flex-col lg:w-72 bg-gradient-to-b from-teal-900 via-teal-950 to-slate-950 text-white shrink-0 border-r border-teal-800/40">
            <!-- Brand with User's Uploaded Logo -->
            <div class="p-6 border-b border-teal-800/40 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <img src="/images/logo.png" alt="Barakah Logo" class="h-10 w-auto object-contain rounded-xl shadow-md">
                </a>
                
                <!-- Desktop Language Switcher -->
                <div class="inline-flex items-center rounded-xl bg-teal-950/60 p-1 border border-teal-700/50 text-[11px] font-bold">
                    <a href="{{ route('locale.switch', 'en') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-teal-600 text-white shadow-xs' : 'text-teal-200 hover:text-white' }}">EN</a>
                    <a href="{{ route('locale.switch', 'bn') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'bn' ? 'bg-teal-600 text-white shadow-xs' : 'text-teal-200 hover:text-white' }}">বাং</a>
                    <a href="{{ route('locale.switch', 'ar') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'ar' ? 'bg-teal-600 text-white shadow-xs' : 'text-teal-200 hover:text-white' }}">عربي</a>
                </div>
            </div>

            <!-- User Info Summary -->
            @auth
            <div class="px-5 py-4 mx-4 my-4 rounded-2xl bg-teal-800/40 border border-teal-700/50 backdrop-blur-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-300 font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs font-mono text-emerald-400 truncate">{{ auth()->user()->phone ?? auth()->user()->account_id }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-teal-700/40 flex items-center justify-between text-xs text-teal-200">
                    <span>Role: <strong class="capitalize text-white">{{ auth()->user()->role }}</strong></span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Active</span>
                </div>
            </div>
            @endauth

            <!-- Desktop Navigation Links -->
            <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto custom-scroll">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition {{ request()->routeIs('dashboard') ? 'bg-teal-600 text-white shadow-md shadow-teal-900/40' : 'text-teal-200 hover:bg-teal-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('wallet.send') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition {{ request()->routeIs('wallet.send*') ? 'bg-teal-600 text-white shadow-md shadow-teal-900/40' : 'text-teal-200 hover:bg-teal-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>{{ __('Send Money') }}</span>
                </a>
                <a href="{{ route('wallet.cashout') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition {{ request()->routeIs('wallet.cashout*') ? 'bg-teal-600 text-white shadow-md shadow-teal-900/40' : 'text-teal-200 hover:bg-teal-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>{{ __('Cash Out') }}</span>
                </a>
                <a href="{{ route('wallet.qr') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition {{ request()->routeIs('wallet.qr') ? 'bg-teal-600 text-white shadow-md shadow-teal-900/40' : 'text-teal-200 hover:bg-teal-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>{{ __('My Account QR') }}</span>
                </a>
                <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition {{ request()->routeIs('transactions*') ? 'bg-teal-600 text-white shadow-md shadow-teal-900/40' : 'text-teal-200 hover:bg-teal-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('History') }}</span>
                </a>
                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition {{ request()->routeIs('profile*') ? 'bg-teal-600 text-white shadow-md shadow-teal-900/40' : 'text-teal-200 hover:bg-teal-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>{{ __('Profile & Security') }}</span>
                </a>

                @if(auth()->check() && auth()->user()->isAdmin())
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-emerald-400 uppercase tracking-wider">{{ __('Administration') }}</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl font-bold text-sm text-emerald-300 hover:bg-emerald-950/60 transition">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>{{ __('Admin Portal') }}</span>
                </a>
                @endif
            </nav>

            <!-- Desktop Footer (Powered by Relic Software) -->
            <div class="p-4 border-t border-teal-800/40 text-center">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 px-3 text-xs font-bold text-rose-300 hover:bg-rose-950/40 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('Log Out') }}
                    </button>
                </form>
                <div class="mt-3 text-[11px] text-teal-300/70 font-medium">
                    {{ __('Powered by') }} <span class="text-emerald-400 font-bold">Relic Software</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-screen max-w-2xl lg:max-w-none mx-auto w-full">
            
            <!-- Mobile Top App Header (Android Style with Logo & Language Switcher) -->
            <header class="lg:hidden sticky top-0 z-40 bg-teal-800 text-white shadow-md">
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <img src="/images/logo.png" alt="Barakah" class="h-9 w-auto object-contain rounded-lg">
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Mobile Language Switcher -->
                        <div class="inline-flex items-center rounded-xl bg-teal-900/80 p-0.5 border border-teal-600/40 text-[10px] font-bold">
                            <a href="{{ route('locale.switch', 'en') }}" class="px-1.5 py-0.5 rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-teal-500 text-white' : 'text-teal-200' }}">EN</a>
                            <a href="{{ route('locale.switch', 'bn') }}" class="px-1.5 py-0.5 rounded-lg transition {{ app()->getLocale() === 'bn' ? 'bg-teal-500 text-white' : 'text-teal-200' }}">বাং</a>
                            <a href="{{ route('locale.switch', 'ar') }}" class="px-1.5 py-0.5 rounded-lg transition {{ app()->getLocale() === 'ar' ? 'bg-teal-500 text-white' : 'text-teal-200' }}">عربي</a>
                        </div>

                        @auth
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
                                Admin
                            </a>
                            @endif
                            <a href="{{ route('profile') }}" class="w-8 h-8 rounded-full bg-teal-700 border border-teal-500/50 flex items-center justify-center text-xs font-bold text-emerald-200 shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Alerts / Toast Messages -->
            <div class="px-4 pt-3 lg:px-8">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-3 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-700 hover:text-emerald-950 font-bold">&times;</button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mb-3 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-700 hover:text-rose-950 font-bold">&times;</button>
                </div>
                @endif

                @if(session('info'))
                <div x-data="{ show: true }" x-show="show" class="mb-3 p-3.5 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button @click="show = false" class="text-teal-700 hover:text-teal-950 font-bold">&times;</button>
                </div>
                @endif
            </div>

            <!-- Page Body Content -->
            <main class="flex-1 pb-24 lg:pb-8 px-4 lg:px-8 max-w-4xl w-full mx-auto">
                @yield('content')
            </main>

            <!-- Mobile Android Bottom Navbar (Fixed at Bottom, lg:hidden) -->
            <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-50 shadow-lg">
                <div class="grid grid-cols-4 h-16 max-w-md mx-auto">
                    <!-- Home -->
                    <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center transition-colors {{ request()->routeIs('dashboard') ? 'text-teal-700 font-extrabold' : 'text-slate-500 hover:text-teal-600' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span class="text-[11px] mt-0.5">{{ __('Home') }}</span>
                    </a>
                    
                    <!-- Send Money -->
                    <a href="{{ route('wallet.send') }}" class="flex flex-col items-center justify-center transition-colors {{ request()->routeIs('wallet.send*') ? 'text-teal-700 font-extrabold' : 'text-slate-500 hover:text-teal-600' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span class="text-[11px] mt-0.5">{{ __('Send Money') }}</span>
                    </a>

                    <!-- History -->
                    <a href="{{ route('transactions.index') }}" class="flex flex-col items-center justify-center transition-colors {{ request()->routeIs('transactions*') ? 'text-teal-700 font-extrabold' : 'text-slate-500 hover:text-teal-600' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[11px] mt-0.5">{{ __('History') }}</span>
                    </a>

                    <!-- Profile -->
                    <a href="{{ route('profile') }}" class="flex flex-col items-center justify-center transition-colors {{ request()->routeIs('profile*') ? 'text-teal-700 font-extrabold' : 'text-slate-500 hover:text-teal-600' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="text-[11px] mt-0.5">{{ __('Profile') }}</span>
                    </a>
                </div>
            </nav>

        </div>
    </div>

    @stack('scripts')
</body>
</html>