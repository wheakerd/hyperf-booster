<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace Wheakerd\HyperfBooster\Hyperf\Di;

use Hyperf\Config\ProviderConfig;
use function Hyperf\Support\value;

/**
 * @see \Wheakerd\HyperfBooster\ScanConfig
 * @ScanConfig
 * @\Wheakerd\HyperfBooster\Di\ScanConfig
 */
final class ScanConfig
{
    private static ?ScanConfig $instance = null;

    /**
     * @param array $paths the paths should be scanned everytime
     */
    public function __construct(
        readonly private bool  $cacheable,
        readonly private array $paths = [],
        readonly private array $dependencies = [],
        readonly private array $ignoreAnnotations = [],
        readonly private array $globalImports = [],
        readonly private array $collectors = [],
        readonly private array $classMap = [],
        readonly private array $aspects = [],
        readonly private array $config = [],
    )
    {
    }

    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    public function getAspects(): array
    {
        return $this->aspects;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function getIgnoreAnnotations(): array
    {
        return $this->ignoreAnnotations;
    }

    public function getGlobalImports(): array
    {
        return $this->globalImports;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getClassMap(): array
    {
        return $this->classMap;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public static function instance(string $configProvider): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        [$config, $serverDependencies, $cacheable] = self::initConfigByConfigProvider($configProvider);

        return self::$instance = new self(
            $cacheable,
            $config['paths'] ?? [],
            $serverDependencies ?? [],
            $config['ignore_annotations'] ?? [],
            $config['global_imports'] ?? [],
            $config['collectors'] ?? [],
            $config['class_map'] ?? [],
            $config['aspects'] ?? [],
            $config,
        );
    }

    /**
     * @formatter:on
     * @param string $configProvider
     * @return array
     */
    public static function initConfigByConfigProvider(string $configProvider): array
    {
        $config = (new $configProvider)();
        $configFromProviders = [];

        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];
        if (isset($providerConfigs['dependencies'])) {
            $serverDependencies = array_replace($serverDependencies, $providerConfigs['dependencies']);
        }

        $config = self::allocateConfigValue($configFromProviders['annotations'] ?? [], $config);

        // Load the config/autoload/annotations.php and merge the config
        if (isset($providerConfigs['annotations'])) {
            $config = self::allocateConfigValue($providerConfigs['annotations'], $config);
        }

        if (isset($providerConfigs['aspects'])) {
            $config = self::allocateConfigValue($providerConfigs['aspects'], $config);
        }

        // Merge the config
        $appEnv = $providerConfigs['app_env'] ?? 'dev';
        $cacheable = value($providerConfigs['scan_cacheable'] ?? $appEnv === 'prod');
        if (isset($providerConfigs['annotations'])) {
            $config = self::allocateConfigValue($providerConfigs['annotations'], $config);
        }

        return [$config, $serverDependencies, $cacheable];
    }

    private static function allocateConfigValue(array $content, array $config): array
    {
        if (!isset($content['scan'])) {
            return $config;
        }
        foreach ($content['scan'] as $key => $value) {
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
            if (!is_array($value)) {
                $value = [$value];
            }
            $config[$key] = array_merge($config[$key], $value);
        }
        return $config;
    }
}