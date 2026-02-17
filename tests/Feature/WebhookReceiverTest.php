<?php

namespace Tests\Feature;

use App\Jobs\ProcessWebhookCall;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookReceiverTest extends TestCase
{
    public function test_it_accepts_valid_github_signature_and_dispatches_job(): void
    {
        Queue::fake();

        $endpoint = WebhookEndpoint::query()->create([
            'name' => 'GitHub Main',
            'key' => 'github-main',
            'type' => 'github',
            'secret' => 'topsecret',
            'enabled' => true,
        ]);

        $payload = ['action' => 'opened', 'number' => 123];
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES) ?: '{}';
        $signature = 'sha256=' . hash_hmac('sha256', $json, 'topsecret');

        $response = $this->withHeaders([
            'X-Hub-Signature-256' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/api/webhooks/' . $endpoint->key, $payload);

        $response->assertOk();
        $this->assertDatabaseCount('webhook_calls', 1);

        Queue::assertPushed(ProcessWebhookCall::class);
    }

    public function test_it_rejects_invalid_signature(): void
    {
        Queue::fake();

        WebhookEndpoint::query()->create([
            'name' => 'GitHub Main',
            'key' => 'github-main',
            'type' => 'github',
            'secret' => 'topsecret',
            'enabled' => true,
        ]);

        $response = $this->withHeaders([
            'X-Hub-Signature-256' => 'sha256=invalid',
        ])->postJson('/api/webhooks/github-main', ['hello' => 'world']);

        $response->assertStatus(401);
        $this->assertDatabaseCount('webhook_calls', 0);
        Queue::assertNothingPushed();
    }
}
