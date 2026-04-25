<?php

declare(strict_types=1);

namespace Valhalla\Framework\Support;

final class Paths
{
    private static string $basePath = '';

    public static function setBasePath(string $basePath): void
    {
        self::$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public static function basePath(): string
    {
        if (self::$basePath === '') {
            self::$basePath = getcwd() ?: '.';
        }

        return self::$basePath;
    }
}
