<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send an FCM push notification to a specific user across all registered devices.
     */
    public static function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        $tokens = $user->deviceTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::info("FCM: No device tokens registered for user ID {$user->id} ({$user->name}). Notification: {$title}");
            return ['success' => true, 'sent_count' => 0, 'reason' => 'no_tokens'];
        }

        $sent = self::sendToTokens($tokens, $title, $body, $data);
        return ['success' => $sent, 'sent_count' => count($tokens)];
    }

    /**
     * Send notification to an array of device registration tokens.
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($tokens)) {
            return false;
        }

        $projectId = config('firebase.project_id', 'barakah-c44d4');
        $accessToken = self::getGoogleAccessToken();

        // ── 1. Modern FCM HTTP v1 API (Google Service Account) ────────────
        if ($accessToken) {
            $successCount = 0;
            $clickAction = $data['click_action'] ?? url('/dashboard');

            foreach ($tokens as $token) {
                // Ensure all data values are strings for FCM v1 specification
                $stringData = array_map(function ($val) {
                    return is_scalar($val) ? (string) $val : json_encode($val);
                }, array_merge(['title' => $title, 'body' => $body, 'click_action' => $clickAction], $data));

                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $stringData,
                        'webpush' => [
                            'notification' => [
                                'icon' => url('/images/icons/icon-192x192.png'),
                                'badge' => url('/images/icons/icon-72x72.png'),
                            ],
                            'fcm_options' => [
                                'link' => $clickAction,
                            ],
                        ],
                    ],
                ];

                try {
                    $response = Http::withToken($accessToken)
                        ->timeout(8)
                        ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

                    if ($response->successful()) {
                        $successCount++;
                    } else {
                        $errorStatus = $response->json('error.status') ?? '';
                        Log::warning("FCM v1 Response ({$response->status()}): {$response->body()}");

                        // If token is invalid or unregistered, clean it up from database
                        if (in_array($errorStatus, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                            DeviceToken::where('token', $token)->delete();
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("FCM v1 Dispatch Exception: " . $e->getMessage());
                }
            }

            return $successCount > 0 || count($tokens) === 0;
        }

        // ── 2. Legacy FCM Server Key Endpoint Fallback ───────────────────
        $serverKey = config('firebase.server_key');
        if (!empty($serverKey)) {
            try {
                $payload = [
                    'registration_ids' => array_values($tokens),
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'icon' => url('/images/icons/icon-192x192.png'),
                        'badge' => url('/images/icons/icon-72x72.png'),
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
                    return true;
                }
            } catch (\Exception $e) {
                Log::error("FCM Legacy Exception: " . $e->getMessage());
            }
        }

        // ── 3. Dev / Local Simulation Mode ──────────────────────────────
        Log::info("FCM Push [Dev Simulation]: '{$title}' - '{$body}' to " . count($tokens) . " devices.", [
            'tokens_count' => count($tokens),
            'sample_token' => substr($tokens[0], 0, 15) . '...',
            'data' => $data,
        ]);

        return true;
    }

    /**
     * Generate or retrieve a cached Google OAuth2 Access Token from the Service Account JSON.
     */
    public static function getGoogleAccessToken(): ?string
    {
        $credPath = config('firebase.credentials_file');
        if (empty($credPath)) {
            return null;
        }

        // Check relative to base path or absolute
        $fullPath = file_exists($credPath) ? $credPath : base_path($credPath);
        if (!file_exists($fullPath)) {
            return null;
        }

        return Cache::remember('google_fcm_access_token', 3000, function () use ($fullPath) {
            try {
                $json = json_decode(file_get_contents($fullPath), true);
                if (empty($json['client_email']) || empty($json['private_key'])) {
                    return null;
                }

                $email = $json['client_email'];
                $privateKey = $json['private_key'];
                $now = time();

                $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
                $claims = rtrim(strtr(base64_encode(json_encode([
                    'iss' => $email,
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'exp' => $now + 3600,
                    'iat' => $now,
                ])), '+/', '-_'), '=');

                $signature = '';
                if (!openssl_sign("{$header}.{$claims}", $signature, $privateKey, 'SHA256')) {
                    Log::error("FCM: Failed to sign Google Service Account JWT with OpenSSL.");
                    return null;
                }

                $sig = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
                $jwt = "{$header}.{$claims}.{$sig}";

                $response = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::warning("FCM Google OAuth2 Token Request Failed: " . $response->body());
                return null;
            } catch (\Exception $e) {
                Log::error("FCM Google OAuth2 Token Exception: " . $e->getMessage());
                return null;
            }
        });
    }
}
