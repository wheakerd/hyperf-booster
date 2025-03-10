<?php
declare(strict_types=1);
namespace Wheakerd\HyperfBooster\Hyperf\Watcher;

use Exception;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\Di\MetadataCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use Hyperf\Support\Composer;
use Hyperf\Support\Filesystem\FileNotFoundException;
use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\Watcher\Ast\Metadata;
use Hyperf\Watcher\Ast\RewriteClassNameVisitor;
use PhpParser\NodeTraverser;
use Wheakerd\HyperfBooster\Hyperf\Di\ScanConfig;
use Wheakerd\HyperfBooster\Hyperf\Di\Scanner;

final class Process
{
    protected AnnotationReader $reader;

    protected ScanConfig $config;

    protected Filesystem $filesystem;

    protected Ast $ast;

    protected string $path = BASE_PATH . '/runtime/container/scan.cache';

    /**
     * @param string $file
     * @throws Exception
     */
    public function __construct(protected string $file)
    {
        $this->ast = new Ast();
        $this->config = $this->initScanConfig();
        $this->reader = new AnnotationReader($this->config->getIgnoreAnnotations());
        $this->filesystem = new Filesystem();
    }

    /**
     * @return void
     * @throws FileNotFoundException
     * @throws NotFoundException
     */
    public function __invoke(): void
    {
        $meta = $this->getMetadata($this->file);
        if ($meta === null) {
            return;
        }
        $class = $meta->toClassName();
        $collectors = $this->config->getCollectors();
        [$data, $proxies, $aspectClasses] = file_exists($this->path) ? unserialize(file_get_contents($this->path)) : [[], [], []];
        foreach ($data as $collector => $deserialized) {
            /** @var MetadataCollector $collector */
            if (in_array($collector, $collectors)) {
                $collector::deserialize($deserialized);
            }
        }

        if (! empty($this->file)) {
            require $this->file;
        }

        // Collect the annotations.
        $ref = ReflectionManager::reflectClass($class);
        foreach ($collectors as $collector) {
            $collector::clear($class);
        }

        $scanner = new Scanner($this->config, new NullScanHandler());
        $scanner->collect($this->reader, $ref);

        $collectors = $this->config->getCollectors();
        $data = [];
        /** @var MetadataCollector|string $collector */
        foreach ($collectors as $collector) {
            $data[$collector] = $collector::serialize();
        }

        $composerLoader = Composer::getLoader();
        $composerLoader->addClassMap($this->config->getClassMap());
        $this->deleteAspectClasses($aspectClasses, $proxies, $class);

        // Reload the proxy class.
        $manager = new ProxyManager(array_merge($composerLoader->getClassMap(), $proxies, [$class => $this->file]), BASE_PATH . '/runtime/container/proxy/');
        $proxies = $manager->getProxies();
        $aspectClasses = $manager->getAspectClasses();

        $this->putCache($this->path, serialize([$data, $proxies, $aspectClasses]));
    }

    protected function putCache($path, $data): void
    {
        if (! $this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0755, true);
        }

        $this->filesystem->put($path, $data);
    }

    /**
     * @param string $file
     * @return Metadata|null
     * @throws FileNotFoundException
     */
    protected function getMetadata(string $file): ?Metadata
    {
        $stmts = $this->ast->parse($this->filesystem->get($file));
        $meta = new Metadata();
        $meta->path = $file;
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new RewriteClassNameVisitor($meta));
        $traverser->traverse($stmts);
        if (! $meta->isClass()) {
            return null;
        }
        return $meta;
    }

    /**
     * @return ScanConfig
     * @throws Exception
     */
    protected function initScanConfig(): ScanConfig
    {
        $configProvider = Composer::getJsonContent()['extra']['config'] ?? null;

        if (null === $configProvider) {
            throw new Exception('No config provider was found in the `composer.json`.');
        }

        return ScanConfig::instance($configProvider);
    }

    protected function deleteAspectClasses($aspectClasses, $proxies, $class): void
    {
        foreach ($aspectClasses as $aspect => $classes) {
            if ($aspect !== $class) {
                continue;
            }
            foreach ($classes as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        $classesAspects = AspectCollector::get('classes', []);
        foreach ($classesAspects as $aspect => $rules) {
            if ($aspect !== $class) {
                continue;
            }
            foreach ($rules as $rule) {
                if (isset($proxies[$rule]) && file_exists($proxies[$rule])) {
                    unlink($proxies[$rule]);
                }
            }
        }
    }
}
