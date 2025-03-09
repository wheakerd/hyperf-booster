<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace App;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\ServerInterface;
use Psr\Log\LogLevel;
use Swoole\Constant;
use function Hyperf\Support\env;

/**
 * @ConfigProvider
 * @\App\HyperfBooster\ConfigProvider
 */
final class ConfigProvider
{
    public function config(): array
    {
        return [
            'app_name'       => env('APP_NAME', 'skeleton'),
            'app_env'        => env('APP_ENV', 'dev'),
            'scan_cacheable' => env('SCAN_CACHEABLE', false),
            StdoutLoggerInterface::class => [
                'log_level' => match (env('APP_ENV', 'dev')) {
                    'dev' => [
                        LogLevel::ALERT,
                        LogLevel::CRITICAL,
                        LogLevel::DEBUG,
                        LogLevel::EMERGENCY,
                        LogLevel::ERROR,
                        LogLevel::INFO,
                        LogLevel::NOTICE,
                        LogLevel::WARNING,
                    ],
                    default => [
                        LogLevel::ALERT,
                        LogLevel::CRITICAL,
                        LogLevel::EMERGENCY,
                        LogLevel::ERROR,
                        LogLevel::NOTICE,
                        LogLevel::WARNING,
                    ],
                },
            ],
        ];
    }

    public function annotations(): array
    {
        return [
            'scan' => [
                'paths' => [
                    BASE_PATH . '/app',
                    BASE_PATH . '/extend',
                ],
                'ignore_annotations' => [
                    'mixin',
                ],
            ]
        ];
    }

    public function aspects(): array
    {
        return [];
    }

    public function cache(): array
    {
        return [];
    }

    public function commands(): array
    {
        return [];
    }

    public function databases(): array
    {
        return [];
    }

    public function devtool(): array
    {
        return [
            'generator' => [
                'amqp' => [
                    'consumer' => [
                        'namespace' => 'App\\Amqp\\Consumer',
                    ],
                    'producer' => [
                        'namespace' => 'App\\Amqp\\Producer',
                    ],
                ],
                'aspect' => [
                    'namespace' => 'App\\Aspect',
                ],
                'command' => [
                    'namespace' => 'App\\Command',
                ],
                'controller' => [
                    'namespace' => 'App\\Controller',
                ],
                'job' => [
                    'namespace' => 'App\\Job',
                ],
                'listener' => [
                    'namespace' => 'App\\Listener',
                ],
                'middleware' => [
                    'namespace' => 'App\\Middleware',
                ],
                'Process' => [
                    'namespace' => 'App\\Processes',
                ],
            ],
        ];
    }

    public function exceptions(): array
    {
        return [];
    }

    public function listeners(): array
    {
        return [];
    }

    public function logger(): array
    {
        return [];
    }

    public function middlewares(): array
    {
        return [];
    }

    public function processes(): array
    {
        return [];
    }

    public function redis(): array
    {
        return [];
    }

    public function server(): array
    {
        return [
            'mode' => SWOOLE_PROCESS,
            'servers' => [
                [
                    'name' => 'http',
                    'type' => ServerInterface::SERVER_HTTP,
                    'host' => '0.0.0.0',
                    'port' => 9602,
                    'sock_type' => SWOOLE_SOCK_TCP,
                    'callbacks' => [
                    ],
                    'options' => [
                        // Whether to enable request lifecycle event
                        'enable_request_lifecycle' => true,
                    ],
                ],
            ],
            'settings' => [
                Constant::OPTION_DAEMONIZE => false,
                Constant::OPTION_HEARTBEAT_IDLE_TIME => 60,
                Constant::OPTION_HEARTBEAT_CHECK_INTERVAL => 30,
                Constant::OPTION_ENABLE_COROUTINE => true,
                Constant::OPTION_WORKER_NUM => swoole_cpu_num(),
                Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/hyperf.pid',
                Constant::OPTION_OPEN_TCP_NODELAY => true,
                Constant::OPTION_MAX_COROUTINE => 100000,
                Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
                Constant::OPTION_MAX_REQUEST => 100000,
                Constant::OPTION_SOCKET_BUFFER_SIZE => 1024 * 1024 * 10,
                Constant::OPTION_BUFFER_OUTPUT_SIZE => 1024 * 1024 * 10,
                Constant::OPTION_PACKAGE_MAX_LENGTH => 1024 * 1024 * 10,
            ],
            'callbacks' => [],
        ];
    }

    public function translation(): array
    {
        return [];
    }

    public function __invoke(): array
    {
        $config = [];

        foreach (get_class_methods($this) as $method) {
            //  Filter magic method
            if (str_starts_with($method, '__')) continue;

            $config[$method] = $this->{$method}();
        }

        return $config;
    }
}