<?php

declare(strict_types=1);

namespace Valhalla\Framework\Auth;

use Valhalla\Framework\Core\Request;

final class Auth
{
    private static ?AuthManager $manager = null;

    public static function setManager(AuthManager $manager): void
    {
        self::$manager = $manager;
    }

    public static function manager(): AuthManager
    {
        if (! self::$manager instanceof AuthManager) {
            throw new \RuntimeException('Auth manager has not been configured.');
        }

        return self::$manager;
    }

    public static function attempt(Request $request): ?array
    {
        return self::manager()->attempt($request);
    }

    public static function check(): bool
    {
        return self::manager()->check();
    }

    public static function user(): ?array
    {
        return self::manager()->user();
    }

    public static function generateToken(array $user): string
    {
        return self::manager()->generateToken($user);
    }
}
