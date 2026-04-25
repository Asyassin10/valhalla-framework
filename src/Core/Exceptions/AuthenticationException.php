<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core\Exceptions;

final class AuthenticationException extends HttpException
{
    public function __construct(string $message = 'Unauthorized')
    {
        parent::__construct($message, 401);
    }
}
