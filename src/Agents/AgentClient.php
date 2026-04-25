<?php

declare(strict_types=1);

namespace Valhalla\Framework\Agents;

use RuntimeException;

final class AgentClient
{
    public function call(string $host, int $port, string $task, array $payload = []): array
    {
        $socket = @stream_socket_client(sprintf('tcp://%s:%d', $host, $port), $errno, $errstr, 5);

        if (!is_resource($socket)) {
            throw new RuntimeException(sprintf('Unable to connect to agent: %s', $errstr ?: $errno));
        }

        $message = [
            'id' => bin2hex(random_bytes(8)),
            'task' => $task,
            'payload' => $payload,
        ];

        fwrite($socket, json_encode($message, JSON_UNESCAPED_SLASHES) . PHP_EOL);
        $response = trim((string) fgets($socket));
        fclose($socket);

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid response from agent.');
        }

        return $decoded;
    }
}
