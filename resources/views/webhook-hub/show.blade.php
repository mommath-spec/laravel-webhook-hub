<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Webhook Call {{ $call->uuid }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif; margin: 2rem; }
        pre { background: #111; color: #e8e8e8; padding: 1rem; border-radius: 6px; overflow: auto; }
        .meta { margin-bottom: 1rem; }
        .meta code { background: #f2f2f2; padding: 0.1rem 0.3rem; border-radius: 4px; }
    </style>
</head>
<body>
<p><a href="{{ route('webhook-hub.index') }}">&larr; Back to dashboard</a></p>
<h1>Webhook Call Details</h1>

<div class="meta">
    <p><strong>UUID:</strong> <code>{{ $call->uuid }}</code></p>
    <p><strong>Endpoint:</strong> {{ $call->endpoint?->name }} ({{ $call->endpoint?->key }})</p>
    <p><strong>Status:</strong> {{ $call->status }}</p>
    <p><strong>Attempts:</strong> {{ $call->attempts }}</p>
    <p><strong>Created at:</strong> {{ $call->created_at }}</p>
    <p><strong>Last error:</strong> {{ $call->last_error ?? '-' }}</p>
</div>

<h2>Payload</h2>
<pre>{{ json_encode($call->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>

<h2>Headers</h2>
<pre>{{ json_encode($call->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>

<h2>Relay Results</h2>
<pre>{{ json_encode($call->relay_results ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
</body>
</html>
