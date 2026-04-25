<?php

declare(strict_types=1);

namespace Valhalla\Framework\Tests;

use PHPUnit\Framework\TestCase;
use Valhalla\Framework\Agents\AgentClient;
use Valhalla\Framework\Agents\AgentServer;
use Valhalla\Framework\Agents\EchoAgentHandler;

final class AgentTest extends TestCase
{
    private array $processes = [];

    protected function tearDown(): void
    {
        foreach ($this->processes as $process) {
            proc_terminate($process);
            proc_close($process);
        }
    }

    public function testAgentCallReturnsStructuredResponse(): void
    {
        $port = 9912;
        $process = proc_open(sprintf('php %s/support/agent_server.php %d', dirname(__DIR__) . '/tests', $port), [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        self::assertIsResource($process);
        $this->processes[] = $process;
        usleep(300000);

        $client = new AgentClient();
        $response = $client->call('127.0.0.1', $port, 'summarize', ['text' => 'hello']);

        self::assertSame('ok', $response['status']);
        self::assertSame('summarize', $response['result']['task']);
    }
}
