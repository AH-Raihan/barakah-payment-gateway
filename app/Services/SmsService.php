<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS message to a phone number.
     */
    public function send(string $phone, string $message): bool
    {
        if (!config('sms.enabled', true)) {
            return false;
        }

        // Clean phone number (e.g. BD format)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) < 11) {
            Log::warning("SmsService: Invalid phone number format {$phone}");
            return false;
        }

        $driver = config('sms.default', 'log');

        switch ($driver) {
            case 'http':
                return $this->sendViaHttp($cleanPhone, $message);
            case 'log':
            default:
                Log::info("SMS to {$cleanPhone}: {$message}");
                return true;
        }
    }

    /**
     * Send notification via external HTTP SMS Gateway API.
     */
    protected function sendViaHttp(string $phone, string $message): bool
    {
        $endpoint = config('sms.drivers.http.endpoint');
        $apiKey = config('sms.drivers.http.api_key');
        $senderId = config('sms.drivers.http.sender_id');

        try {
            $response = Http::timeout(5)->post($endpoint, [
                'api_key' => $apiKey,
                'sender_id' => $senderId,
                'to' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("SmsService HTTP Gateway Exception: " . $e->getMessage());
            return false;
        }
    }
}

