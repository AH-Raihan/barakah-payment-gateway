import './bootstrap';
import Alpine from 'alpinejs';
import mask from '@alpinejs/mask';
import { Html5Qrcode, Html5QrcodeScanner } from 'html5-qrcode';

Alpine.plugin(mask);

window.Html5Qrcode = Html5Qrcode;
window.Html5QrcodeScanner = Html5QrcodeScanner;

// Helper for generating unique idempotency keys
window.generateIdempotencyKey = function() {
    return 'idemp_' + Date.now().toString(36) + '_' + Math.random().toString(36).substring(2, 10);
};

// Global submit-once safeguard to prevent duplicate transactions from rapid clicks
Alpine.data('submitOnce', () => ({
    submitting: false,
    handleSubmit(e) {
        if (this.submitting) {
            e.preventDefault();
            return false;
        }
        this.submitting = true;
        return true;
    }
}));

window.Alpine = Alpine;
Alpine.start();
