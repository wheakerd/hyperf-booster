<?php
declare (strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Exception\Exception;

/**
 * @property string $vendorDir
 * @DefinitionSourceFactory
 * @\Wheakerd\HyperfBooster\DefinitionSourceFactory
 */
final class DefinitionSourceFactory
{
    public function __invoke(): DefinitionSource
    {
        $configFromProviders = [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];

        return new DefinitionSource($serverDependencies);
    }
}