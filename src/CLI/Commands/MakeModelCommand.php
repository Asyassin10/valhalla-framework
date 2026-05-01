<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Throwable;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\ORM\OrmManager;

final class MakeModelCommand implements Command
{
    public function signature(): string
    {
        return 'make:model';
    }

    public function description(): string
    {
        return 'Generate an ORM model or entity.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? null;

        if ($name === null) {
            $console->error('Usage: valhalla make:model NAME');

            return 1;
        }

        try {
            $manager = new OrmManager($context->config(), $context->workingPath());
            $path = $manager->driver()->makeModel($name);
            $console->line(sprintf('Created model: %s', $path));

            return 0;
        } catch (Throwable $throwable) {
            $console->error($throwable->getMessage());

            return 1;
        }
    }
}
