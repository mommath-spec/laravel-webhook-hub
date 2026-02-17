<?php

namespace App\Services\SignatureVerifier;

use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;

interface SignatureVerifierInterface
{
    public function verify(Request $request, WebhookEndpoint $endpoint): bool;
}
