<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Auth\AuthManager;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class AuthGenerateCommand implements Command
{
    public function signature(): string
    {
        return 'auth:generate';
    }

    public function description(): string
    {
        return 'Generate a JWT for a sample or provided user.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        Auth::setManager(new AuthManager($context->config()));
        $user = [
            'id' => $arguments[0] ?? 1,
            'name' => $arguments[1] ?? 'Valhalla User',
        ];

        $console->line(Auth::generateToken($user));
        return 0;
    }
}
