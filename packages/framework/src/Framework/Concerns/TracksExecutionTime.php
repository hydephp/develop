<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use function microtime;

trait TracksExecutionTime
{
    protected float $timeStart;

    protected function startClock(): void
    {
        $this->timeStart = microtime(true);
    }

    protected function getExecutionTimeInMs(): int|float
    {
        return (microtime(true) - $this->timeStart) * 1000;
    }
}
