<?php
declare(strict_types=1);

// Compatibility for historical CLI scripts. Active code uses Database::get().
require_once __DIR__ . '/../bootstrap/app.php';
$db = Database::get();
$GLOBALS['db'] = $db;

