<?php

namespace App\Jobs;

use App\Models\WebhookCall;
use App\Models\WebhookDestination;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RelayWebhookCall implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $webhookCallId,
        private readonly int $destinationId
    ) {
    }

    public function tries(): int
    {
        return (int) config('webhook-hub.relay.max_attempts', 3);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        /** @var array<int, int> $backoff */
        $backoff = config('webhook-hub.relay.backoff', [5, 30, 120]);

        return $backoff;
    }

    public function handle(): void
    {
        $call = WebhookCall::query()->find($this->webhookCallId);
        $destination = WebhookDestination::query()->find($this->destinationId);

        if (! $call || ! $destination || ! $destination->enabled) {
            return;
        }

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-WebhookHub-Call-UUID' => $call->uuid,
        ];

        if (! empty($destination->secret)) {
            $headers['X-WebhookHub-Signature'] = hash_hmac(
                'sha256',
                json_encode($call->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '',
                (string) $destination->secret
            );
        }

        $response = Http::timeout((int) config('webhook-hub.http_timeout', 10))
            ->withHeaders($headers)
            ->post($destination->url, $call->payload);

        $call->increment('attempts');

        if ($response->failed()) {
            $destination->update([
                'last_status' => 'failed',
                'last_response_code' => $response->status(),
            ]);

            $call->update([
                'status' => 'failed',
                'last_error' => 'Relay failed with HTTP ' . $response->status(),
            ]);

            throw new RuntimeException('Relay failed with HTTP ' . $response->status());
        }

        $destination->update([
            'last_status' => 'success',
            'last_response_code' => $response->status(),
        ]);

        $relayResults = $call->relay_results ?? [];
        $relayResults[] = [
            'destination_id' => $destination->id,
            'status' => 'success',
            'response_code' => $response->status(),
            'at' => now()->toIso8601String(),
        ];

        $call->update([
            'status' => 'processed',
            'last_error' => null,
            'relay_results' => $relayResults,
        ]);
    }
}
