<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Contract\ConfigInterface;

/**
 * @ConfigProvider
 * @\Wheakerd\HyperfBooster\ConfigProvider
 */
final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConfigInterface::class => ConfigFactory::class,
            ],
            'publish' => [
                [
                    'id'          => 'config',
                    'description' => 'The config for hyperf-booster.',
                    'source'      => __DIR__ . '/../publish/ConfigProvider.php',
                    'destination' => BASE_PATH . '/app/ConfigProvider.php',
                ],
            ],
        ];
    }
}