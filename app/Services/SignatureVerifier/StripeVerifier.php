<?php

namespace App\Services\SignatureVerifier;

use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;

class StripeVerifier implements SignatureVerifierInterface
{
    public function verify(Request $request, WebhookEndpoint $endpoint): bool
    {
        if (empty($endpoint->secret)) {
            return false;
        }

        $signatureHeader = (string) $request->header('Stripe-Signature', '');
        if ($signatureHeader === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $pair) {
            [$key, $value] = array_pad(explode('=', trim($pair), 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key] = $value;
            }
        }

        if (! isset($parts['t'], $parts['v1'])) {
            return false;
        }

        $signedPayload = $parts['t'] . '.' . $request->getContent();
        $expected = hash_hmac('sha256', $signedPayload, (string) $endpoint->secret);

        return hash_equals($expected, $parts['v1']);
    }
}
