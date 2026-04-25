<?php

declare(strict_types=1);

return [
    'http' => [
        'timeout' => 3.0,
        'retries' => 2,
        'retry_delay_ms' => 100,
        'circuit_breaker' => [
            'threshold' => 3,
            'cooldown' => 10,
        ],
    ],
];
