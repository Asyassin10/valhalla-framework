<?php

namespace Valhalla\Framework\Log;

use Valhalla\Framework\Core\Facade;


/**                                                                                                                                                                                   
 * @method static void info(mixed $message, array $context = [])
 * @method static void error(mixed $message, array $context = [])
 * @method static void warning(mixed $message, array $context = [])                                                                                                                   
 * @method static void debug(mixed $message, array $context = [])
 * @method static void notice(mixed $message, array $context = [])                                                                                                                    
 * @method static void critical(mixed $message, array $context = [])                                                                                                                  
 * @method static void alert(mixed $message, array $context = [])
 * @method static void emergency(mixed $message, array $context = [])                                                                                                                 
 * @method static void logError(\Throwable $exception, array $context = [])                                                                                                           
 * @method static \Valhalla\Framework\Log\Logger channel(string $channel)
 */
class Log extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'logger';
    }
}
