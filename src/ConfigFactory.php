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
final class ConfigFactory extends \Hyperf\Config\ConfigFactory
{
    public function __invoke(ContainerInterface $container): Config
    {
        $config = $this->readConfig();

        if (null === $config) {
            return parent::__invoke($container);
        }

        $merged = array_merge_recursive(ProviderConfig::load(), $config);

        return new Config($merged);
    }

    /**
     * @return array|null
     */
    private function readConfig(): ?array
    {
        $configFactory = Composer::getJsonContent()['extra']['config'] ?? null;

        if (is_string($configFactory) && class_exists($configFactory)) {
            $config = (new $configFactory)();
            return is_array($config) ? $config : [];
        }

        return null;
    }
}