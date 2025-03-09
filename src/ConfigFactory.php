<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Config\Config;
use Hyperf\Config\ProviderConfig;
use Hyperf\Support\Composer;
use Psr\Container\ContainerInterface;

/**
 * @ConfigFactory
 * @\Wheakerd\HyperfBooster\ConfigFactory
 */
final class ConfigFactory
{
    public function __invoke(ContainerInterface $container): Config
    {
        $config = $this->readConfig();

        $merged = array_merge_recursive(ProviderConfig::load(), $config);

        return new Config($merged);
    }

    /**
     * @return array
     */
    private function readConfig(): array
    {
        $configFactory = Composer::getJsonContent()['extra']['config'] ?? null;

        if (is_string($configFactory) && class_exists($configFactory)) {
            $config = (new $configFactory)();
            return is_array($config) ? $config : [];
        }

        trigger_error('Missing composer.json[\'extra\'][\'config\'].', E_USER_WARNING);

        return [];
    }
}