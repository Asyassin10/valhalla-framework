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
use Valhalla\Framework\CLI\Commands\InstallRuntimeCommand;
use Valhalla\Framework\CLI\Commands\InstallCommand;
use Valhalla\Framework\CLI\Commands\MakeMigrationCommand;
use Valhalla\Framework\CLI\Commands\MakeControllerCommand;
use Valhalla\Framework\CLI\Commands\MakeMiddlewareCommand;
use Valhalla\Framework\CLI\Commands\MakeModelCommand;
use Valhalla\Framework\CLI\Commands\MakeServiceCommand;
use Valhalla\Framework\CLI\Commands\MigrateCommand;
use Valhalla\Framework\CLI\Commands\MigrateDiffCommand;
use Valhalla\Framework\CLI\Commands\MigrateRollbackCommand;
use Valhalla\Framework\CLI\Commands\NewProjectCommand;
use Valhalla\Framework\CLI\Commands\OrmInstallCommand;
use Valhalla\Framework\CLI\Commands\OrmRemoveCommand;
use Valhalla\Framework\CLI\Commands\QueueWorkCommand;
use Valhalla\Framework\CLI\Commands\RuntimeCommand;
use Valhalla\Framework\CLI\Commands\RoutesListCommand;

final class Application
{
    /** @var array<string, Command> */
    private array $commands = [];

    public function __construct(private readonly string $basePath)
    {
        foreach ([
            new NewProjectCommand(),
            new MakeControllerCommand(),
            new MakeMiddlewareCommand(),
            new MakeServiceCommand(),
            new InstallCommand(),
            new AuthGenerateCommand(),
            new RoutesListCommand(),
            new OrmInstallCommand(),
            new OrmRemoveCommand(),
            new MigrateCommand(),
            new MigrateRollbackCommand(),
            new MigrateDiffCommand(),
            new MakeModelCommand(),
            new MakeMigrationCommand(),
            new InstallRuntimeCommand('docker'),
            new InstallRuntimeCommand('podman'),
            new RuntimeCommand('up', 'Start the configured container stack.'),
            new RuntimeCommand('down', 'Stop the configured container stack.'),
            new RuntimeCommand('build', 'Build the configured container stack.'),
            new RuntimeCommand('logs', 'Tail logs from the configured container stack.'),
            new RuntimeCommand('shell', 'Open a shell in the app container.'),
            new QueueWorkCommand(),
            new AgentInstallCommand(),
            new AgentStartCommand(),
            new AgentStopCommand(),
            new AgentCallCommand(),
            new AgentListCommand(),
            new AgentServeCommand(),
        ] as $command) {
            $this->commands[$command->signature()] = $command;
        }
    }

    public function run(array $argv): int
    {
        $console = new Console();
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
