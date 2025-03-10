<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster\Hyperf\Di;

use Hyperf\Di\ClassLoader as HyperfClassLoader;
use Hyperf\Di\Exception\DirectoryNotExistException;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\Di\ScanHandler\PcntlScanHandler;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Support\Composer;
use Hyperf\Support\DotenvManager;
use Hyperf\Support\Filesystem\FileNotFoundException;

/**
 * @see HyperfClassLoader
 * @ClassLoader
 * @\Wheakerd\HyperfBooster\Di\ClassLoader
 */
final class ClassLoader extends HyperfClassLoader
{
    /**
     * @param string|null $proxyFileDirPath
     * @param string|null $configDir
     * @param ScanHandlerInterface|null $handler
     * @return void
     * @throws DirectoryNotExistException
     * @throws NotFoundException
     * @throws FileNotFoundException
     */
    public static function init(?string $proxyFileDirPath = null, ?string $configDir = null, ?ScanHandlerInterface $handler = null): void
    {
        if (!$proxyFileDirPath) {
            // This dir is the default proxy file dir path of Hyperf
            $proxyFileDirPath = BASE_PATH . '/runtime/container/proxy/';
        }

        $configProvider = Composer::getJsonContent()['extra']['config'] ?? null;

        if (!is_null($configProvider)) {
            if (!$handler) {
                $handler = new PcntlScanHandler();
            }

            $composerLoader = Composer::getLoader();

            if (file_exists(BASE_PATH . '/.env')) {
                DotenvManager::load([BASE_PATH]);
            }

            // Scan by ScanConfig to generate the reflection class map
            $scanConfig = ScanConfig::instance($configProvider);
            $composerLoader->addClassMap(
                $scanConfig->getClassMap()
            );

            $scanner = new Scanner($scanConfig, $handler);
            $composerLoader->addClassMap(
                $scanner->scan($composerLoader->getClassMap(), $proxyFileDirPath)
            );

            goto end;
        }

        HyperfClassLoader::init(... func_get_args());
        end:
    }
}