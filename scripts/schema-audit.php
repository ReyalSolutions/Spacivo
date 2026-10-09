<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../bootstrap/app.php';
$db = Database::get();
if (in_array('--verify-baseline', $argv, true)) {
    $definitions = json_decode(file_get_contents(__DIR__ . '/../database/schema/legacy-baseline.json'), true, 512, JSON_THROW_ON_ERROR);
    $missing = App\Core\SchemaBaseline::inspect($db, $definitions);
    echo json_encode(['baseline_matches' => $missing === [], 'missing_tables' => $missing], JSON_PRETTY_PRINT) . PHP_EOL;
    exit($missing === [] ? 0 : 1);
}
$schema = [];
$tables = $db->query('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = \'BASE TABLE\' ORDER BY TABLE_NAME');
while ($table = $tables->fetch_assoc()) {
    $name = $table['TABLE_NAME'];
    $statement = $db->prepare('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION');
    $statement->bind_param('s', $name);
    $statement->execute();
    $schema[$name] = ['engine' => $table['ENGINE'], 'columns' => $statement->get_result()->fetch_all(MYSQLI_ASSOC)];
    $statement->close();
}
// Metadata only: never export user records, credentials, or column default values.
echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
