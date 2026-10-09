<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../bootstrap/app.php';
$checks = [
    'php_7_4_or_later' => PHP_VERSION_ID >= 70400,
    'mysqli' => extension_loaded('mysqli'),
    'json' => extension_loaded('json'),
    'storage_writable' => is_writable(__DIR__ . '/../storage/logs'),
    'environment_configured' => is_file(__DIR__ . '/../.env') || getenv('DB_NAME') !== false,
];
if (in_array('--database', $argv, true)) {
    try {
        $checks['database'] = (int)Database::get()->query('SELECT 1')->fetch_row()[0] === 1;
    } catch (Throwable $error) {
        $checks['database'] = false;
    }
}
echo json_encode($checks, JSON_PRETTY_PRINT) . PHP_EOL;
exit(in_array(false, $checks, true) ? 1 : 0);
