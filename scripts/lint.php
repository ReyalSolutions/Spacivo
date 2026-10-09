<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$failures = 0;
$checked = 0;
// The nested reyal_solutions application and historical diagnostic scripts are separate from active application code.
foreach (['index.php', 'app', 'admin', 'bootstrap', 'config', 'public', 'scripts', 'tests', 'database/migrations'] as $directory) {
    $path = $root . '/' . $directory;
    if (!is_dir($path) && !is_file($path)) {
        continue;
    }
    $iterator = is_file($path) ? [new SplFileInfo($path)]
        : new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $normalizedPath = str_replace('\\', '/', $file->getPathname());
        if ($file->getExtension() !== 'php' || strpos($normalizedPath, '/public/uploads/') !== false
            || substr($normalizedPath, -19) === '/public/phpinfo.php') {
            continue;
        }
        $output = [];
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $status);
        $checked++;
        if ($status !== 0) {
            $failures++;
            echo implode(PHP_EOL, $output) . PHP_EOL;
        }
    }
}
echo 'Linted ' . $checked . ' files; failures: ' . $failures . PHP_EOL;
exit($failures === 0 ? 0 : 1);
