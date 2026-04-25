<?php

declare(strict_types=1);

return [
    'registry' => env('VALHALLA_AGENT_REGISTRY', storage_path('agents/registry.json')),
    'pid_dir' => env('VALHALLA_AGENT_PID_DIR', storage_path('agents/pids')),
    'default_host' => env('VALHALLA_AGENT_HOST', '127.0.0.1'),
    'default_port' => (int) env('VALHALLA_AGENT_PORT', 9501),
];
