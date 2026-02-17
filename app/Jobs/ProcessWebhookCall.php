<?php

namespace App\Jobs;

use App\Models\WebhookCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhookCall implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $webhookCallId
    ) {
    }

    public function handle(): void
    {
        $call = WebhookCall::query()
            ->with(['endpoint.destinations'])
            ->find($this->webhookCallId);

        if (! $call || ! $call->endpoint || ! $call->endpoint->enabled) {
            return;
        }

        $destinations = $call->endpoint->destinations->where('enabled', true);

        if ($destinations->isEmpty()) {
            $call->update(['status' => 'processed']);

            return;
        }

        foreach ($destinations as $destination) {
            RelayWebhookCall::dispatch($call->id, $destination->id);
        }

        $call->update(['status' => 'processed']);
    }
}
