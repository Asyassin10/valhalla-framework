<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use Throwable;
use Valhalla\Framework\Core\Exceptions\HttpException;
use Valhalla\Framework\Log\Logger;

final class ErrorHandler
{
    public function __construct(
        private readonly Logger $logger,
        private readonly bool $debug = false
    ) {}

    public function render(Throwable $throwable): Response
    {
        $status = $throwable instanceof HttpException ? $throwable->statusCode() : 500;

        $this->logger->logError($throwable);
        $payload = [
            'error' => [
                'message' => $throwable->getMessage(),
                'type' => $throwable::class,
            ],
        ];

        if ($this->debug) {
            $payload['error']['trace'] = explode(PHP_EOL, $throwable->getTraceAsString());
        }

        return Response::json($payload, $status);
    }
}
