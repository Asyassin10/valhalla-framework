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
        self::assertContains('GET    /health', $output);
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
        self::assertFileExists($tmp.'/sample-service/composer.json');
        self::assertFileExists($tmp.'/sample-service/.env');

        $composerJson = file_get_contents($tmp.'/sample-service/composer.json') ?: '';
        self::assertStringContainsString('"valhalla/framework"', $composerJson);
    }
}
