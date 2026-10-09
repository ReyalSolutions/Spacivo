<?php
declare(strict_types=1);

final class Database
{
    private static ?mysqli $conn = null;

    public static function get(): mysqli
    {
        if (self::$conn instanceof mysqli) {
            return self::$conn;
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $dbHost = getenv('DB_HOST') ?: 'localhost';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASSWORD');
        if ($dbPass === false) {
            $dbPass = getenv('DB_PASS') ?: '';
        }
        $dbName = getenv('DB_NAME') ?: 'tenant_boarding';

        $dbPort = (int)(getenv('DB_PORT') ?: 3306);
        $db = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

        $db->set_charset('utf8mb4');
        self::$conn = $db;
        return self::$conn;
    }
}

