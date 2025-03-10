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
        if (! defined('BASE_PATH')) {
            throw new Exception('BASE_PATH is not defined.');
        }

        if (! defined('ROOT_PATH')) {
            throw new Exception('ROOT_PATH is not defined.');
        }

        $configFromProviders = [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];

        return new DefinitionSource($serverDependencies);
    }
}