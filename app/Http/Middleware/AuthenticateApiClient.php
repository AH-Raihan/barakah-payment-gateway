<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key missing. Please provide X-API-KEY header.',
            ], 401);
        }

        // Find active client matching hash
        $clients = ApiClient::where('status', 'active')->get();
        $matchedClient = null;

        foreach ($clients as $client) {
            if (Hash::check($apiKey, $client->api_key)) {
                $matchedClient = $client;
                break;
            }
        }

        if (!$matchedClient) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API credentials.',
            ], 401);
        }

        $request->attributes->set('api_client', $matchedClient);

        return $next($request);
    }
}

