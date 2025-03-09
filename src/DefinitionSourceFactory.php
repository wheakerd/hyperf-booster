<?php
declare (strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Exception\Exception;
use Phar;

/**
 * @DefinitionSourceFactory
 * @\Wheakerd\HyperfBooster\DefinitionSourceFactory
 */
final class DefinitionSourceFactory
{
    /**
     * @return DefinitionSource
     * @throws Exception
     */
    public function __invoke(): DefinitionSource
    {
        if (!defined('BASE_PATH')) {
            throw new Exception('BASE_PATH is not defined.');
        }

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