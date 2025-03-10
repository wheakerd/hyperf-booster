<?php
declare(strict_types=1);

putenv('SCAN_CACHEABLE=(true)');

$dir = dirname(__DIR__, 2);
$cwd = getcwd();

switch (true) {
    case file_exists($dir . '/.bin.php'):
        require_once $dir . '/.bin.php';
        break;
    case file_exists($dir . '/../.bin.php'):
        require_once $dir . '/../.bin.php';
        break;
    case file_exists($dir . '/bin/hyperf.php'):
        require_once $dir . '/bin/hyperf.php';
        break;
    case file_exists($dir . '/../bin/hyperf.php'):
        require_once $dir . '/../bin/hyperf.php';
        break;
    case file_exists($cwd . '/bin/hyperf.php'):
        require_once $cwd . '/bin/hyperf.php';
        break;
    default:
        require_once 'bin/hyperf.php';
        break;
}