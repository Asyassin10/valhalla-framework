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
}
