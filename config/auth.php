<?php

declare(strict_types=1);

return [
    'jwt' => [
        'secret' => env('VALHALLA_JWT_SECRET', 'change-me'),
        'issuer' => env('VALHALLA_JWT_ISSUER', 'valhalla'),
        'audience' => env('VALHALLA_JWT_AUDIENCE', 'valhalla-services'),
        'ttl' => (int) env('VALHALLA_JWT_TTL', 3600),
        'algo' => env('VALHALLA_JWT_ALGO', 'HS256'),
    ],
    'api_tokens' => [
        env('VALHALLA_API_TOKEN', 'local-service-token') => [
            'id' => 'service.local',
            'name' => 'Local Service',
            'roles' => ['service'],
        ],
    ],
];
