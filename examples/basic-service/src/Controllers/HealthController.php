<?php

declare(strict_types=1);

namespace App\Controllers;

use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;

final class HealthController
{
    public function __invoke(Request $request): Response
    {
        return Response::json([
            'service' => 'basic-service',
            'status' => 'ok',
            'path' => $request->path(),
        ]);
    }
}
