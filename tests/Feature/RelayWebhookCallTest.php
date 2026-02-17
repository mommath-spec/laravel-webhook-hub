<?php

namespace Tests\Feature;

use App\Jobs\RelayWebhookCall;
use App\Models\WebhookCall;
use App\Models\WebhookDestination;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RelayWebhookCallTest extends TestCase
{
    public function test_it_relays_payload_to_destination(): void
    {
        Http::fake([
            'https://internal.example/webhooks/order' => Http::response(['ok' => true], 200),
        ]);

        $endpoint = WebhookEndpoint::query()->create([
            'name' => 'Stripe Main',
            'key' => 'stripe-main',
            'type' => 'stripe',
            'secret' => 'whsec_xxx',
            'enabled' => true,
        ]);

        $destination = WebhookDestination::query()->create([
            'endpoint_id' => $endpoint->id,
            'url' => 'https://internal.example/webhooks/order',
            'secret' => 'relay_secret',
            'enabled' => true,
        ]);

        $call = WebhookCall::query()->create([
            'endpoint_id' => $endpoint->id,
            'uuid' => '8a658fdf-1111-4444-8888-123456789001',
            'payload' => ['event' => 'payment_intent.succeeded'],
            'headers' => ['content-type' => 'application/json'],
            'status' => 'received',
            'attempts' => 0,
        ]);

        (new RelayWebhookCall($call->id, $destination->id))->handle();

        Http::assertSentCount(1);
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://internal.example/webhooks/order'
                && $request->hasHeader('X-WebhookHub-Call-UUID');
        });

        $this->assertDatabaseHas('webhook_destinations', [
            'id' => $destination->id,
            'last_status' => 'success',
            'last_response_code' => 200,
        ]);
    }
}
