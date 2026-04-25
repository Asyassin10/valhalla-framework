<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core\Exceptions;

final class NotFoundException extends HttpException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('No route matched [%s].', $path), 404);
    }
}
