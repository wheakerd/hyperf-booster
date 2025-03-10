<?php
declare(strict_types=1);

use Wheakerd\HyperfBooster\Application;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);

require BASE_PATH . '/vendor/autoload.php';

// Self-called anonymous function that creates its own scope and keep the global namespace clean.
(fn() => (new Application)->run())();