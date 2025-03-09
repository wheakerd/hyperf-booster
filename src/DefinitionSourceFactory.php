<?php
declare (strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Support\Composer;
use Phar;

/**
 * @property string $vendorDir
 * @DefinitionSourceFactory
 * @\Wheakerd\HyperfBooster\DefinitionSourceFactory
 */
final class DefinitionSourceFactory
{
    public function __invoke(): DefinitionSource
    {
        !defined('BASE_PATH') && define('BASE_PATH',
            (fn() => $this->vendorDir)->call(Composer::getLoader())
        );

        !defined('ROOT_PATH') && define('ROOT_PATH',
            pharEnable() ? dirname(Phar::running(false)) : BASE_PATH,
        );

        $configFromProviders = [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];

        return new DefinitionSource($serverDependencies);
    }
}