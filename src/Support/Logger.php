<?php

declare(strict_types=1);

namespace Valhalla\Framework\Support;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Stringable;

final class Logger
{
    private MonologLogger $logger;

    public function __construct(Config $config)
    {
        $channel = (string) $config->get('logging.channel', 'valhalla');
        $path = (string) $config->get('logging.path', storage_path('logs/valhalla.log'));
        $level = Level::fromName(strtoupper((string) $config->get('logging.level', 'DEBUG')));

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $handler = new StreamHandler($path, $level);
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));

        $this->logger = new MonologLogger($channel);
        $this->logger->pushHandler($handler);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info((string) $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning((string) $message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error((string) $message, $context);
    }
}
