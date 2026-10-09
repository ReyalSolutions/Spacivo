<?php
declare(strict_types=1);
// Compatibility entry point: both roles use the canonical listing controller and view.
$_GET['url'] = 'admin/houses';
require __DIR__ . '/../index.php';
