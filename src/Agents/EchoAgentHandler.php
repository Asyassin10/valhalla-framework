<?php

declare(strict_types=1);

namespace Valhalla\Framework\Agents;

final class EchoAgentHandler implements AgentTaskHandler
{
    public function handle(string $task, array $payload = []): array
    {
        return [
            'task' => $task,
            'payload' => $payload,
            'message' => sprintf('Agent processed task [%s].', $task),
        ];
    }
}
