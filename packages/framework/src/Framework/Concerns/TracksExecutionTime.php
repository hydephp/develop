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

    protected function getExecutionTimeString(): string
    {
        return number_format($this->getExecutionTimeInMs(), 2).'ms';
    }

    protected function getExecutionTimeInMs(): int|float
    {
        return (microtime(true) - $this->timeStart) * 1000;
    }
}
