<?php

namespace Tests\Benchmarks\CBench;

trait TrackingHelpers
{
    protected function getExecutionTimeInMs(int $precision = 2): float
    {
        return round(($this->time_end - $this->time_start) * 1000, $precision);
    }

    protected function getAverageExecutionTimeInMs(int $precision = 8): float
    {
        return round($this->getExecutionTimeInMs(32) / $this->iterations, $precision);
    }

    protected function getAverageIterationsPerSecond(): float
    {
        return round($this->iterations / $this->getExecutionTimeInMs(32), 2);
    }

    protected function getMemoryUsage(): string
    {
        $memory = memory_get_usage(true);

        if ($memory < 1024) {
            return $memory . 'B';
        }

        if ($memory < 1048576) {
            return round($memory / 1024, 2) . 'KB';
        }

        return round($memory / 1048576, 2) . 'MB';
    }
}
