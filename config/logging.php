<?php

declare(strict_types=1);

return [
    'driver' => env('VALHALLA_LOG_DRIVER', 'single'),
    'channel' => env('VALHALLA_LOG_CHANNEL', 'valhalla'),
    'path' => env('VALHALLA_LOG_PATH', storage_path('logs')),
    'level' => env('VALHALLA_LOG_LEVEL', 'DEBUG'),
];
