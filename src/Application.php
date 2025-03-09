<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Engine\DefaultOption;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

        ClassLoader::init();
    }

    /**
     * @return void
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(): void
    {
        ApplicationContext::setContainer(
            new Container(
                (new DefinitionSourceFactory)()
            )
        )->get(ApplicationInterface::class)->run();
    }
}