<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Contract\ConfigInterface;
use Wheakerd\HyperfBooster\Command\WatchCommand;

/**
 * @ConfigProvider
 * @\Wheakerd\HyperfBooster\ConfigProvider
 */
final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [
                WatchCommand::class,
            ],
            'dependencies' => [
                ConfigInterface::class => ConfigFactory::class,
            ],
            'publish' => [
                [
                    'id'          => 'bin',
                    'description' => 'The bin for runner.',
                    'source'      => __DIR__ . '/../publish/.bin.php',
                    'destination' => BASE_PATH . '/.bin.php',
                ],
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