<?php

namespace Tests\Benchmarks\CBench;

/**
 * Based on CBench, ported to PSR-4.
 *
 * @link https://github.com/caendesilva/CBench
 * @license MIT
 */
class Benchmark
{
    use ConsoleHelpers;
    use TrackingHelpers;

    public const VERSION = 'dev-master';

    protected int $iterations;
    protected float $time_start;
    protected float $time_end;
    protected ?string $name;

    public function __construct(int $iterations, ?string $name = null)
    {
        $this->iterations = $iterations;
        $this->name = $name;

        $this->init();
    }

    public function __destruct()
    {
        $this->disengage();
    }

    public static function run(callable $callback, int $iterations = 100, ?string $name = null): Benchmark
    {
        $benchmark = new Benchmark($iterations, $name);
        $benchmark->execute($callback);
        return $benchmark;
    }

    protected function init(): void
    {
        $this->comment(str_repeat('=', 40))
            ->line('Preparing Benchmark script')
            ->comment(str_repeat('-', 40))
            ->line('Script version:    ' . self::VERSION)
            ->line('Current time:      ' . date('Y-m-d H:i:s'))
            ->line()
            ->line('Iterations to run: ' . $this->iterations)
            ->line('Name of benchmark: ' . ($this->name ?? '[not set]'))
            ->comment(str_repeat('=', 40))
            ->line();
    }

    protected function disengage(): void
    {
        $this->line()
            ->comment(str_repeat('=', 40))
            ->line('Benchmark script complete')
            ->comment(str_repeat('-', 40));

        $this->info('Run information:')
            ->line('Script version:    ' . self::VERSION)
            ->line('Today\'s date:      ' . date('Y-m-d'))
            ->line('Name of benchmark: ' . ($this->name ?? '[not set]'))
            ->newline();

        $this->info('Benchmark information:')
            ->line('Total iterations:       ' . $this->iterations)
            ->line('Total execution time:   ' . $this->getExecutionTimeInMs() . 'ms')
            ->line('Avg.  iteration time:   ' . $this->getAverageExecutionTimeInMs() . 'ms')
            ->line('Avg.  iterations/sec:   ' . $this->getAverageIterationsPerSecond())
            ->line('Approx. Memory usage:   ' . $this->getMemoryUsage())
            ->newline();

        $this->info('System information:')
            ->line('PHP version: ' . PHP_VERSION . ' (' . php_sapi_name() . ')')
            ->line('OS/Arch:     ' . PHP_OS . ' (' . PHP_INT_SIZE * 8 . '-bit' . ')')
            ->line('xdebug:      ' . (extension_loaded('xdebug') ? 'enabled ✅' : 'disabled ❌'))
            ->line('opcache:     ' . (extension_loaded('opcache') ? 'enabled ✅' : 'disabled ❌'))
            ->comment(str_repeat('=', 40));
    }

    protected function execute(callable $callback): void
    {
        $this->start();
        for ($i = 0; $i < $this->iterations; $i++) {
            $callback();
        }
        $this->end();
    }

    protected function start(): void
    {
        $this->time_start = microtime(true);

        $this->info('Starting benchmark...')->newline();
    }

    protected function end(): void
    {
        $this->time_end = microtime(true);

        $this->newline(2)->info('Benchmark complete!');
    }
}
