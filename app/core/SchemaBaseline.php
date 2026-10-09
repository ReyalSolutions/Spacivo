<?php
declare(strict_types=1);

namespace App\Core;

final class SchemaBaseline
{
    private static function normalize(string $sql): string
    {
        $sql = preg_replace('/\sAUTO_INCREMENT=\d+/i', '', $sql);
        return preg_replace('/\s+/', ' ', trim($sql));
    }

    public static function inspect(\mysqli $db, array $definitions): array
    {
        $existing = [];
        $tables = $db->query('SHOW TABLES');
        while ($table = $tables->fetch_row()) {
            $existing[$table[0]] = true;
        }
        $missing = [];
        foreach ($definitions as $table => $sql) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/D', $table) || !is_string($sql)
                || strpos($sql, 'CREATE TABLE `' . $table . '` (') !== 0) {
                throw new \RuntimeException('Invalid baseline definition.');
            }
            if (!isset($existing[$table])) {
                $missing[] = $table;
                continue;
            }
            $current = $db->query('SHOW CREATE TABLE `' . $table . '`')->fetch_row()[1];
            if (self::normalize($current) !== self::normalize($sql)) {
                throw new \RuntimeException('Baseline schema differs for table: ' . $table);
            }
        }
        return $missing;
    }

    public static function apply(\mysqli $db, array $definitions): void
    {
        // Inspect all existing definitions before any DDL; never silently adopt a different schema.
        $missing = self::inspect($db, $definitions);
        $foreignKeys = (int)$db->query('SELECT @@SESSION.FOREIGN_KEY_CHECKS')->fetch_row()[0];
        $db->query('SET SESSION FOREIGN_KEY_CHECKS = 0');
        try {
            foreach ($missing as $table) {
                $db->query($definitions[$table]);
            }
        } finally {
            $db->query('SET SESSION FOREIGN_KEY_CHECKS = ' . $foreignKeys);
        }
        if (self::inspect($db, $definitions) !== []) {
            throw new \RuntimeException('Baseline installation incomplete.');
        }
    }
}
