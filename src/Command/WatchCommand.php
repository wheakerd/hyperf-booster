<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster\Command;

use Exception;
use Hyperf\Command\Command;
use Hyperf\Command\Concerns\NullDisableEventDispatcher;
use Hyperf\Watcher\Option;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;
use Wheakerd\HyperfBooster\Hyperf\Watcher\Watcher;
use function Hyperf\Support\make;

/**
 * @WatchCommand
 * @\Wheakerd\HyperfBooster\Command\WatchCommand
 */
final class WatchCommand extends Command
{
    use NullDisableEventDispatcher;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('wheakerd:server-watch');
        $this->setDescription('watch command');
        $this->addOption('config', 'C', InputOption::VALUE_OPTIONAL, '', '.watcher.php');
        $this->addOption('file', 'F', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', []);
        $this->addOption('dir', 'D', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', []);
        $this->addOption('no-restart', 'N', InputOption::VALUE_NONE, 'Whether no need to restart server');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $configFile = $this->input->getOption('config');

        if (!file_exists($configFile)) {
            throw new Exception('.watcher.php file not found.');
        }

        $options = (array)include $configFile;

        $option = make(Option::class, [
            'options' => $options,
            'dir' => $this->input->getOption('dir'),
            'file' => $this->input->getOption('file'),
            'restart' => !$this->input->getOption('no-restart'),
        ]);

        $watcher = make(Watcher::class, [
            'option' => $option,
            'output' => $this->output,
        ]);

        $watcher->run();
    }
}
