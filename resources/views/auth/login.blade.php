<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>{{ __('Sign In') }} — {{ __('Barakah') }}</title>
    
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
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="/images/logo.png" alt="Barakah" class="h-12 w-auto mx-auto object-contain rounded-2xl shadow-sm mb-2">
            </a>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ __('Barakah') }}</h1>
            <p class="text-xs text-teal-700 font-semibold mt-0.5">{{ __('Your Institutional Financial Wallet') }}</p>
        </div>

        <!-- Login Card (Clean White) -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50">
            <h2 class="text-base font-extrabold text-slate-900 mb-1">{{ __('Account Sign In') }}</h2>
            <p class="text-xs text-slate-500 mb-5">{{ __('Enter your institutional Mobile Phone or Email') }}</p>

            @if(session('info'))
            <div class="mb-4 p-3 rounded-2xl bg-teal-50 border border-teal-200 text-teal-800 text-xs font-medium">
                {{ session('info') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4" x-data="submitOnce" @submit="handleSubmit($event)">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        {{ __('Phone / Email / Account ID') }}
                    </label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                           placeholder="{{ __('Phone / Email / Account ID') }}"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm font-semibold focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        {{ __('Password') }}
                    </label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm font-semibold focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded bg-slate-100 border-slate-300 text-teal-600 focus:ring-0">
                        <span>{{ __('Remember device') }}</span>
                    </label>
                </div>

                <button type="submit" :disabled="submitting"
                        class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-lg shadow-teal-700/20 transition transform active:scale-[0.98] disabled:opacity-50">
                    <span x-show="!submitting">{{ __('Continue to Wallet') }}</span>
                    <span x-show="submitting">...</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
                {{ __('New member in the institute?') }} 
                <a href="{{ route('register') }}" class="text-teal-700 hover:text-teal-900 font-extrabold ml-1">{{ __('Create Account') }}</a>
            </div>
        </div>

        <div class="mt-6 text-center text-[11px] text-slate-400">
            {{ __('Powered by') }} <span class="text-emerald-700 font-bold">Relic Software</span>
        </div>
    </div>

</body>
</html>