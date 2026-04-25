<?php

declare(strict_types=1);

return [
    'http' => [
        'timeout' => (float) env('VALHALLA_HTTP_TIMEOUT', 3.0),
        'retries' => (int) env('VALHALLA_HTTP_RETRIES', 2),
        'retry_delay_ms' => (int) env('VALHALLA_HTTP_RETRY_DELAY_MS', 100),
        'circuit_breaker' => [
            'threshold' => (int) env('VALHALLA_CIRCUIT_THRESHOLD', 3),
            'cooldown' => (int) env('VALHALLA_CIRCUIT_COOLDOWN', 10),
        ],
    ],
];
