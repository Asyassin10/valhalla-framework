<?php

declare(strict_types=1);

namespace Valhalla\Framework\ORM\Contracts;

interface OrmDriverInterface
{
    public function boot(array $config): void;

    public function migrate(): void;

    public function rollback(): void;

    public function makeModel(string $name): string;

    public function makeMigration(string $name): string;
}
