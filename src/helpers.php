<?php

declare(strict_types=1);
use Valhalla\Framework\Support\Env;
use Valhalla\Framework\Support\Paths;

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = Paths::basePath();

        return $path === '' ? $base : $base.DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        $config = base_path('config');

        return $path === '' ? $config : $config.DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $storage = base_path('storage');

        return $path === '' ? $storage : $storage.DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}
