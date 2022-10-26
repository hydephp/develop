<?php

declare(strict_types=1);

namespace Tests\Benchmarks;

use Hyde\Testing\TestCase;
use Illuminate\Support\Str;
use Tests\Benchmarks\CBench\Benchmark;
use Tests\Benchmarks\CBench\Report;

/**
 * Run with vendor/bin/pest tests/Benchmarks --test-suffix="Benchmark.php".
 */
class BenchCase extends TestCase
{
    public function benchmark(callable $callback, int $iterations = 100, ?string $name = null): Benchmark
    {
        $class = (basename(static::class, 'Test'));
        $method = substr(debug_backtrace()[1]['function'], 4);

        if (extension_loaded('xdebug')) {
            echo "Warning: xdebug is enabled, this will affect the performance of the benchmark.\n\n";
        }

        $benchmark = Benchmark::run($callback, $iterations, $name ?? $class.'::'.$method, env('SILENCE_BENCHMARKS', true));

        file_put_contents(sprintf(
            '%s/reports/%s.json', __DIR__, (
                Str::snake($class.'-'.lcfirst($method)))),
            json_encode(new Report($benchmark), JSON_PRETTY_PRINT));

        $this->log($method, "Ran $benchmark->iterations iterations in {$benchmark->getExecutionTimeInMs()}ms ({$benchmark->getAverageExecutionTimeInMs()}ms avg / {$benchmark->getAverageIterationsPerSecond()} per sec)");

        $this->assertTrue(true);

        $benchmark->runName = $name ?? $class.'::'.$method;

        return $benchmark;
    }

    protected function log(string $method, string $message): void
    {
        echo "\033[33m$method: \033[32m$message\033[0m\n";
    }
}
