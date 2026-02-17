# Laravel Webhook Ingest & Relay Hub

Mini webhook platform in Laravel focused on **secure ingest**, **operational logging**, and **reliable relay**.

This repository is a portfolio-friendly, open-source version of a webhook hub pattern used in real SaaS integrations.

## What it does

- Receives incoming webhooks on a dynamic endpoint: `/api/webhooks/{endpointKey}`.
- Resolves endpoint configuration from database (`webhook_endpoints`).
- Verifies request authenticity by provider type:
  - Stripe (`Stripe-Signature` HMAC flow, minimal implementation)
  - GitHub (`X-Hub-Signature-256`)
  - Generic token (`X-Webhook-Token` or `?token=`)
- Stores every accepted call in `webhook_calls` with payload + headers + processing status.
- Dispatches asynchronous jobs:
  - `ProcessWebhookCall` -> selects destinations
  - `RelayWebhookCall` -> sends payload to configured destinations with retry/backoff
- Exposes simple read-only operational panel at `/webhook-hub` (local/testing only).

## Architecture flow

External Service (Stripe/GitHub/Woo/etc.)
-> `POST /api/webhooks/{endpointKey}`
-> `WebhookReceiverController`
-> `webhook_calls` row created
-> `ProcessWebhookCall` (job)
-> `RelayWebhookCall` (job per destination)
-> Destination URLs (internal APIs / microservices)

## Domain model

### `webhook_endpoints`

Represents incoming sources.

Fields:

- `name`
- `key` (e.g. `stripe-main`, `woo-shop-a`)
- `type` (`stripe`, `github`, `generic`)
- `secret`
- `enabled`

### `webhook_calls`

Represents each accepted incoming webhook.

Fields:

- `endpoint_id`
- `uuid`
- `payload` (JSON)
- `headers` (JSON)
- `status` (`received`, `processed`, `failed`)
- `attempts`
- `last_error`
- `relay_results` (JSON)

### `webhook_destinations`

Represents relay targets.

Fields:

- `endpoint_id`
- `url`
- `secret` (used to generate `X-WebhookHub-Signature`)
- `enabled`
- `last_status`
- `last_response_code`

## Key files

- `app/Http/Controllers/WebhookReceiverController.php`
- `app/Http/Controllers/WebhookHubController.php`
- `app/Services/SignatureVerifier/*`
- `app/Jobs/ProcessWebhookCall.php`
- `app/Jobs/RelayWebhookCall.php`
- `config/webhook-hub.php`
- `routes/api.php`
- `routes/web.php`

## Quick start

1. Install dependencies:

```bash
composer install
```

1. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

1. Configure DB + queue in `.env`.

1. Run migrations:

```bash
php artisan migrate
```

1. Start queue worker:

```bash
php artisan queue:work
```

## Example configuration

Create endpoint:

```php
WebhookEndpoint::query()->create([
    'name' => 'Stripe Main',
    'key' => 'stripe-main',
    'type' => 'stripe',
    'secret' => 'whsec_xxx',
    'enabled' => true,
]);
```

Create destination:

```php
WebhookDestination::query()->create([
    'endpoint_id' => 1,
    'url' => 'https://internal-api.example/events/stripe',
    'secret' => 'relay_secret',
    'enabled' => true,
]);
```

Incoming webhook URL:

```text
POST https://your-app.test/api/webhooks/stripe-main
```

## Retry/backoff + retention

See `config/webhook-hub.php`:

- `relay.max_attempts`
- `relay.backoff`
- `retention_days`
- `http_timeout`

Environment vars:

- `WEBHOOK_HUB_RELAY_MAX_ATTEMPTS`
- `WEBHOOK_HUB_RELAY_BACKOFF`
- `WEBHOOK_HUB_RETENTION_DAYS`
- `WEBHOOK_HUB_HTTP_TIMEOUT`

## Tests

- `tests/Feature/WebhookReceiverTest.php`
- `tests/Feature/RelayWebhookCallTest.php`

Run:

```bash
composer test
```

## Code quality

```bash
composer analyse
composer format
```

## Why this repo matters

This repo demonstrates:

- secure webhook ingestion,
- provider-specific signature verification,
- asynchronous processing + relay,
- retries/backoff patterns,
- operational visibility and auditability.

It complements WooCommerce/Laravel integration repos by covering event-driven inbound integration architecture.
