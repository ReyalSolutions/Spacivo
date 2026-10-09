<?php
declare(strict_types=1);

namespace App\Core;

final class ExceptionHandler
{
    public static function register(string $logDirectory): void
    {
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        if (!is_dir($logDirectory) && !mkdir($logDirectory, 0700, true) && !is_dir($logDirectory)) {
            throw new \RuntimeException('Unable to initialize application logs.');
        }
        ini_set('error_log', $logDirectory . '/php.log');
        set_exception_handler(static function (\Throwable $error) use ($logDirectory): void {
            $requestId = bin2hex(random_bytes(8));
            // Do not log exception messages: SQL and integration errors can contain secrets.
            $entry = json_encode([
                'time' => gmdate('c'), 'request_id' => $requestId,
                'type' => get_class($error), 'file' => $error->getFile(), 'line' => $error->getLine(),
            ], JSON_UNESCAPED_SLASHES);
            error_log($entry . PHP_EOL, 3, $logDirectory . '/application.log');
            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, 'Application error. Reference: ' . $requestId . PHP_EOL);
                exit(1);
            }
            http_response_code(500);
            header('X-Request-ID: ' . $requestId);
            if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.', 'request_id' => $requestId]);
            } else {
                header('Content-Type: text/html; charset=utf-8');
                echo '<h1>Something went wrong</h1><p>Please try again. Reference: ' . $requestId . '</p>';
            }
        });
    }
}
