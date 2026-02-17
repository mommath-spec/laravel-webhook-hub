<?php

namespace App\Services\SignatureVerifier;

use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;

class GenericSecretVerifier implements SignatureVerifierInterface
{
    public function verify(Request $request, WebhookEndpoint $endpoint): bool
    {
        if (empty($endpoint->secret)) {
            return false;
        }

        $provided = (string) ($request->header('X-Webhook-Token') ?? $request->query('token', ''));

        return $provided !== '' && hash_equals((string) $endpoint->secret, $provided);
    }
}
