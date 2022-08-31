<?php

namespace Hyde\Framework\Concerns;

use LaravelZero\Framework\Commands\Command;

/**
 * Base class for commands that run a simple action.
 *
 * @deprecated v0.63.0-beta as it is no longer used.
 */
abstract class ActionCommand extends Command
{
    protected function action(string $title, \Closure $task, string $resultMessage = 'Finished')
    {
        /** @var float $actionTime */
        $actionTime = microtime(true);

        $this->comment("$title...");

        $result = $task();

        $this->line(" > $resultMessage in ".$this->getExecutionTimeInMs($actionTime).'ms');

        return $result;
    }

    protected function getExecutionTimeInMs(float $timeStart): string
    {
        return number_format(((microtime(true) - $timeStart) * 1000), 2);
    }
}
