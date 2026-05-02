<?php

declare(strict_types=1);

use Valhalla\Framework\Agents\AgentServer;
use Valhalla\Framework\Agents\EchoAgentHandler;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$port = (int) ($argv[1] ?? 9912);
$server = new AgentServer('127.0.0.1', $port, new EchoAgentHandler());
$server->run();
