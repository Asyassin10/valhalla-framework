<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\CLI\Templates;

final class MakeMiddlewareCommand implements Command
{
    public function signature(): string
    {
        return 'make:middleware';
    }

    public function description(): string
    {
        return 'Generate a middleware class.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? 'GeneratedMiddleware';
        $class = str_ends_with($name, 'Middleware') ? $name : $name . 'Middleware';
        $path = $context->workingPath() . '/src/Middleware/' . $class . '.php';
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, Templates::middleware('App\Middleware', $class));
        $console->line(sprintf('Middleware created: %s', $path));
        return 0;
    }
}
