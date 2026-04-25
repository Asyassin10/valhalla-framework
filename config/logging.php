<?php

declare(strict_types=1);

return [
    'channel' => env('VALHALLA_LOG_CHANNEL', 'valhalla'),
    'path' => env('VALHALLA_LOG_PATH', storage_path('logs/valhalla.log')),
    'level' => env('VALHALLA_LOG_LEVEL', 'debug'),
];
