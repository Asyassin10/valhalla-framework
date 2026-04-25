<?php

declare(strict_types=1);

return [
    'jwt' => [
        'secret' => env('VALHALLA_JWT_SECRET', 'basic-service-secret'),
        'issuer' => 'basic-service',
        'audience' => 'basic-service-clients',
        'ttl' => 3600,
        'algo' => 'HS256',
    ],
    'api_tokens' => [
        'basic-service-token' => [
            'id' => 'svc-basic',
            'name' => 'Basic Service',
            'roles' => ['service'],
        ],
    ],
];
