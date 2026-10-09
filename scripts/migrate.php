<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../bootstrap/app.php';
if (PHP_VERSION_ID < 70400) {
    fwrite(STDERR, "PHP 7.4 or later is required.\n");
    exit(1);
}
if (!in_array('--apply', $argv, true)) {
    $runner = new App\Core\MigrationRunner(Database::get(), __DIR__ . '/../database/migrations');
    echo json_encode(['migrations' => $runner->status()], JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}
if (getenv('APP_ENV') === 'production') {
    fwrite(STDERR, "Production migrations require the approved release procedure.\n");
    exit(1);
}
$runner = new App\Core\MigrationRunner(Database::get(), __DIR__ . '/../database/migrations');
echo json_encode(['applied' => $runner->migrate()], JSON_PRETTY_PRINT) . PHP_EOL;
