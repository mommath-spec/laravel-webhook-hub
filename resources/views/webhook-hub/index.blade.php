<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Webhook Hub</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif; margin: 2rem; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th, td { border: 1px solid #ddd; padding: 0.6rem; text-align: left; }
        th { background: #f8f8f8; }
        .ok { color: #0a7d2c; font-weight: 600; }
        .bad { color: #a30f0f; font-weight: 600; }
    </style>
</head>
<body>
<h1>Webhook Hub</h1>

<h2>Endpoints</h2>
<table>
    <thead>
    <tr>
        <th>Name</th>
        <th>Key</th>
        <th>Type</th>
        <th>Enabled</th>
        <th>Destinations</th>
        <th>Calls</th>
    </tr>
    </thead>
    <tbody>
    @forelse($endpoints as $endpoint)
        <tr>
            <td>{{ $endpoint->name }}</td>
            <td><code>{{ $endpoint->key }}</code></td>
            <td>{{ $endpoint->type }}</td>
            <td>{!! $endpoint->enabled ? '<span class="ok">yes</span>' : '<span class="bad">no</span>' !!}</td>
            <td>{{ $endpoint->destinations_count }}</td>
            <td>{{ $endpoint->calls_count }}</td>
        </tr>
    @empty
        <tr><td colspan="6">No endpoints yet.</td></tr>
    @endforelse
    </tbody>
</table>

<h2>Latest Calls</h2>
<table>
    <thead>
    <tr>
        <th>UUID</th>
        <th>Endpoint</th>
        <th>Status</th>
        <th>Attempts</th>
        <th>Received at</th>
    </tr>
    </thead>
    <tbody>
    @forelse($calls as $call)
        <tr>
            <td><a href="{{ route('webhook-hub.show', ['uuid' => $call->uuid]) }}">{{ $call->uuid }}</a></td>
            <td>{{ $call->endpoint?->name ?? 'n/a' }}</td>
            <td>{{ $call->status }}</td>
            <td>{{ $call->attempts }}</td>
            <td>{{ $call->created_at }}</td>
        </tr>
    @empty
        <tr><td colspan="5">No calls yet.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
