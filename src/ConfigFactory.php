<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Config\Config;
use Hyperf\Config\ProviderConfig;
use Hyperf\Di\ReflectionManager;
use Hyperf\Support\Composer;
use LogicException;
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

    private function readConfig(): array
    {
        $configFactory = Composer::getJsonContent()['extra']['config'] ?? null;

        if (null === $configFactory) {
            throw new LogicException('Missing composer.json[\'extra\'][\'config\'].');
        }

        if (!class_exists($configFactory)) {
            throw new LogicException(
                sprintf('The config factory [%s] does not exist.', $configFactory)
            );
        }

        if (!ReflectionManager::reflectClass($configFactory)->isInstantiable()) {
            throw new LogicException(
                sprintf('The config factory [%s] can\'t be instantiated.', $configFactory)
            );
        }

        $config = (new $configFactory)();
        return is_array($config) ? $config : [];
    }
}