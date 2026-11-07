@extends('layouts.app')

@section('title', 'Send Money')

@section('content')
<div class="py-4 space-y-4" x-data="sendMoneyWizard()">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ __('Send Money') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Instant Transfers') }}</p>
        </div>
        <div class="text-right">
            <span class="text-[11px] text-slate-400 block font-medium">{{ __('Available Balance') }}</span>
            <span class="text-sm font-bold text-teal-700 font-mono">৳ {{ number_format($wallet->balance, 2) }}</span>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
        
        <form id="send-form" action="{{ route('wallet.send') }}" method="POST" @submit="handleSubmit($event)">
            @csrf
            <input type="hidden" name="idempotency_key" :value="idempotencyKey">
            <input type="hidden" name="pin" :value="pin">

            <!-- Step 1: Recipient with Scan QR Button -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Recipient') }}
                    </label>
                    <!-- Scan QR Button -->
                    <button type="button" @click="openQrScanner()"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-teal-50 border border-teal-200 text-teal-800 text-xs font-bold hover:bg-teal-100 transition shadow-sm active:scale-95">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>{{ __('Scan QR') }}</span>
                    </button>
                </div>

                <div class="relative">
                    <input type="text" name="recipient" x-model="recipientInput" @input.debounce.300ms="lookupRecipient()"
                           required placeholder="{{ __('Enter Phone or Account ID') }}"
                           class="w-full pl-4 pr-10 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-sm font-semibold focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition">
                    <div class="absolute right-3 top-3.5">
                        <span x-show="isSearching" class="text-teal-600 text-xs font-semibold">Checking...</span>
                        <span x-show="recipientFound && !isSearching" class="text-emerald-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </span>
                    </div>
                </div>

                <!-- Verified Recipient Preview -->
                <div x-show="recipientFound" x-transition class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm shadow-sm" x-text="recipientData.name ? recipientData.name.substring(0, 2).toUpperCase() : 'U'"></div>
                        <div>
                            <p class="text-sm font-extrabold text-emerald-950" x-text="recipientData.name"></p>
                            <p class="text-xs text-emerald-800 font-mono" x-text="recipientData.phone || recipientData.account_id"></p>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-900 uppercase px-2.5 py-1 rounded-full bg-emerald-200/70" x-text="recipientData.role"></span>
                </div>
            </div>

            <!-- Step 2: Amount -->
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    {{ __('Amount to Send (BDT)') }}
                </label>
                <div class="relative">
                    <div class="absolute left-4 top-3 text-xl font-bold text-slate-400">৳</div>
                    <input type="number" step="any" min="1" max="{{ $wallet->balance }}" name="amount" x-model.number="amount"
                           required placeholder="0.00"
                           class="w-full pl-10 pr-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-2xl font-extrabold font-mono focus:outline-none focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition">
                </div>

                <!-- Quick Amount Chips -->
                <div class="flex flex-wrap gap-2 mt-2.5">
                    <template x-for="chip in [100, 500, 1000, 2000, 5000]" :key="chip">
                        <button type="button" @click="amount = chip"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition"
                                :class="amount === chip ? 'bg-teal-700 text-white border-teal-700 shadow-sm' : 'bg-slate-100 text-slate-700 border-slate-200 hover:border-teal-400'">
                            +৳<span x-text="chip"></span>
                        </button>
                    </template>
                </div>

                <div x-show="amount > {{ $wallet->balance }}" class="mt-2 p-2 rounded-xl bg-rose-50 text-xs text-rose-700 font-bold">
                    Amount exceeds available balance of ৳{{ number_format($wallet->balance, 2) }}
                </div>
            </div>

            <!-- Step 3: Reference -->
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    {{ __('Reference / Note (Optional)') }}
                </label>
                <input type="text" name="description" placeholder="{{ __('Note (optional)') }}"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-800 text-xs focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <!-- Submit Button -->
            <div class="pt-3">
                <button type="button" @click="openPinModal()"
                        :disabled="!recipientInput || !amount || amount <= 0 || amount > {{ $wallet->balance }}"
                        class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white font-extrabold text-sm shadow-lg shadow-teal-700/20 transition transform active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed">
                    {{ __('Continue to Confirm') }}
                </button>
            </div>
        </form>

    </div>

    <!-- QR Camera Scanner Modal -->
    <div x-show="showScannerModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4" style="display: none;">
        <div @click.away="closeQrScanner()" class="bg-white rounded-3xl w-full max-w-sm p-5 shadow-2xl space-y-3 text-center">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Scan Recipient QR Code</h3>
                <button type="button" @click="closeQrScanner()" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <!-- Video Viewfinder -->
            <div id="qr-reader" class="rounded-2xl overflow-hidden border border-slate-200 w-full min-h-[260px] bg-slate-100 flex items-center justify-center">
                <span class="text-xs text-slate-400">Initializing Camera...</span>
            </div>

            <p class="text-[11px] text-slate-500">Point your camera at the other user's Barakah QR code</p>
        </div>
    </div>

    <!-- 5-Digit PIN Confirmation Modal -->
    <div x-show="showPinModal" x-transition.opacity class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-950/70 backdrop-blur-sm p-0 sm:p-4" style="display: none;">
        <div @click.away="showPinModal = false" class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-6 shadow-2xl text-center space-y-4">
            <div class="w-12 h-1 bg-slate-300 rounded-full mx-auto sm:hidden mb-2"></div>
            
            <div class="w-14 h-14 rounded-2xl bg-teal-100 text-teal-800 mx-auto flex items-center justify-center font-bold text-xl shadow-inner">
                🔒
            </div>

            <div>
                <h3 class="text-base font-extrabold text-slate-900">{{ __('Authorize Transfer') }}</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('Amount to Send (BDT)') }}: <strong class="text-slate-900 font-mono text-sm">৳<span x-text="amount"></span></strong>
                </p>
            </div>

            <!-- PIN Input -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">{{ __('Enter your 5-digit PIN') }}</label>
                <input type="password" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" x-model="pin" autofocus
                       placeholder="•••••"
                       class="w-48 mx-auto px-4 py-3 rounded-2xl bg-slate-100 border border-slate-300 text-center tracking-widest text-2xl font-mono text-slate-900 focus:outline-none focus:border-teal-500 focus:bg-white transition">
            </div>

            <!-- Buttons -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click="showPinModal = false"
                        class="py-3 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    {{ __('Cancel') }}
                </button>
                <button type="button" @click="confirmAndSubmit()"
                        :disabled="pin.length !== 5 || submitting"
                        class="py-3 px-4 rounded-xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold shadow-md shadow-teal-700/20 transition disabled:opacity-40">
                    <span x-show="!submitting">{{ __('Confirm & Send') }}</span>
                    <span x-show="submitting">...</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function sendMoneyWizard() {
    return {
        recipientInput: '',
        recipientFound: false,
        isSearching: false,
        recipientData: {},
        amount: null,
        pin: '',
        showPinModal: false,
        showScannerModal: false,
        submitting: false,
        html5QrCode: null,
        idempotencyKey: window.generateIdempotencyKey ? window.generateIdempotencyKey() : ('idemp_' + Date.now()),

        lookupRecipient() {
            if (this.recipientInput.length < 3) {
                this.recipientFound = false;
                this.recipientData = {};
                return;
            }
            this.isSearching = true;
            fetch('{{ route('wallet.lookup') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ query: this.recipientInput })
            })
            .then(res => res.json())
            .then(data => {
                this.isSearching = false;
                if (data.found) {
                    this.recipientFound = true;
                    this.recipientData = data;
                } else {
                    this.recipientFound = false;
                    this.recipientData = {};
                }
            })
            .catch(() => { this.isSearching = false; });
        },

        openQrScanner() {
            this.showScannerModal = true;
            this.$nextTick(() => {
                if (window.Html5Qrcode) {
                    this.html5QrCode = new window.Html5Qrcode("qr-reader");
                    this.html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 220, height: 220 } },
                        (decodedText) => {
                            // On successful scan
                            this.recipientInput = decodedText.trim();
                            this.closeQrScanner();
                            this.lookupRecipient();
                        },
                        (errorMessage) => {
                            // Ignored per-frame scan attempts
                        }
                    ).catch(err => {
                        console.warn("Camera access failed", err);
                    });
                }
            });
        },

        closeQrScanner() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                    this.html5QrCode = null;
                }).catch(() => {
                    this.html5QrCode = null;
                });
            }
            this.showScannerModal = false;
        },

        openPinModal() {
            if (!this.recipientInput || !this.amount || this.amount <= 0) return;
            this.pin = '';
            this.showPinModal = true;
        },

        confirmAndSubmit() {
            if (this.pin.length !== 5 || this.submitting) return;
            this.submitting = true;
            document.getElementById('send-form').submit();
        }
    }
}
</script>
@endsection
