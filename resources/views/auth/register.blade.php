<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0d9488">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/images/icons/icon-192x192.png">
    <title>{{ __('Create Account') }} — {{ __('Barakah') }}</title>
    
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

    <!-- Top Language Switcher -->
    <div class="max-w-md w-full mx-auto flex items-center justify-between pt-2">
        <a href="{{ route('home') }}" class="text-xs font-semibold text-teal-800 hover:text-teal-950 flex items-center gap-1">
            &larr; {{ __('Home') }}
        </a>
        <div class="inline-flex items-center rounded-xl bg-white p-1 border border-slate-200 text-xs font-bold shadow-xs">
            <a href="{{ route('locale.switch', 'en') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-teal-700 text-white' : 'text-slate-600' }}">EN</a>
            <a href="{{ route('locale.switch', 'bn') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'bn' ? 'bg-teal-700 text-white' : 'text-slate-600' }}">বাং</a>
            <a href="{{ route('locale.switch', 'ar') }}" class="px-2 py-0.5 rounded-lg transition {{ app()->getLocale() === 'ar' ? 'bg-teal-700 text-white' : 'text-slate-600' }}">عربي</a>
        </div>
    </div>

    <div class="max-w-md w-full mx-auto my-auto py-6">
        
        <!-- Uploaded Logo Header -->
        <div class="text-center mb-5">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="/images/logo.png" alt="Barakah" class="h-12 w-auto mx-auto object-contain rounded-2xl shadow-sm mb-2">
            </a>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ __('Create Account') }}</h1>
            <p class="text-xs text-teal-700 font-semibold mt-0.5">{{ __('Your Institutional Financial Wallet') }}</p>
        </div>

        <!-- Registration Card (Clean White) -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-xl shadow-slate-200/50 space-y-4">

            @if($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-3" x-data="submitOnce" @submit="handleSubmit($event)">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Full Name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="{{ __('Full Name') }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Institutional Email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="{{ __('Institutional Email') }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Mobile Phone (BD)') }}</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                           placeholder="01712345678"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Password') }}</label>
                        <input type="password" name="password" required placeholder="Min 6 chars"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" required placeholder="Repeat"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs font-semibold focus:outline-none focus:border-teal-500 focus:bg-white transition">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="submitting"
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-lg shadow-teal-700/20 transition transform active:scale-[0.98]">
                        <span x-show="!submitting">{{ __('Create Account & Set PIN') }}</span>
                        <span x-show="submitting">...</span>
                    </button>
                </div>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                {{ __('Already have an account?') }} 
                <a href="{{ route('login') }}" class="text-teal-700 hover:text-teal-900 font-extrabold ml-1">{{ __('Sign In') }}</a>
            </div>
        </div>

        <div class="mt-4 text-center text-[11px] text-slate-400">
            {{ __('Powered by') }} <span class="text-emerald-700 font-bold">Relic Software</span>
        </div>
    </div>

</body>
</html>