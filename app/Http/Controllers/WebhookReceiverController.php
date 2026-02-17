<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookCall;
use App\Models\WebhookCall;
use App\Models\WebhookEndpoint;
use App\Services\SignatureVerifier\SignatureVerifierManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookReceiverController extends Controller
{
    public function __construct(
        private readonly SignatureVerifierManager $signatureVerifierManager
    ) {
    }

    public function handle(Request $request, string $endpointKey): JsonResponse
    {
        $endpoint = WebhookEndpoint::query()
            ->where('key', $endpointKey)
            ->where('enabled', true)
            ->first();

        if (! $endpoint) {
            return response()->json(['message' => 'Endpoint not found.'], 404);
        }

        if (! $this->signatureVerifierManager->verify($request, $endpoint)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $rawPayload = $request->getContent();
        $decodedPayload = json_decode($rawPayload, true);
        $payload = is_array($decodedPayload) ? $decodedPayload : ['raw' => $rawPayload];

        $headers = collect($request->headers->all())
            ->map(static function (mixed $value): mixed {
                if (is_array($value) && count($value) === 1) {
                    return $value[0];
                }

                return $value;
            })
            ->all();

        $call = WebhookCall::query()->create([
            'endpoint_id' => $endpoint->id,
            'uuid' => (string) Str::uuid(),
            'payload' => $payload,
            'headers' => $headers,
            'status' => 'received',
            'attempts' => 0,
        ]);

        ProcessWebhookCall::dispatch($call->id);

        return response()->json([
            'received' => true,
            'call_uuid' => $call->uuid,
        ]);
    }
}
