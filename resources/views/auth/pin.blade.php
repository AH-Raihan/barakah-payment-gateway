<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Enter PIN — Barakah Wallet</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-arabic { font-family: 'Amiri', serif; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col justify-between selection:bg-teal-600 selection:text-white p-4 no-tap-highlight"
      x-data="pinEntry()">

    <!-- Top Status / Switch User -->
    <div class="max-w-xs w-full mx-auto pt-2 flex items-center justify-between text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <img src="/images/logo.png" alt="Barakah" class="h-7 w-auto object-contain">
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="font-bold text-rose-600 hover:text-rose-800 transition">Switch User</button>
        </form>
    </div>

    <!-- Main PIN Pad Center -->
    <div class="max-w-xs w-full mx-auto my-auto flex flex-col items-center text-center">
        
        <!-- User Avatar & Greetings -->
        <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-teal-600 to-emerald-500 p-0.5 shadow-lg shadow-teal-700/20 mb-2">
            <div class="w-full h-full rounded-full bg-white flex items-center justify-center text-teal-800 font-extrabold text-xl">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
        </div>

        <h2 class="text-base font-extrabold text-slate-900 tracking-tight">{{ auth()->user()->name }}</h2>
        <p class="text-xs font-mono text-teal-700 font-bold mb-3">{{ auth()->user()->phone ?? auth()->user()->account_id }}</p>

        <p class="text-xs font-semibold text-slate-500 mb-5">Enter your 5-digit PIN</p>

        <!-- Lockout Notice if applicable -->
        @if($isLocked)
        <div class="mb-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
            Account locked due to failed attempts. Retry in <strong>{{ $lockMinutes }} min</strong>.
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
            {{ session('error') }}
        </div>
        @endif

        <!-- 5 PIN Dots Indicator -->
        <div class="flex items-center justify-center space-x-4 mb-7">
            <template x-for="i in 5" :key="i">
                <div class="w-4 h-4 rounded-full border-2 transition-all duration-200"
                     :class="pin.length >= i ? 'bg-teal-700 border-teal-700 scale-125 shadow-md shadow-teal-700/40' : 'bg-white border-slate-300'">
                </div>
            </template>
        </div>

        <!-- Hidden Form for submission -->
        <form id="pin-form" action="{{ route('pin.verify') }}" method="POST">
            @csrf
            <input type="hidden" name="pin" :value="pin">
        </form>

        <!-- Android Style Clean Touch Keypad (Light Theme) -->
        <div class="grid grid-cols-3 gap-2.5 w-full max-w-[280px]">
            <template x-for="n in [1, 2, 3, 4, 5, 6, 7, 8, 9]" :key="n">
                <button type="button" @click="press(n)"
                        class="numpad-btn h-14 rounded-2xl bg-white border border-slate-200 text-xl font-extrabold text-slate-800 flex items-center justify-center shadow-sm hover:border-teal-500 active:bg-teal-50">
                    <span x-text="n"></span>
                </button>
            </template>
            
            <!-- Clear Button -->
            <button type="button" @click="clear()"
                    class="numpad-btn h-14 rounded-2xl bg-slate-100 text-xs font-bold text-slate-500 flex items-center justify-center hover:bg-slate-200">
                Clear
            </button>
            
            <!-- Zero -->
            <button type="button" @click="press('0')"
                    class="numpad-btn h-14 rounded-2xl bg-white border border-slate-200 text-xl font-extrabold text-slate-800 flex items-center justify-center shadow-sm hover:border-teal-500 active:bg-teal-50">
                0
            </button>
            
            <!-- Backspace -->
            <button type="button" @click="backspace()"
                    class="numpad-btn h-14 rounded-2xl bg-slate-100 text-rose-600 flex items-center justify-center hover:bg-rose-50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-7.172a2 2 0 00-1.414.586L3 12z"/></svg>
            </button>
        </div>

    </div>

    <!-- Footer -->
    <div class="max-w-xs w-full mx-auto pb-3 text-center text-[11px] text-slate-400">
        Institutional Financial Security • Barakah
    </div>

    <script>
        function pinEntry() {
            return {
                pin: '',
                isSubmitting: false,
                press(digit) {
                    if (this.pin.length < 5 && !this.isSubmitting) {
                        this.pin += digit.toString();
                        if (this.pin.length === 5) {
                            this.submit();
                        }
                    }
                },
                backspace() {
                    if (this.pin.length > 0 && !this.isSubmitting) {
                        this.pin = this.pin.slice(0, -1);
                    }
                },
                clear() {
                    if (!this.isSubmitting) {
                        this.pin = '';
                    }
                },
                submit() {
                    this.isSubmitting = true;
                    setTimeout(() => {
                        document.getElementById('pin-form').submit();
                    }, 200);
                }
            }
        }
    </script>
</body>
</html>
