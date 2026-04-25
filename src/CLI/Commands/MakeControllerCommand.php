<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\CLI\Templates;

final class MakeControllerCommand implements Command
{
    public function signature(): string
    {
        return 'make:controller';
    }

    public function description(): string
    {
        return 'Generate a controller class.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? 'GeneratedController';
        $class = str_ends_with($name, 'Controller') ? $name : $name . 'Controller';
        $path = $context->workingPath() . '/src/Controllers/' . $class . '.php';
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, Templates::controller('App\Controllers', $class));
        $console->line(sprintf('Controller created: %s', $path));
        return 0;
    }
}
