<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Phar;

/**
 * Determine whether it is located in a PHAR environment.
 * @return bool
 */
function pharEnable(): bool
{
    return !strlen(Phar::running(false));
}