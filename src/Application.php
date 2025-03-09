<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Hyperf\Di\ScanHandler\PcntlScanHandler;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Engine\DefaultOption;
use Hyperf\Support\Composer;
use Hyperf\Support\DotenvManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Hyperf\Support\make;

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
     * @param ScanHandlerInterface|null $handler
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(?ScanHandlerInterface $handler = null): void
    {
        $this->init(
            $handler ?? new PcntlScanHandler(),
            ApplicationContext::setContainer(
                new Container(
                    (new DefinitionSourceFactory)()
                )
            )->get(ConfigInterface::class),
        );

        call_user_func([make(ApplicationInterface::class), 'run']);
    }

    private function init(ScanHandlerInterface $handler, ConfigInterface $config): void
    {
        $proxyFileDirPath = BASE_PATH . '/runtime/container/proxy/';

        $cacheable = $config->get('scan_cacheable', false);
        $configDir = BASE_PATH . '/config';
        $paths = $config->get('annotations.scan.paths', []);
        $dependencies = $config->get('dependencies', []);
        $ignore_annotations = $config->get('annotations.ignore_annotations', []);
        $global_imports = $config->get('global_imports', []);
        $collectors = $config->get('annotations.scan.collectors', []);
        $class_map = $config->get('annotations.scan.class_map', []);

        $composerLoader = Composer::getLoader();

        if (file_exists(BASE_PATH . '/.env')) {
            DotenvManager::load([BASE_PATH]);
        }

        // Scan by ScanConfig to generate the reflection class map
        $config = new ScanConfig(
            $cacheable,
            $configDir,
            $paths,
            $dependencies,
            $ignore_annotations,
            $global_imports,
            $collectors,
            $class_map,
        );

        $composerLoader->addClassMap($config->getClassMap());

        $scanner = new Scanner($config, $handler);
        $composerLoader->addClassMap(
            $scanner->scan($composerLoader->getClassMap(), $proxyFileDirPath)
        );
    }
}