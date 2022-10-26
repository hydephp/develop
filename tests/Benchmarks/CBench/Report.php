<?php
declare(strict_types=1);

namespace Tests\Benchmarks\CBench;

class Report
{
    protected Benchmark $benchmark;

    public string $benchmark_name;
    public string $human_date;
    public string $timestamp;
    public string $commit_sha;
    public string $script_version;

    public float $total_iterations;
    public float $total_execution_time;
    public float $avg_iteration_time;
    public float $avg_iterations_sec;
    public float $approx_memory_usage;

    public string $php_version;
    public string $php_sapi;
    public string $runner_os;
    public string $runner_arch;
    public bool $xdebug_enabled;
    public bool $opcache_enabled;

    public function __construct(Benchmark $benchmark)
    {
        $this->benchmark = $benchmark;

        $this->generate();
    }

    protected function generate(): void
    {
        $this->human_date = date('Y-m-d');
        $this->benchmark_name = $this->benchmark->name ?? '[not set]';
        $this->timestamp = date('c', $this->benchmark->time_start);
        $this->commit_sha = trim(shell_exec('git rev-parse HEAD'));
        $this->script_version = $this->benchmark::VERSION;

        $this->total_iterations = $this->benchmark->iterations;
        $this->total_execution_time = $this->benchmark->getExecutionTimeInMs();
        $this->avg_iteration_time = $this->benchmark->getAverageExecutionTimeInMs();
        $this->avg_iterations_sec = $this->benchmark->getAverageIterationsPerSecond();
        $this->approx_memory_usage = $this->benchmark->getUnformattedMemoryUsage();

        $this->php_version = PHP_VERSION;
        $this->php_sapi = php_sapi_name();
        $this->runner_os = PHP_OS;
        $this->runner_arch = PHP_INT_SIZE * 8 .'-bit';
        $this->xdebug_enabled = extension_loaded('xdebug');
        $this->opcache_enabled = extension_loaded('opcache');
    }
}
