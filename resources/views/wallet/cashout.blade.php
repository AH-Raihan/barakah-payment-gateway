@extends('layouts.app')

@section('title', 'Cash Out')

@section('content')
<div class="py-4 space-y-4" x-data="cashOutWizard()">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ __('Cash Out') }}</h2>
            <p class="text-xs text-slate-500">Withdraw cash at any institutional counter or agent</p>
        </div>
        <div class="text-right">
            <span class="text-[11px] text-slate-400 block font-medium">{{ __('Available Balance') }}</span>
            <span class="text-sm font-bold text-amber-700 font-mono">৳ {{ number_format($wallet->balance, 2) }}</span>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
        
        <form id="cashout-form" action="{{ route('wallet.cashout') }}" method="POST">
            @csrf
            <input type="hidden" name="idempotency_key" :value="idempotencyKey">
            <input type="hidden" name="pin" :value="pin">

            <!-- Agent Selection with Scan QR Button -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Agent Account ID or Phone') }}
                    </label>
                    <button type="button" @click="openQrScanner()"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold hover:bg-amber-100 transition shadow-sm active:scale-95">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>{{ __('Scan Agent QR') }}</span>
                    </button>
                </div>
                
                <input type="text" name="agent_account" x-model="agentAccount" required
                       placeholder="{{ __('Agent Account ID or Phone') }}"
                       class="w-full px-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-sm font-semibold focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition">

                @if($agents->isNotEmpty())
                <div class="mt-2.5">
                    <p class="text-[11px] text-slate-500 mb-1.5 font-bold uppercase tracking-wider">Quick Select Authorized Agents:</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($agents as $agent)
                        <button type="button" @click="agentAccount = '{{ $agent->phone ?? $agent->account_id }}'"
                                class="text-xs font-bold px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 hover:bg-amber-100 transition shadow-sm">
                            {{ $agent->name }} ({{ $agent->phone ?? $agent->account_id }})
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Amount -->
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    {{ __('Cash Out Amount (BDT)') }}
                </label>
                <div class="relative">
                    <div class="absolute left-4 top-3 text-xl font-bold text-slate-400">৳</div>
                    <input type="number" step="any" min="1" max="{{ $wallet->balance }}" name="amount" x-model.number="amount"
                           required placeholder="0.00"
                           class="w-full pl-10 pr-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-2xl font-extrabold font-mono focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition">
                </div>

                <div class="flex flex-wrap gap-2 mt-2.5">
                    <template x-for="chip in [500, 1000, 2000, 5000]" :key="chip">
                        <button type="button" @click="amount = chip"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition"
                                :class="amount === chip ? 'bg-amber-600 text-white border-amber-600 shadow-sm' : 'bg-slate-100 text-slate-700 border-slate-200 hover:border-amber-400'">
                            ৳<span x-text="chip"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-3">
                <button type="button" @click="openPinModal()"
                        :disabled="!agentAccount || !amount || amount <= 0 || amount > {{ $wallet->balance }}"
                        class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-amber-600 to-orange-500 hover:from-amber-500 hover:to-orange-400 text-white font-extrabold text-sm shadow-lg shadow-amber-600/20 transition transform active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed">
                    {{ __('Confirm Cash Out') }}
                </button>
            </div>
        </form>

    </div>

    <!-- QR Camera Scanner Modal -->
    <div x-show="showScannerModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4" style="display: none;">
        <div @click.away="closeQrScanner()" class="bg-white rounded-3xl w-full max-w-sm p-5 shadow-2xl space-y-3 text-center">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Scan Agent QR Code</h3>
                <button type="button" @click="closeQrScanner()" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <div id="agent-qr-reader" class="rounded-2xl overflow-hidden border border-slate-200 w-full min-h-[260px] bg-slate-100 flex items-center justify-center">
                <span class="text-xs text-slate-400">Initializing Camera...</span>
            </div>

            <p class="text-[11px] text-slate-500">Scan the counter agent's Barakah QR code</p>
        </div>
    </div>

    <!-- 5-Digit PIN Modal -->
    <div x-show="showPinModal" x-transition.opacity class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-950/70 backdrop-blur-sm p-0 sm:p-4" style="display: none;">
        <div @click.away="showPinModal = false" class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-6 shadow-2xl text-center space-y-4">
            <div class="w-12 h-1 bg-slate-300 rounded-full mx-auto sm:hidden mb-2"></div>
            
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-800 mx-auto flex items-center justify-center font-bold text-xl shadow-inner">
                💵
            </div>

            <div>
                <h3 class="text-base font-extrabold text-slate-900">Authorize Cash Out</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Amount: <strong class="text-slate-900 font-mono text-sm">৳<span x-text="amount"></span></strong> via Agent <strong class="text-slate-900 font-mono" x-text="agentAccount"></strong>
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Enter your 5-digit PIN</label>
                <input type="password" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" x-model="pin" autofocus
                       placeholder="•••••"
                       class="w-48 mx-auto px-4 py-3 rounded-2xl bg-slate-100 border border-slate-300 text-center tracking-widest text-2xl font-mono text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white transition">
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click="showPinModal = false"
                        class="py-3 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" @click="confirmAndSubmit()"
                        :disabled="pin.length !== 5 || submitting"
                        class="py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-md shadow-amber-600/20 transition disabled:opacity-40">
                    <span x-show="!submitting">Confirm</span>
                    <span x-show="submitting">Processing...</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function cashOutWizard() {
    return {
        agentAccount: '',
        amount: null,
        pin: '',
        showPinModal: false,
        showScannerModal: false,
        submitting: false,
        html5QrCode: null,
        idempotencyKey: window.generateIdempotencyKey ? window.generateIdempotencyKey() : ('idemp_' + Date.now()),

        openQrScanner() {
            this.showScannerModal = true;
            this.$nextTick(() => {
                if (window.Html5Qrcode) {
                    this.html5QrCode = new window.Html5Qrcode("agent-qr-reader");
                    this.html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 220, height: 220 } },
                        (decodedText) => {
                            this.agentAccount = decodedText.trim();
                            this.closeQrScanner();
                        },
                        () => {}
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
            if (!this.agentAccount || !this.amount || this.amount <= 0) return;
            this.pin = '';
            this.showPinModal = true;
        },

        confirmAndSubmit() {
            if (this.pin.length !== 5 || this.submitting) return;
            this.submitting = true;
            document.getElementById('cashout-form').submit();
        }
    }
}
</script>
@endsection
