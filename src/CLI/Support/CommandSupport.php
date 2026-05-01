<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Support;

use Valhalla\Framework\CLI\Console;

final class CommandSupport
{
    public static function binaryExists(string $binary): bool
    {
        $result = shell_exec(sprintf('command -v %s 2>/dev/null', escapeshellarg($binary)));

        return is_string($result) && trim($result) !== '';
    }

    public static function run(string $command, ?string $workingPath = null): int
    {
        if ($workingPath !== null) {
            $command = sprintf('cd %s && %s', escapeshellarg($workingPath), $command);
        }

        passthru($command, $exitCode);

        return $exitCode;
    }

    public static function writeFile(string $path, string $contents, Console $console, bool $confirmOverwrite = true): bool
    {
        if (is_file($path) && $confirmOverwrite && ! $console->confirm(sprintf('File [%s] already exists. Overwrite it?', $path), false)) {
            $console->line(sprintf('Skipped [%s].', $path));

            return false;
        }

        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, $contents);

        return true;
    }

    public static function appendEnvValues(string $path, array $values): void
    {
        $existing = is_file($path) ? file_get_contents($path) ?: '' : '';
        $lines = $existing === '' ? [] : explode("\n", rtrim($existing, "\n"));

        foreach ($values as $key => $value) {
            $pattern = sprintf('/^%s=/', preg_quote($key, '/'));
            $found = false;

            foreach ($lines as $index => $line) {
                if (preg_match($pattern, $line) === 1) {
                    $lines[$index] = sprintf('%s=%s', $key, $value);
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $lines[] = sprintf('%s=%s', $key, $value);
            }
        }

        file_put_contents($path, implode("\n", array_filter($lines, static fn (string $line): bool => $line !== ''))."\n");
    }

    public static function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', trim($value));

        return str_replace(' ', '', ucwords($value));
    }

    public static function snake(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', self::studly($value)) ?? $value;

        return strtolower(str_replace(' ', '_', $value));
    }
}
