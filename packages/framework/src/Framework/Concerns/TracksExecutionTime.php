<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use function microtime;
use function number_format;

trait TracksExecutionTime
{
    protected float $timeStart;

    protected function startClock(): void
    {
        $this->timeStart = microtime(true);
    }

    protected function stopClock(): float
    {
        return microtime(true) - $this->timeStart;
    }

    protected function getExecutionTimeInMs(): int|float
    {
        return $this->stopClock() * 1000;
    }

    protected function getExecutionTimeString(): string
    {
        return number_format($this->getExecutionTimeInMs(), 2).'ms';
    }
}
