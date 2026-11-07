<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send an FCM push notification to a specific user across all registered devices.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        $tokens = $user->deviceTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::info("FCM: No device tokens registered for user ID {$user->id} ({$user->name}). Notification: {$title}");
            return true;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to an array of device registration tokens.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($tokens)) {
            return false;
        }

        $serverKey = config('firebase.server_key');

        // Development / Logging mode if server key is not yet provided in .env
        if (empty($serverKey)) {
            Log::info("FCM Push [Local/Dev Simulation]: '{$title}' - '{$body}' to " . count($tokens) . " devices.", [
                'tokens_count' => count($tokens),
                'sample_token' => substr($tokens[0], 0, 15) . '...',
                'data' => $data,
            ]);
            return true;
        }

        try {
            $payload = [
                'registration_ids' => array_values($tokens),
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => url('/images/logo-icon.png'),
                    'badge' => url('/images/logo-icon.png'),
                    'sound' => 'default',
                    'click_action' => $data['click_action'] ?? url('/dashboard'),
                ],
                'data' => array_merge([
                    'title' => $title,
                    'body' => $body,
                    'click_action' => url('/dashboard'),
                ], $data),
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $result = $response->json();
                
                // Cleanup invalid tokens if reported by FCM
                if (!empty($result['results'])) {
                    foreach ($result['results'] as $index => $res) {
                        if (isset($res['error']) && in_array($res['error'], ['NotRegistered', 'InvalidRegistration'], true)) {
                            $badToken = $tokens[$index] ?? null;
                            if ($badToken) {
                                DeviceToken::where('token', $badToken)->delete();
                            }
                        }
                    }
                }

                return true;
            }

            Log::warning("FCM Error Response (HTTP {$response->status()}): " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("FCM Exception: " . $e->getMessage());
            return false;
        }
    }
}

