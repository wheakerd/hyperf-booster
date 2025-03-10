<?php
declare(strict_types=1);

namespace Wheakerd\HyperfBooster\Hyperf\Watcher;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\Watcher\Watcher as HyperfWatcher;
use function Hyperf\Watcher\exec;

/**
 * @Watcher
 * @\Wheakerd\HyperfBooster\Watcher\Watcher
 */
final class Watcher extends HyperfWatcher
{
    public function run()
    {
        $this->dumpAutoload();
        $this->restart();

        $channel = new Channel(999);
        Coroutine::create(function () use ($channel) {
            $this->driver->watch($channel);
        });

        $result = [];
        while (true) {
            $file = $channel->pop(0.001);
            if ($file === false) {
                if (count($result) > 0) {
                    $result = [];
                    $this->restart(false);
                }
            } else {
                $ret = exec(sprintf('%s %s/vendor/wheakerd/hyperf-booster/collector-reload.php %s', $this->option->getBin(), BASE_PATH, $file));
                if ($ret['code'] === 0) {
                    $this->output->writeln('Class reload success.');
                } else {
                    $this->output->writeln('Class reload failed.');
                    $this->output->writeln($ret['output'] ?? '');
                }
                $result[] = $file;
            }
        }
    }
}