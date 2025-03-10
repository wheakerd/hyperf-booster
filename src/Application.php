<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Di\Container;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Engine\DefaultOption;
use Phar;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Wheakerd\HyperfBooster\Hyperf\Di\ClassLoader;

/**
 * @Application
 * @\Wheakerd\HyperfBooster\Application
 */
final class Application
{
    public function __construct()
    {
        /**
         * @document Support for `PHP` compilation
         */
        !defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_VERSION_ID >= 60000
            ? DefaultOption::hookFlags()
            : SWOOLE_HOOK_TCP
            | SWOOLE_HOOK_UNIX
            | SWOOLE_HOOK_UDP
            | SWOOLE_HOOK_UDG
            | SWOOLE_HOOK_SSL
            | SWOOLE_HOOK_TLS
            | SWOOLE_HOOK_SLEEP
            | SWOOLE_HOOK_STREAM_FUNCTION
            | SWOOLE_HOOK_BLOCKING_FUNCTION
            | SWOOLE_HOOK_PROC
            | SWOOLE_HOOK_NATIVE_CURL
            | SWOOLE_HOOK_SOCKETS
            | SWOOLE_HOOK_STDIO
        );
    }

    /**
     * @param string|null $proxyFileDirPath
     * @param string|null $configDir
     * @param ScanHandlerInterface|null $handler
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function run(?string $proxyFileDirPath = null, ?string $configDir = null, ?ScanHandlerInterface $handler = null): void
    {
        ClassLoader::init(... func_get_args());

        ApplicationContext::setContainer(
            new Container(
                (new DefinitionSourceFactory)()
            )
        )->get(ApplicationInterface::class)->run();
    }
}