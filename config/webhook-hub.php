<?php

return [
    'relay' => [
        'max_attempts' => (int) env('WEBHOOK_HUB_RELAY_MAX_ATTEMPTS', 3),
        'backoff' => array_map(
            static fn (string $item): int => (int) trim($item),
            explode(',', (string) env('WEBHOOK_HUB_RELAY_BACKOFF', '5,30,120'))
        ),
    ],

    'retention_days' => (int) env('WEBHOOK_HUB_RETENTION_DAYS', 30),
    'http_timeout' => (int) env('WEBHOOK_HUB_HTTP_TIMEOUT', 10),
];
