<?php
declare(strict_types=1);

final class Autoloader
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        spl_autoload_register(static function (string $class): void {
            if (strpos($class, 'App\\') === 0) {
                $relative = substr($class, 4);
                if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*(?:\\\\[A-Za-z][A-Za-z0-9_]*)*$/D', $relative)) {
                    return;
                }
                // Keep the existing lowercase core directory on case-sensitive hosts.
                if (strpos($relative, 'Core\\') === 0) {
                    $relative = 'core\\' . substr($relative, 5);
                }
                $file = __DIR__ . '/../' . str_replace('\\', '/', $relative) . '.php';
                if (is_file($file)) {
                    require_once $file;
                }
                return;
            }
            if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $class)) {
                return;
            }
            // Only load classes that match our project naming (no namespaces).
            $baseDir = __DIR__ . '/../';

            $candidates = [
                $baseDir . 'controllers/' . $class . '.php',
                $baseDir . 'models/' . $class . '.php',
                $baseDir . 'core/' . $class . '.php',
                $baseDir . 'helpers/' . $class . '.php',
            ];

            foreach ($candidates as $file) {
                if (is_file($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }
}

