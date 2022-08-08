<?php

namespace Tests\Benchmarks;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Tests\Benchmarks\CBench\Benchmark;

class PageParserBenchmark extends BenchCase
{
    /**
     * Results history:
     * - #5d044679b: 0.30337441ms
     */
    public function testParseMarkdownPostFile()
    {
        $this->mockConsoleOutput = false;
        $this->artisan('make:post -n');

        $result = $this->benchmark(function () {
            return MarkdownPost::parse('my-new-post');
        }, 10000);

        $this->report($result);

        Hyde::unlink('_posts/my-new-post.md');
    }

    protected function report(Benchmark $benchmark): void
    {
        echo "- <b>$benchmark->runName</b> - avg_iteration_time\n";
        echo "- #".trim(shell_exec('git rev-parse --short HEAD')).
            ": {$benchmark->getAverageExecutionTimeInMs()}ms\n";
    }
}
