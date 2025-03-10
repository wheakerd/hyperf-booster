<?php
declare(strict_types=1);

use Wheakerd\HyperfBooster\Application;
use function Wheakerd\HyperfBooster\getBasePath;
use function Wheakerd\HyperfBooster\pharEnable;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

!defined('BASE_PATH') && define('BASE_PATH', getBasePath());

!defined('ROOT_PATH') && define('ROOT_PATH',
    pharEnable() ? dirname(Phar::running(false)) : BASE_PATH,
);

// Self-called anonymous function that creates its own scope and keep the global namespace clean.
(fn() => (new Application)->run())();