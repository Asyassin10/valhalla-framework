<?php

namespace Valhalla\Framework\Log;

use Valhalla\Framework\Core\Facade;

class Log extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'logger';
    }
}
