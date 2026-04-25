<?php

declare(strict_types=1);

namespace Valhalla\Framework\Agents;

interface AgentTaskHandler
{
    public function handle(string $task, array $payload = []): array;
}
