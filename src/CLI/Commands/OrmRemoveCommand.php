<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class OrmRemoveCommand implements Command
{
    public function signature(): string
    {
        return 'orm:remove';
    }

    public function description(): string
    {
        return 'Remove ORM configuration from the project.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $path = $context->workingPath().'/config/orm.php';

        if (! is_file($path)) {
            $console->error('No ORM configuration found.');

            return 1;
        }

        unlink($path);
        $console->line('Removed [config/orm.php].');
        $console->line('Remove the Composer ORM packages manually if you no longer need them.');

        return 0;
    }
}
