<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\CLI\Templates;

final class MakeServiceCommand implements Command
{
    public function signature(): string
    {
        return 'make:service';
    }

    public function description(): string
    {
        return 'Generate a service helper class.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? 'GeneratedService';
        $class = str_ends_with($name, 'Service') ? $name : $name . 'Service';
        $path = $context->workingPath() . '/src/Services/' . $class . '.php';
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, Templates::service('App\Services', $class));
        $console->line(sprintf('Service created: %s', $path));
        return 0;
    }
}
