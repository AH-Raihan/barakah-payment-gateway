<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiClientController extends Controller
{
    public function index()
    {
        $clients = ApiClient::with('user')->latest()->paginate(10);
        return view('admin.api-clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.api-clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'webhook_url' => 'nullable|url|max:500',
            'rate_limit' => 'required|integer|min:10|max:1000',
        ]);

        $plainApiKey = 'brk_live_' . Str::random(32);
        $plainApiSecret = 'sec_' . Str::random(48);

        $client = ApiClient::create([
            'name' => $request->input('name'),
            'user_id' => Auth::id(),
            'api_key' => Hash::make($plainApiKey),
            'api_secret' => Hash::make($plainApiSecret),
            'webhook_url' => $request->input('webhook_url'),
            'rate_limit' => $request->input('rate_limit'),
            'status' => 'active',
        ]);

        ActivityLogService::log(
            Auth::id(),
            'api_client_created',
            "Created API client '{$client->name}' for payment gateway integration"
        );

        return view('admin.api-clients.credentials', compact('client', 'plainApiKey', 'plainApiSecret'));
    }

    public function toggleStatus(ApiClient $client)
    {
        $newStatus = $client->status === 'active' ? 'inactive' : 'active';
        $client->update(['status' => $newStatus]);

        ActivityLogService::log(
            Auth::id(),
            'api_client_status_changed',
            "API client '{$client->name}' status changed to {$newStatus}"
        );

        return back()->with('success', "API Client '{$client->name}' is now {$newStatus}.");
    }
}

