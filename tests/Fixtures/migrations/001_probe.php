<?php
declare(strict_types=1);

return static function (mysqli $db): void {
    $db->query('CREATE TABLE IF NOT EXISTS migration_probe (id INT PRIMARY KEY) ENGINE=InnoDB');
};
