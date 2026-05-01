<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\Core\Application;

final class RoutesListCommand implements Command
{
    public function signature(): string
    {
        return 'routes:list';
    }

    public function description(): string
    {
        return 'List registered routes from routes/api.php.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $app = new Application($context->workingPath());
        $routesFile = $context->workingPath().'/routes/api.php';

        if (! is_file($routesFile)) {
            $console->error('No routes/api.php file found.');

            return 1;
        }

        $app->loadRoutes($routesFile);

        foreach ($app->router()->routes() as $route) {
            $handler = $this->formatHandler($route->handler);
            $console->line($handler === null
                ? sprintf('%-6s %s', $route->method, $route->uri)
                : sprintf('%-6s %s %s', $route->method, $route->uri, $handler)
            );
        }

        return 0;
    }

    private function formatHandler(mixed $handler): ?string
    {
        if (! is_array($handler) || count($handler) !== 2) {
            return null;
        }

        [$className, $methodName] = $handler;

        if (! is_string($className) || ! is_string($methodName)) {
            return null;
        }

        return basename(str_replace('\\', '/', $className)).'@'.$methodName;
    }
}
