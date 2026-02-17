<?php

namespace App\Services\SignatureVerifier;

use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;

class GitHubVerifier implements SignatureVerifierInterface
{
    public function verify(Request $request, WebhookEndpoint $endpoint): bool
    {
        if (empty($endpoint->secret)) {
            return false;
        }

        $provided = (string) $request->header('X-Hub-Signature-256', '');
        if ($provided === '') {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), (string) $endpoint->secret);

        return hash_equals($expected, $provided);
    }
}
