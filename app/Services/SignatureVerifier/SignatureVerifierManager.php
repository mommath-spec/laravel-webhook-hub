<?php

namespace App\Services\SignatureVerifier;

use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;

class SignatureVerifierManager
{
    public function __construct(
        private readonly StripeVerifier $stripeVerifier,
        private readonly GitHubVerifier $gitHubVerifier,
        private readonly GenericSecretVerifier $genericSecretVerifier
    ) {
    }

    public function verify(Request $request, WebhookEndpoint $endpoint): bool
    {
        return $this->resolverForType((string) $endpoint->type)->verify($request, $endpoint);
    }

    private function resolverForType(string $type): SignatureVerifierInterface
    {
        return match (strtolower($type)) {
            'stripe' => $this->stripeVerifier,
            'github' => $this->gitHubVerifier,
            default => $this->genericSecretVerifier,
        };
    }
}
