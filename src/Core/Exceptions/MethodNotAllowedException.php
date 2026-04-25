<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core\Exceptions;

final class MethodNotAllowedException extends HttpException
{
    public function __construct(string $method, string $path)
    {
        parent::__construct(sprintf('Method [%s] not allowed for [%s].', $method, $path), 405);
    }
}
