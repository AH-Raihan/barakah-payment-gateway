# Barakah Payment Gateway SDK for Laravel

[![Latest Version](https://img.shields.io/badge/version-1.0.0-teal.svg)](https://github.com/relicsoftware/barakah-payment-gateway)
[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012-rose.svg)](https://laravel.com)

Official Laravel package to integrate the **Barakah Institutional Financial Wallet** payment gateway into any external Laravel project (like bKash / Nagad / SSLCommerz).

Powered by **Relic Software**.

---

## ⚡ Features

- **bKash / Nagad / Upay Checkout Experience**: Secure 3-step checkout (**Mobile Phone Number + 6-digit SMS OTP + 5-digit PIN**). No account password required from customers!
- **One-line Payment Initiation**: Clean developer syntax with instant browser redirect: `return Barakah::createPayment([...])->redirect();`
- **Sandbox & Live Mode**: Automatic instant testing in sandbox mode with mock OTP auto-fill, and live telecom SMS gateway in production.
- **Headless API**: Full support for native mobile apps (React Native, Flutter, iOS, Android) to conduct OTP + PIN checkout headlessly.
- **Real-Time Payment Status**: Check transaction references, amount paid, and status via `Barakah::status($paymentId)`.
- **Laravel 11 & 12 Ready**: Supports automatic package discovery and Facade registration.

---

## 📦 Installation

In your external project's `composer.json`, add the repository:

```json
"repositories": [
    {
        "type": "path",
        "url": "../packages/relicsoftware/barakah-payment-gateway"
    }
]
```

Then install via Composer:

```bash
composer require relicsoftware/barakah-payment-gateway
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=barakah-config
```

---

## ⚙️ Configuration

Add your Barakah server credentials to your project's `.env` file:

```env
BARAKAH_BASE_URL=https://wallet.yourinstitute.edu
BARAKAH_API_KEY=your_generated_api_key_from_admin_portal
BARAKAH_MODE=sandbox
BARAKAH_TIMEOUT=15
```

> **Note**: To obtain an API Key, log in to your Barakah Admin Portal -> **Payment Gateway API** -> **Create API Client**.

---

## 🚀 Quick Usage

### 1. Initiate Payment (Controller)

In your checkout controller, create a payment and immediately redirect the user to the Barakah secure checkout page:

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RelicSoftware\BarakahPayment\Facades\Barakah;

class CheckoutController extends Controller
{
    public function pay(Request $request)
    {
        // 1. Create a payment request
        $payment = Barakah::createPayment([
            'amount' => 350.00,
            'description' => 'Semester Library Pass - Order #9021',
            'callback_url' => route('barakah.callback'),
            'expires_in_minutes' => 30,
        ]);

        // 2. Redirect customer to Barakah web checkout
        return $payment->redirect();
    }

    public function callback(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $status = $request->query('status'); // 'paid'

        // 3. Verify status directly with Barakah server
        if (Barakah::isPaid($paymentId)) {
            $paymentInfo = Barakah::status($paymentId);
            
            $transactionRef = $paymentInfo->getTransactionReference();
            $amount = $paymentInfo->getAmount();

            // Mark order as paid in your database
            // Order::where('payment_id', $paymentId)->update(['status' => 'paid', 'ref' => $transactionRef]);

            return view('order.success', compact('transactionRef', 'amount'));
        }

        return redirect()->route('cart')->with('error', 'Payment was not completed.');
    }
}
```

---

## 📱 Headless Integration (For Mobile Apps & SPAs)

If you are building a custom mobile app (Flutter, React Native) or single page application and want to build your own custom UI:

### Step 1: Send SMS OTP
```php
$res = Barakah::sendOtp($paymentId, $customerPhone);
// Returns: ['success' => true, 'message' => 'Verification code sent to 018****123']
```

### Step 2: Verify OTP
```php
$res = Barakah::verifyOtp($paymentId, $otpCode);
// Returns: ['success' => true, 'message' => 'OTP verified successfully']
```

### Step 3: Authorize with 5-Digit PIN (No Password)
```php
$res = Barakah::execute($paymentId, $fiveDigitPin);
// Returns: ['success' => true, 'data' => ['status' => 'paid', 'transaction_reference' => 'TXN-...']]
```

---

## 🛡️ Webhook Handling

Barakah automatically posts a webhook notification to your configured `callback_url` or `webhook_url` upon successful payment:

```php
Route::post('/barakah/webhook', function (Request $request) {
    $paymentId = $request->input('payment_id');
    $status = $request->input('status'); // 'paid'
    $transactionRef = $request->input('transaction_reference');
    $amount = $request->input('amount');

    if ($status === 'paid' && Barakah::isPaid($paymentId)) {
        // Fulfill order
    }

    return response()->json(['status' => 'ok']);
});
```

---

## 📄 License

The MIT License (MIT). Created and maintained by **Relic Software**.

