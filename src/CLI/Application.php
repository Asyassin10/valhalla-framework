<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI;

use Valhalla\Framework\CLI\Commands\AgentCallCommand;
use Valhalla\Framework\CLI\Commands\AgentInstallCommand;
use Valhalla\Framework\CLI\Commands\AgentListCommand;
use Valhalla\Framework\CLI\Commands\AgentServeCommand;
use Valhalla\Framework\CLI\Commands\AgentStartCommand;
use Valhalla\Framework\CLI\Commands\AgentStopCommand;
use Valhalla\Framework\CLI\Commands\AuthGenerateCommand;
use Valhalla\Framework\CLI\Commands\InstallCommand;
use Valhalla\Framework\CLI\Commands\MakeControllerCommand;
use Valhalla\Framework\CLI\Commands\MakeMiddlewareCommand;
use Valhalla\Framework\CLI\Commands\MakeServiceCommand;
use Valhalla\Framework\CLI\Commands\NewProjectCommand;
use Valhalla\Framework\CLI\Commands\RoutesListCommand;

final class Application
{
    /** @var array<string, Command> */
    private array $commands = [];

    public function __construct(private readonly string $basePath)
    {
        foreach ([
            new NewProjectCommand,
            new MakeControllerCommand,
            new MakeMiddlewareCommand,
            new MakeServiceCommand,
            new InstallCommand,
            new AuthGenerateCommand,
            new RoutesListCommand,
            new AgentInstallCommand,
            new AgentStartCommand,
            new AgentStopCommand,
            new AgentCallCommand,
            new AgentListCommand,
            new AgentServeCommand,
        ] as $command) {
            $this->commands[$command->signature()] = $command;
        }
    }

    public function run(array $argv): int
    {
        $console = new Console;
        $context = new Context($this->basePath);
        $input = array_slice($argv, 1);
        $signature = implode(' ', array_slice($input, 0, 2));

        if (isset($this->commands[$signature])) {
            $command = $this->commands[$signature];
            $arguments = array_slice($input, 2);

            return $command->handle($arguments, $console, $context);
        }

        $signature = $input[0] ?? 'help';

        if (isset($this->commands[$signature])) {
            return $this->commands[$signature]->handle(array_slice($input, 1), $console, $context);
        }

        $console->line('Valhalla CLI');
        $console->line('');

        foreach ($this->commands as $command) {
            $console->line(sprintf('  %-22s %s', $command->signature(), $command->description()));
        }

        return 0;
    }
}
