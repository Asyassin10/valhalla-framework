<?php

namespace Valhalla\Framework\Log;

use InvalidArgumentException;
use Throwable;

class Logger
{
    // Defaults
    private const DEFAULT_CHANNEL_NAME = 'app';

    private const DEFAULT_ROTATION_DAYS = 60;

    private const LEVELS = [
        'DEBUG' => 100,
        'INFO' => 200,
        'NOTICE' => 250,
        'WARNING' => 300,
        'ERROR' => 400,
        'CRITICAL' => 500,
        'ALERT' => 550,
        'EMERGENCY' => 600,
    ];

    protected array $drivers = [
        'stack',
        'single',
        'daily',
    ];

    protected array $levels = [
        'DEBUG',
        'INFO',
        'NOTICE',
        'WARNING',
        'ERROR',
        'CRITICAL',
        'ALERT',
        'EMERGENCY',
    ];

    /**
     * @var array<string, LogChannel>
     */
    protected array $channels;

    protected string $path;

    protected LogChannel $channel;

    protected LogChannel $ErrorChannel;

    protected string $level;

    protected string $driver;

    protected int $days;

    private function shouldLog(string $messageLevel, string $channelLevel): bool
    {
        return self::LEVELS[$messageLevel] >= self::LEVELS[$channelLevel];
    }

    private function getProcessedLogLevel(string $messageLevel): string
    {
        if ($this->isMainChannel()) {
            return $messageLevel;
        }
        $channelLevel = $this->channel->getLevel();

        return $this->shouldLog($messageLevel, $channelLevel)
            ? $messageLevel
            : $channelLevel;
    }

    private function isMainChannel(): bool
    {
        return $this->channel->getName() === self::DEFAULT_CHANNEL_NAME;
    }

    private function buildChannels(array $config = [])
    {
        if (
            isset($config['channels']) &&
            is_array($config['channels']) &&
            ! empty($config['channels']) &&
            array_keys($config['channels']) !== range(0, count($config['channels']) - 1)
        ) {
            foreach ($config['channels'] as $name => $channelConfig) {
                $this->channels[$name] = new LogChannel($name, $channelConfig);
            }
        }
    }

    public function __construct(array $config = [])
    {
        // Get Driver
        $driver = $config['driver'] ?? null;
        if ($driver === null) {
            throw new InvalidArgumentException('Driver is required.');
        }

        if (! in_array($driver, $this->drivers, true)) {
            throw new InvalidArgumentException(
                "Driver [$driver] is not supported."
            );
        }
        $this->driver = $driver;

        // Get level
        $level = $config['level'] ?? null;
        if ($level === null) {
            throw new InvalidArgumentException('Level is required.');
        }
        if (! in_array($level, $this->levels, true)) {
            throw new InvalidArgumentException(
                "Level [$level] is not supported."
            );
        }
        $this->level = $level;
        $this->days = self::DEFAULT_ROTATION_DAYS;

        $this->path = $config['path'] ?? storage_path('logs');
        $this->channel = new LogChannel(self::DEFAULT_CHANNEL_NAME, ['driver' => 'single']);
        $this->ErrorChannel = new LogChannel(self::DEFAULT_CHANNEL_NAME, ['driver' => 'single']);
        $this->buildChannels($config);
    }

    public function channel(string $channel)
    {
        if (! isset($this->channels[$channel])) {
            throw new InvalidArgumentException(
                "Log channel [$channel] does not exist."
            );
        }

        $this->channel = $this->channels[$channel];
        $this->driver = $this->channels[$channel]->getDriver();

        return $this;
    }

    public function info(mixed $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(mixed $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function warning(mixed $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function debug(mixed $message, array $context = []): void
    {
        $this->write('DEBUG', $message, $context);
    }

    public function notice(mixed $message, array $context = []): void
    {
        $this->write('NOTICE', $message, $context);
    }

    public function critical(mixed $message, array $context = []): void
    {
        $this->write('CRITICAL', $message, $context);
    }

    public function alert(mixed $message, array $context = []): void
    {
        $this->write('ALERT', $message, $context);
    }

    public function emergency(mixed $message, array $context = []): void
    {
        $this->write('EMERGENCY', $message, $context);
    }

    public function logError(Throwable $exception, array $context = []): void
    {

        $context = array_merge([
            'exception' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
        ], $context);
        $message = $exception->getMessage();

        $this->write('EMERGENCY', $message, $context, true);
    }

    protected function normalize(string $level, mixed $message, array $context, bool $isError): array
    {
        return [
            'timestamp' => date('c'),
            'level' => $level,
            'channel' => $isError == false ? $this->channel->getName() : $this->ErrorChannel->getName(),
            'message' => $this->normalizeValue($message),
            'context' => $this->normalizeValue($context),
        ];
    }

    public function jsonFormatter(array $data): string
    {

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            $json = json_encode([
                'level' => 'ERROR',
                'message' => 'Failed to encode log record',
            ]);
        }

        return $json.PHP_EOL;
    }

    protected function normalizeValue(mixed $value): mixed
    {
        // 1. scalar
        if (is_string($value) || is_numeric($value) || is_bool($value) || $value === null) {
            return $value;
        }

        // 2. indexed array → string
        if (is_array($value) && array_is_list($value)) {
            return implode(', ', array_map(
                fn ($v) => $this->normalizeValue($v),
                $value
            ));
        }

        // 3. associative array → structured JSON
        if (is_array($value)) {
            $clean = [];

            foreach ($value as $k => $v) {
                $clean[$k] = $this->normalizeValue($v);
            }

            return $clean; // will be JSON encoded later
        }

        // 4. object → extract properties (key => value)
        if (is_object($value)) {
            $clean = [];

            $ref = new \ReflectionClass($value);

            foreach ($ref->getProperties() as $property) {
                $property->setAccessible(true);

                $clean[$property->getName()] = $this->normalizeValue(
                    $property->getValue($value)
                );
            }

            return $clean;
        }

        // 5. fallback
        return '[UNSUPPORTED]';
    }

    protected function extractObjectData(object $object): array
    {
        $data = [];

        foreach (get_object_vars($object) as $key => $value) {
            $data[$key] = $this->normalizeValue($value);
        }

        return $data;
    }

    protected function rotateLogs(): void
    {
        $driver = $this->isMainChannel()
            ? $this->driver
            : $this->channel->getDriver();

        $path = $this->isMainChannel()
            ? $this->path
            : $this->channel->getPath();

        $days = $this->isMainChannel()
            ? $this->days
            : $this->channel->getDays();

        if ($driver !== 'daily') {
            return;
        }

        $files = glob($path.'/*.log');

        if (! $files) {
            return;
        }

        $threshold = time() - ($days * 86400);

        foreach ($files as $file) {
            $modifiedTime = filemtime($file);

            if ($modifiedTime !== false && $modifiedTime < $threshold) {
                @unlink($file);
            }
        }
    }

    protected function write(
        string $level,
        mixed $message,
        array $context = [],
        bool $isError = false
    ): void {
        $this->rotateLogs();
        $log_level = $this->getProcessedLogLevel($level);
        $record = $this->normalize($log_level, $message, $context, $isError);
        $formatted = $this->format($record);
        $file = $this->getLogFile($isError);
        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, recursive: true);
        }

        file_put_contents(
            $this->getLogFile($isError),
            $formatted,
            FILE_APPEND | LOCK_EX
        );
    }

    protected function interpolate(
        string $message,
        array $context = []
    ): string {
        foreach ($context as $key => $value) {
            $message = str_replace(
                '{'.$key.'}',
                (string) $value,
                $message
            );
        }

        return $message;
    }

    public function format(array $record): string
    {
        $date = date('Y-m-d H:i:s', strtotime($record['timestamp']));

        $channel = $record['channel'] ?? 'app';
        $level = $record['level'] ?? 'INFO';

        $message = $record['message'] ?? '';

        // ensure message becomes string safely
        if (is_array($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return "[{$date}] {$channel}.{$level}: {$message}".PHP_EOL;
    }

    protected function getLogFile(bool $IsError): string
    {
        if ($this->isMainChannel()) {
            $channel = self::DEFAULT_CHANNEL_NAME;
        } elseif ($IsError) {
            $channel = $this->ErrorChannel->getName();
        } else {
            $channel = $this->channel->getName();
        }

        if ($this->driver == 'daily') {
            return $this->path.'/'.$channel.'-'.date('Y-m-d').'.log';
        }

        return $this->path.'/'.$channel.'.log';
    }
}
