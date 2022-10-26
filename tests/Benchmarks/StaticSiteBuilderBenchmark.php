<?php

declare(strict_types=1);

namespace Tests\Benchmarks;

use Hyde\Framework\Hyde;
use Tests\Benchmarks\CBench\Benchmark;

class StaticSiteBuilderBenchmark extends BenchCase
{
    /**
     * Results history:
     * - #49834e374: 76.05051994ms
     * - #1cf136fc0: 85.29273033ms
     * - #fd90ee987: 49.32703972ms.
     */
    public function testParseBladePageFile()
    {
        $this->mockConsoleOutput = false;
        $this->artisan('publish:homepage posts -n');
        $this->artisan('make:page Test -n');
        $this->artisan('make:post -n');
        $this->artisan('make:page --docs Test -n');

        $result = $this->benchmark(function () {
            return $this->artisan('build');
        }, 100);

        $this->report($result);

        $this->artisan('publish:homepage welcome -n');
        Hyde::unlink('_pages/test.md');
        Hyde::unlink('_posts/my-new-post.md');
        Hyde::unlink('_docs/test.md');
    }

    protected function report(Benchmark $benchmark): void
    {
        echo "- <b>$benchmark->runName</b> - avg_iteration_time\n";
        echo '- #'.trim(shell_exec('git rev-parse --short HEAD')).
            ": {$benchmark->getAverageExecutionTimeInMs()}ms\n";
    }
}
