<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI;

interface Command
{
    public function signature(): string;

    public function description(): string;

    public function handle(array $arguments, Console $console, Context $context): int;
}
