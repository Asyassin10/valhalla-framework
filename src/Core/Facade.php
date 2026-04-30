<?php

namespace Valhalla\Framework\Core;

abstract class Facade
{
    protected static Application $app;

    public static function setApplication(Application $app): void
    {
        static::$app = $app;
    }

    abstract protected static function getFacadeAccessor(): string;

    public static function __callStatic($method, $args)
    {
        $instance = static::$app->make(
            static::getFacadeAccessor()
        );

        return $instance->$method(...$args);
    }
}
