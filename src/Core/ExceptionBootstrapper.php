<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use ErrorException;
use Throwable;
use Valhalla\Framework\Log\Logger;

final class ExceptionBootstrapper
{
    public static function bootstrap(Logger $logger, bool $debug = false): void
    {
        // Convert recoverable PHP errors into ErrorException so they propagate
        // through the normal exception pipeline and reach ErrorHandler::render().
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        // Fatal errors (E_ERROR, E_PARSE, …) cannot be thrown; catch them here
        // after the script dies and emit a JSON response directly.
        register_shutdown_function(function () use ($logger): void {
            $error = error_get_last();

            if (!$error) {
                return;
            }

            $fatalTypes = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];

            if (!in_array($error['type'], $fatalTypes, true)) {
                return;
            }

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }

            $payload = [
                'error' => [
                    'message' => $error['message'],
                    'type' => 'FatalError',
                    'file' => $error['file'],
                    'line' => $error['line'],
                ],
            ];

            $logger->critical($error['message'], $payload['error']);

            ob_clean(); // 🔥 IMPORTANT: clear partial HTML output from PHP server
            echo json_encode($payload);

            exit;
        });
        // Safety net for exceptions that escape outside Application::handle()
        // (e.g. thrown during the boot sequence itself).
 set_exception_handler(function (Throwable $e) use ($logger, $debug): void {

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }

    $logger->logError($e);

    ob_clean(); // 🔥 remove partial output

    $payload = [
        'error' => [
            'message' => $e->getMessage(),
            'type' => $e::class,
        ],
    ];

    if ($debug) {
        $payload['error']['file']  = $e->getFile();
        $payload['error']['line']  = $e->getLine();
        $payload['error']['trace'] = explode(PHP_EOL, $e->getTraceAsString());
    }

    echo json_encode($payload);
    exit;
});
    }
}
