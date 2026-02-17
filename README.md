# Laravel Webhook Hub

Centralized webhook ingest and relay hub in Laravel with logging, signature verification and retries.  
Accepts webhooks from external services (Stripe, WooCommerce, GitHub, generic) and relays them to one or more internal destinations.

## Features

- Single entrypoint for multiple webhook endpoints (`/webhooks/{endpointKey}`).
- Configurable `WebhookEndpoint` records (type, secret, enabled).
- Logs all incoming webhook calls (payload, headers, status, attempts).
- Pluggable signature verification strategies (Stripe, GitHub, generic secret).
- Relays events to one or more `WebhookDestination` URLs with retry/backoff.

## Requirements

- PHP 8.0+
- Laravel 10+

## High-level architecture

External service → `/webhooks/{endpointKey}` → WebhookEndpoint → WebhookCall (logged) → ProcessWebhookCall(Job) → RelayWebhookCall(Job) → Destinations.

## Planned structure

- `app/Models/WebhookEndpoint.php`
- `app/Models/WebhookCall.php`
- `app/Models/WebhookDestination.php`
- `app/Http/Controllers/WebhookReceiverController.php`
- `app/Jobs/ProcessWebhookCall.php`
- `app/Jobs/RelayWebhookCall.php`
- `app/Services/SignatureVerifier/*Verifier.php`
- `config/webhook-hub.php`
- `routes/api.php`
- `resources/views/webhook-hub/index.blade.php`

## Roadmap

- [ ] Implement migrations for endpoints, calls and destinations.
- [ ] Implement WebhookReceiverController with basic logging.
- [ ] Implement signature verifiers for Stripe/GitHub/generic.
- [ ] Implement relay jobs with retry/backoff.
- [ ] Add read-only dashboard for recent webhook calls.

## License

MIT
