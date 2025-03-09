<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Phar;

/**
 * Determine whether it is located in a PHAR environment.
 * @return bool
 */
function phar_enable(): bool
{
    if (!extension_loaded('phar')) {
        return false;
    }

    return !strlen(Phar::running(false));
}