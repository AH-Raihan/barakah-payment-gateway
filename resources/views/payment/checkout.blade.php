<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Barakah Payment Gateway — Checkout</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col justify-between selection:bg-teal-600 selection:text-white p-4">

    <div class="max-w-md w-full mx-auto my-auto py-6" x-data="paymentCheckout('{{ $payment->uuid }}')">
        
        <!-- Header with Logo and Sandbox/Live Indicator -->
        <div class="text-center mb-5">
            <div class="flex items-center justify-center gap-2 mb-2">
                <img src="/images/logo.png" alt="Barakah" class="h-10 w-auto object-contain rounded-xl shadow-xs">
                @if($isSandbox)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 uppercase tracking-wider">
                    Sandbox
                </span>
                @else
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase tracking-wider">
                    Live
                </span>
                @endif
            </div>
            <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Institutional Payment Gateway</h1>
            <p class="text-xs text-teal-800 font-semibold">Fast, Secure Campus Checkout (SMS OTP & PIN)</p>
        </div>

        <!-- Checkout Card (Clean White Light Theme) -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-xl shadow-slate-200/50 space-y-4">
            
            <!-- Merchant & Order Summary -->
            <div class="p-4 rounded-2xl bg-teal-50/70 border border-teal-100 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Merchant:</span>
                    <strong class="text-teal-950 font-extrabold">{{ $payment->apiClient->name ?? 'Campus Merchant' }}</strong>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Order:</span>
                    <span class="text-slate-800 font-semibold truncate max-w-[200px]">{{ $payment->description }}</span>
                </div>
                <div class="pt-2 border-t border-teal-200/60 flex items-center justify-between">
                    <span class="text-xs text-slate-700 font-bold">Total Amount:</span>
                    <div class="text-2xl font-extrabold text-slate-900 font-mono">
                        ৳ {{ number_format($payment->amount, 2) }} <span class="text-xs text-teal-700 font-sans">BDT</span>
                    </div>
                </div>
            </div>

            <!-- 3-Step Checkout Stepper Indicator (bKash / Nagad style) -->
            <div class="flex items-center justify-between px-2 pt-1 text-[11px] font-bold">
                <div class="flex items-center gap-1.5" :class="step === 1 ? 'text-teal-700' : (step > 1 ? 'text-emerald-600' : 'text-slate-400')">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] text-white"
                          :class="step === 1 ? 'bg-teal-700' : (step > 1 ? 'bg-emerald-600' : 'bg-slate-300')">1</span>
                    <span>Account</span>
                </div>
                <div class="w-6 h-0.5" :class="step > 1 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                <div class="flex items-center gap-1.5" :class="step === 2 ? 'text-teal-700' : (step > 2 ? 'text-emerald-600' : 'text-slate-400')">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] text-white"
                          :class="step === 2 ? 'bg-teal-700' : (step > 2 ? 'bg-emerald-600' : 'bg-slate-300')">2</span>
                    <span>SMS OTP</span>
                </div>
                <div class="w-6 h-0.5" :class="step > 2 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                <div class="flex items-center gap-1.5" :class="step === 3 ? 'text-teal-700' : 'text-slate-400'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] text-white"
                          :class="step === 3 ? 'bg-teal-700' : 'bg-slate-300'">3</span>
                    <span>5-Digit PIN</span>
                </div>
            </div>

            <!-- Error Banner -->
            <div x-show="errorMessage" x-transition class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between">
                <span x-text="errorMessage"></span>
                <button type="button" @click="errorMessage = ''" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
            </div>

            <!-- Sandbox Notification / Helper Pill -->
            <div x-show="sandboxOtp" x-transition class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="font-extrabold uppercase text-[10px] px-1.5 py-0.5 rounded bg-amber-200">Sandbox Code</span>
                    <span class="font-mono font-bold tracking-widest text-sm" x-text="sandboxOtp"></span>
                </div>
                <button type="button" @click="otp = sandboxOtp" class="text-xs font-extrabold text-amber-900 underline hover:text-amber-950">Auto Fill</button>
            </div>

            <!-- STEP 1: Enter Mobile Account Number -->
            <div x-show="step === 1" x-transition>
                <form @submit.prevent="requestOtp()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Your Barakah Wallet Account / Phone
                        </label>
                        <input type="text" x-model="phone" required autofocus
                               placeholder="01XXXXXXXXX or Account ID"
                               class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 font-mono text-sm font-semibold focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition">
                        <p class="text-[11px] text-slate-400 mt-1">We will send a 6-digit verification code to this mobile number</p>
                    </div>

                    @auth
                    @if(auth()->user()->phone)
                    <div class="pt-0.5">
                        <button type="button" @click="phone = '{{ auth()->user()->phone }}'"
                                class="text-xs text-teal-800 font-bold hover:underline flex items-center gap-1">
                            <span>Use logged-in phone:</span>
                            <span class="font-mono">{{ auth()->user()->phone }}</span>
                        </button>
                    </div>
                    @endif
                    @endauth

                    <button type="submit" :disabled="loading || phone.length < 4"
                            class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-md shadow-teal-800/20 transition transform active:scale-[0.98] disabled:opacity-50">
                        <span x-show="!loading">Send Verification Code</span>
                        <span x-show="loading">Sending SMS OTP...</span>
                    </button>
                </form>
            </div>

            <!-- STEP 2: Enter 6-Digit SMS OTP -->
            <div x-show="step === 2" x-transition style="display: none;">
                <form @submit.prevent="submitOtp()" class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                6-Digit SMS Verification Code
                            </label>
                            <button type="button" @click="step = 1; errorMessage = ''" class="text-xs font-bold text-teal-700 hover:underline">Change Number</button>
                        </div>
                        <input type="text" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" x-model="otp" required autofocus
                               placeholder="••••••"
                               class="w-full px-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-center tracking-widest text-2xl font-mono focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1.5">
                            <span>Sent to: <strong class="text-slate-700 font-mono" x-text="phone"></strong></span>
                            <button type="button" @click="requestOtp()" :disabled="resendCountdown > 0"
                                    class="text-teal-700 font-bold hover:underline disabled:text-slate-400 disabled:no-underline">
                                <span x-show="resendCountdown === 0">Resend Code</span>
                                <span x-show="resendCountdown > 0" x-text="'Resend in ' + resendCountdown + 's'"></span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading || otp.length !== 6"
                            class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-sm shadow-md shadow-teal-800/20 transition transform active:scale-[0.98] disabled:opacity-50">
                        <span x-show="!loading">Verify Code</span>
                        <span x-show="loading">Verifying OTP...</span>
                    </button>
                </form>
            </div>

            <!-- STEP 3: Enter 5-Digit PIN (NO password required!) -->
            <div x-show="step === 3" x-transition style="display: none;">
                <form @submit.prevent="submitPin()" class="space-y-4">
                    <div>
                        <div class="text-center mb-2">
                            <span class="text-xs text-slate-500">Payer Account:</span>
                            <p class="text-sm font-extrabold text-teal-900" x-text="userName || phone"></p>
                        </div>

                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-center">
                            Enter Your 5-Digit PIN
                        </label>
                        <input type="password" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" x-model="pin" required autofocus
                               placeholder="•••••"
                               class="w-48 mx-auto px-4 py-3 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-center tracking-widest text-2xl font-mono focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition block">
                        <p class="text-[11px] text-slate-400 text-center mt-1">Enter your Barakah mobile wallet PIN (not account password)</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <button type="button" @click="step = 2; pin = ''; errorMessage = ''"
                                class="py-3.5 px-4 rounded-2xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                            Back
                        </button>
                        <button type="submit" :disabled="loading || pin.length !== 5"
                                class="py-3.5 px-4 rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-600 hover:from-teal-600 hover:to-emerald-500 text-white font-extrabold text-xs shadow-md shadow-teal-800/20 transition transform active:scale-[0.98] disabled:opacity-50">
                            <span x-show="!loading">Confirm & Pay</span>
                            <span x-show="loading">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <div class="mt-6 text-center text-[11px] text-slate-400">
            Powered by <strong class="text-emerald-700 font-bold">Relic Software</strong> • Barakah Payment Gateway
        </div>
    </div>

    <script>
    function paymentCheckout(paymentUuid) {
        return {
            uuid: paymentUuid,
            step: 1,
            phone: '{{ $user?->phone ?? "" }}',
            otp: '',
            pin: '',
            userName: '',
            sandboxOtp: null,
            errorMessage: '',
            loading: false,
            resendCountdown: 0,
            timer: null,

            startResendTimer() {
                this.resendCountdown = 60;
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => {
                    if (this.resendCountdown > 0) {
                        this.resendCountdown--;
                    } else {
                        clearInterval(this.timer);
                    }
                }, 1000);
            },

            async requestOtp() {
                this.loading = true;
                this.errorMessage = '';
                try {
                    const res = await fetch(`/payment/checkout/${this.uuid}/send-otp`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone: this.phone })
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Could not send verification code.');
                    }
                    this.step = 2;
                    this.userName = data.user_name || '';
                    if (data.sandbox_otp) {
                        this.sandboxOtp = data.sandbox_otp;
                    }
                    this.startResendTimer();
                } catch (err) {
                    this.errorMessage = err.message;
                } finally {
                    this.loading = false;
                }
            },

            async submitOtp() {
                this.loading = true;
                this.errorMessage = '';
                try {
                    const res = await fetch(`/payment/checkout/${this.uuid}/verify-otp`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ otp: this.otp })
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Verification code is invalid.');
                    }
                    this.userName = data.user_name || this.userName;
                    this.step = 3;
                } catch (err) {
                    this.errorMessage = err.message;
                } finally {
                    this.loading = false;
                }
            },

            async submitPin() {
                this.loading = true;
                this.errorMessage = '';
                try {
                    const res = await fetch(`/payment/checkout/${this.uuid}/pay`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ pin: this.pin })
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Payment authorization failed.');
                    }
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.href = `/payment/checkout/${this.uuid}/success`;
                    }
                } catch (err) {
                    this.errorMessage = err.message;
                } finally {
                    this.loading = false;
                }
            }
        };
    }
    </script>

</body>
</html>
