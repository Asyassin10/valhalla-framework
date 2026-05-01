<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI;

final class Console
{
    public function line(string $message): void
    {
        fwrite(STDOUT, $message.PHP_EOL);
    }

    public function error(string $message): void
    {
        fwrite(STDERR, $message.PHP_EOL);
    }

    public function ask(string $prompt, ?string $default = null): string
    {
        $suffix = $default === null ? '' : sprintf(' [%s]', $default);
        fwrite(STDOUT, $prompt.$suffix.': ');
        $input = trim((string) fgets(STDIN));

        if ($input === '' && $default !== null) {
            return $default;
        }

        return $input;
    }

    public function confirm(string $prompt, bool $default = false): bool
    {
        $defaultLabel = $default ? 'Y/n' : 'y/N';
        $value = strtolower($this->ask(sprintf('%s (%s)', $prompt, $defaultLabel)));

        if ($value === '') {
            return $default;
        }

        return in_array($value, ['y', 'yes'], true);
    }

    public function choice(string $prompt, array $choices, string $default): string
    {
        $this->line(sprintf('%s [%s]', $prompt, implode('/', $choices)));

        do {
            $value = $this->ask('Choose', $default);
        } while (! in_array($value, $choices, true));

        return $value;
    }
}
