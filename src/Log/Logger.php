<?php


namespace Valhalla\Framework\Log;

class Logger
{
    protected string $path;
    protected string $channel;
    protected bool $daily;

    public function __construct(array $config = [])
    {
        $this->path = $config['path'] ?? storage_path('logs');
        $this->channel = $config['channel'] ?? 'app';
        $this->daily = $config['daily'] ?? false;
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
    public function level(string $level, mixed $message, array $context = []): void
    {
        $this->write($level, $message, $context);
    }
    protected function normalize(string $level, mixed $message, array $context): array
    {
        return [
            'timestamp' => date('c'),
            'level'     => $level,
            'channel'   => $this->channel,
            'message'   => $this->normalizeValue($message),
            'context'   => $this->normalizeValue($context),
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
                'message' => 'Failed to encode log record'
            ]);
        }
        return $json . PHP_EOL;
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
                fn($v) => $this->normalizeValue($v),
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

    protected function write(
        string $level,
        mixed $message,
        array $context = []
    ): void {
        $record = $this->normalize($level, $message, $context);

        $formatted = $this->format($record);
        $file = $this->getLogFile();
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, recursive: true);
        }

        file_put_contents(
            $this->getLogFile(),
            $formatted,
            FILE_APPEND
        );
    }

    protected function interpolate(
        string $message,
        array $context = []
    ): string {
        foreach ($context as $key => $value) {
            $message = str_replace(
                '{' . $key . '}',
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
        $level   = $record['level'] ?? 'INFO';

        $message = $record['message'] ?? '';

        // ensure message becomes string safely
        if (is_array($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return "[{$date}] {$channel}.{$level}: {$message}" . PHP_EOL;
    }
    protected function getLogFile(): string
    {
        if ($this->daily) {
            return $this->path . '/' . date('Y-m-d') . '.log';
        }

        return $this->path . '/' . $this->channel . '.log';
    }
}
