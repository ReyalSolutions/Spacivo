<?php
declare(strict_types=1);

return static function (mysqli $db): void {
    $file = __DIR__ . '/../schema/legacy-baseline.json';
    if (hash_file('sha256', $file) !== 'f7712ff849c1f86d56269e1f8cd64262241bd430d3ab2857b164097453627dd5') {
        throw new RuntimeException('Versioned baseline was modified. Add a new migration instead.');
    }
    $definitions = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    App\Core\SchemaBaseline::apply($db, $definitions);
};
