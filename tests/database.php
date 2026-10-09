<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
if (PHP_SAPI !== 'cli' || getenv('APP_ENV') === 'production') {
    throw new RuntimeException('Database tests require a nonproduction CLI environment.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$password = getenv('DB_PASSWORD');
if ($password === false) {
    $password = getenv('DB_PASS') ?: '';
}
$server = new mysqli(getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_USER') ?: 'root', $password, '', (int)(getenv('DB_PORT') ?: 3306));
$server->set_charset('utf8mb4');
$name = 'spacivo_loop_' . bin2hex(random_bytes(6)) . '_test';
$created = false;
$temporaryDirectory = sys_get_temp_dir() . '/spacivo-migrations-' . bin2hex(random_bytes(6));
$passed = 0;
$failed = 0;
$check = static function (bool $condition, string $label) use (&$passed, &$failed): void {
    echo ($condition ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    $condition ? $passed++ : $failed++;
};
try {
    $server->query('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    $server->select_db($name);
    $baseline = json_decode(file_get_contents(__DIR__ . '/../database/schema/legacy-baseline.json'), true, 512, JSON_THROW_ON_ERROR);
    $runner = new App\Core\MigrationRunner($server, __DIR__ . '/../database/migrations');
    $expectedMigrations = ['001_legacy_baseline.php', '002_organizations.php', '003_password_recovery.php', '004_categories.php', '005_inventory.php', '006_inventory_metadata.php'];
    $check($runner->migrate() === $expectedMigrations, 'Fresh application migrations install');
    $check(App\Core\SchemaBaseline::inspect($server, $baseline) === [], 'Installed schema matches captured baseline');
    $check($runner->migrate() === [], 'Application migrations are idempotent');
    $check((int)$server->query('SELECT @@SESSION.FOREIGN_KEY_CHECKS')->fetch_row()[0] === 1, 'Foreign key checks restored');
    $check((int)$server->query('SELECT COUNT(*) FROM users')->fetch_row()[0] === 0, 'Baseline exports no user records');

    $server->query("INSERT INTO roles (name, slug, description) VALUES ('Fixture', 'fixture', 'Preserve fixture')");
    $server->query('DELETE FROM schema_migrations');
    $check($runner->migrate() === $expectedMigrations, 'Existing matching schema is adopted');
    $check((int)$server->query('SELECT COUNT(*) FROM roles')->fetch_row()[0] === 1, 'Adoption preserves existing rows');
    $server->query('ALTER TABLE amenities ADD COLUMN unexpected_column INT NULL');
    $server->query('DELETE FROM schema_migrations');
    try {
        $runner->migrate();
        $check(false, 'Different existing schema rejected');
    } catch (RuntimeException $error) {
        $check(strpos($error->getMessage(), 'amenities') !== false, 'Different existing schema rejected');
    }
    $check((int)$server->query('SELECT COUNT(*) FROM schema_migrations')->fetch_row()[0] === 0, 'Failed baseline is not recorded');
    $server->query('ALTER TABLE amenities DROP COLUMN unexpected_column');
    $check($runner->migrate() === $expectedMigrations, 'Baseline retry succeeds after repair');

    mkdir($temporaryDirectory, 0700, true);
    $migrationFile = $temporaryDirectory . '/001_restartable.php';
    $failMarker = $temporaryDirectory . '/fail_once';
    file_put_contents($failMarker, '1');
    file_put_contents($migrationFile, '<?php return static function (mysqli $db): void {'
        . '$db->query("CREATE TABLE IF NOT EXISTS restart_probe (id INT PRIMARY KEY)");'
        . 'if (is_file(__DIR__ . "/fail_once")) { throw new RuntimeException("Injected failure"); } };');
    $failureRunner = new App\Core\MigrationRunner($server, $temporaryDirectory);
    try {
        $failureRunner->migrate();
        $check(false, 'Injected partial DDL failure surfaces');
    } catch (RuntimeException $error) {
        $check($error->getMessage() === 'Injected failure', 'Injected partial DDL failure surfaces');
    }
    $check($server->query("SELECT 1 FROM schema_migrations WHERE version = '001_restartable.php'")->num_rows === 0, 'Failed migration is not marked applied');
    $lock = 'spacivo:migrations:' . substr(hash('sha256', $name), 0, 40);
    $statement = $server->prepare('SELECT IS_FREE_LOCK(?)');
    $statement->bind_param('s', $lock);
    $statement->execute();
    $check((int)$statement->get_result()->fetch_row()[0] === 1, 'Migration lock released after failure');
    unlink($failMarker);
    $check($failureRunner->migrate() === ['001_restartable.php'], 'Restartable partial migration recovers');
    file_put_contents($migrationFile, "\n// changed\n", FILE_APPEND);
    try {
        $failureRunner->migrate();
        $check(false, 'Changed applied migration rejected');
    } catch (RuntimeException $error) {
        $check(strpos($error->getMessage(), 'Applied migration changed') !== false, 'Changed applied migration rejected');
    }
    require __DIR__ . '/http-smoke.php';
    require __DIR__ . '/organization-security.php';
    require __DIR__ . '/password-recovery.php';
    require __DIR__ . '/categories.php';
    require __DIR__ . '/properties.php';
    require __DIR__ . '/property-metadata.php';
} catch (Throwable $error) {
    $check(false, 'Integration error: ' . get_class($error) . ' ' . $error->getMessage());
} finally {
    // Only remove the exact database this process created, never an existing database.
    if ($created && preg_match('/^spacivo_loop_[a-f0-9]{12}_test$/D', $name)) {
        $server->query('DROP DATABASE `' . $name . '`');
    }
    foreach (glob($temporaryDirectory . '/*') ?: [] as $file) {
        unlink($file);
    }
    if (is_dir($temporaryDirectory)) {
        rmdir($temporaryDirectory);
    }
    $server->close();
}
echo 'Database assertions passed: ' . $passed . '; failed: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
