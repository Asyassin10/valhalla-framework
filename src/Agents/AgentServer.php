<?php

declare(strict_types=1);

namespace Valhalla\Framework\Agents;

use RuntimeException;

final class AgentServer
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly AgentTaskHandler $handler
    ) {
    }

    public function run(): void
    {
        $server = @stream_socket_server(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr);

        if (!is_resource($server)) {
            throw new RuntimeException(sprintf('Unable to start agent server: %s', $errstr ?: $errno));
        }

        while ($connection = @stream_socket_accept($server, -1)) {
            $payload = trim((string) fgets($connection));
            $request = json_decode($payload, true);

            if (!is_array($request)) {
                fwrite($connection, json_encode([
                    'status' => 'error',
                    'error' => 'Invalid JSON payload.',
                ]) . PHP_EOL);
                fclose($connection);
                continue;
            }

            try {
                $result = $this->handler->handle((string) ($request['task'] ?? 'unknown'), (array) ($request['payload'] ?? []));
                $response = [
                    'id' => $request['id'] ?? null,
                    'status' => 'ok',
                    'result' => $result,
                    'error' => null,
                ];
            } catch (\Throwable $throwable) {
                $response = [
                    'id' => $request['id'] ?? null,
                    'status' => 'error',
                    'result' => null,
                    'error' => $throwable->getMessage(),
                ];
            }

            fwrite($connection, json_encode($response, JSON_UNESCAPED_SLASHES) . PHP_EOL);
            fclose($connection);
        }
    }
}
