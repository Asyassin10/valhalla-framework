<?php

declare(strict_types=1);

namespace Valhalla\Framework\Tests;

use PHPUnit\Framework\TestCase;

final class CLITest extends TestCase
{
    public function test_routes_list_prints_configured_routes(): void
    {
        $command = sprintf('php %s/bin/valhalla routes:list', dirname(__DIR__));
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode);
        self::assertContains('GET    /health HealthController@index', $output);
        self::assertContains('GET    /users/{id}', $output);
    }

    public function test_new_project_works_from_any_path(): void
    {
        $tmp = sys_get_temp_dir().'/valhalla-cli-'.bin2hex(random_bytes(4));
        mkdir($tmp, 0777, true);

        $command = sprintf('cd %s && php %s/bin/valhalla new project sample-service', escapeshellarg($tmp), dirname(__DIR__));
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode);
        self::assertDirectoryExists($tmp.'/sample-service/public');
        self::assertDirectoryExists($tmp.'/sample-service/routes');
        self::assertDirectoryExists($tmp.'/sample-service/config');
        self::assertDirectoryExists($tmp.'/sample-service/database/migrations');
        self::assertFileExists($tmp.'/sample-service/composer.json');
        self::assertFileExists($tmp.'/sample-service/.env');
        self::assertFileExists($tmp.'/sample-service/config/database.php');

        $composerJson = file_get_contents($tmp.'/sample-service/composer.json') ?: '';
        self::assertStringContainsString('"asyassin10/valhalla-framework"', $composerJson);
    }

    public function test_make_controller_generates_index_method(): void
    {
        $tmp = sys_get_temp_dir().'/valhalla-cli-'.bin2hex(random_bytes(4));
        mkdir($tmp, 0777, true);
        mkdir($tmp.'/src/Controllers', 0777, true);

        $command = sprintf('cd %s && php %s/bin/valhalla make:controller Users', escapeshellarg($tmp), dirname(__DIR__));
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode);
        self::assertFileExists($tmp.'/src/Controllers/UsersController.php');

        $controller = file_get_contents($tmp.'/src/Controllers/UsersController.php') ?: '';
        self::assertStringContainsString('namespace App\\Controllers;', $controller);
        self::assertStringContainsString('public function index(Request $request): Response', $controller);
        self::assertStringContainsString("return Response::json(['message' => 'ok']);", $controller);
    }

    public function test_cli_help_lists_orm_and_container_commands(): void
    {
        $command = sprintf('php %s/bin/valhalla', dirname(__DIR__));
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode);
        $text = implode("\n", $output);
        self::assertStringContainsString('orm:install            Install an ORM driver (eloquent or doctrine).', $text);
        self::assertStringContainsString('install:docker         Generate a docker container setup.', $text);
        self::assertStringContainsString('make:model             Generate an ORM model or entity.', $text);
    }
}
