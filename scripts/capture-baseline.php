<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../bootstrap/app.php';
$directory = __DIR__ . '/../database/schema';
if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
}
if (is_file($directory . '/legacy-baseline.json')) {
    fwrite(STDERR, "Baseline already exists. Do not recapture a versioned baseline.\n");
    exit(1);
}
$db = Database::get();
$baseline = [];
$tables = $db->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = \'BASE TABLE\' ORDER BY TABLE_NAME');
while ($row = $tables->fetch_assoc()) {
    $table = $row['TABLE_NAME'];
    if ($table === 'schema_migrations') {
        continue;
    }
    if (!preg_match('/^[a-z][a-z0-9_]*$/D', $table)) {
        throw new RuntimeException('Unexpected table name in baseline.');
    }
    $create = $db->query('SHOW CREATE TABLE `' . $table . '`')->fetch_row()[1];
    // Remove data-dependent identity counters. Export structure only, never records.
    $create = preg_replace('/\sAUTO_INCREMENT=\d+/', '', $create);
    $baseline[$table] = $create;
}
file_put_contents($directory . '/legacy-baseline.json', json_encode($baseline, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
echo 'Captured structure for ' . count($baseline) . ' tables; no records exported.' . PHP_EOL;
