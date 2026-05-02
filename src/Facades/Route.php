<?php

declare(strict_types=1);

namespace Valhalla\Framework\Facades;

use Valhalla\Framework\Core\Facade;

final class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'router';
    }
}
