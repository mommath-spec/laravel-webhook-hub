<?php

namespace App\Http\Controllers;

use App\Models\WebhookCall;
use App\Models\WebhookEndpoint;
use Illuminate\View\View;

class WebhookHubController extends Controller
{
    public function index(): View
    {
        if (! app()->environment('local', 'testing')) {
            abort(403);
        }

        return view('webhook-hub.index', [
            'endpoints' => WebhookEndpoint::query()
                ->withCount(['destinations', 'calls'])
                ->orderBy('name')
                ->get(),
            'calls' => WebhookCall::query()
                ->with('endpoint:id,name,key,type')
                ->latest()
                ->limit(50)
                ->get(),
        ]);
    }

    public function show(string $uuid): View
    {
        if (! app()->environment('local', 'testing')) {
            abort(403);
        }

        $call = WebhookCall::query()
            ->with('endpoint:id,name,key,type')
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('webhook-hub.show', [
            'call' => $call,
        ]);
    }
}
