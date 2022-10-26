<?php

declare(strict_types=1);

namespace Tests\Benchmarks;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Tests\Benchmarks\CBench\Benchmark;

class PageParserBenchmark extends BenchCase
{
    /**
     * Results history:
     * - #14c34beb1: 0.17022841ms.
     * - #0db04eaef: 0.2702086ms.
     * - #fd90ee987: 0.28220031ms.
     */
    public function testParseBladePageFile()
    {
        $this->mockConsoleOutput = false;
        $this->artisan('publish:homepage posts -n');

        $result = $this->benchmark(function () {
            return BladePage::parse('index');
        }, 10000);

        $this->report($result);

        $this->artisan('publish:homepage welcome -n');
    }

    /**
     * Results history:
     * - #8e165366a: 0.1986541ms.
     * - #0db04eaef: 0.29952221ms.
     * - #fd90ee987: 0.30080938ms.
     */
    public function testParseMarkdownPageFile()
    {
        $this->mockConsoleOutput = false;
        $this->artisan('make:page Test -n');

        $result = $this->benchmark(function () {
            return MarkdownPage::parse('test');
        }, 10000);

        $this->report($result);

        Hyde::unlink('_pages/test.md');
    }

    /**
     * Results history:
     * - #5d044679b: 0.30337441ms.
     * - #0db04eaef: 0.39583502ms.
     * - #fd90ee987: 0.41671791ms.
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

    /**
     * Results history:
     * - #6f63f5016: 0.16660199ms.
     * - #0db04eaef: 0.2729635ms.
     * - #fd90ee987: 0.27649839ms.
     */
    public function testParseDocumentationPageFile()
    {
        $this->mockConsoleOutput = false;
        $this->artisan('make:page --docs Test -n');

        $result = $this->benchmark(function () {
            return DocumentationPage::parse('test');
        }, 10000);

        $this->report($result);

        Hyde::unlink('_docs/test.md');
    }

    protected function report(Benchmark $benchmark): void
    {
        echo "- <b>$benchmark->runName</b> - avg_iteration_time\n";
        echo '- #'.trim(shell_exec('git rev-parse --short HEAD')).
            ": {$benchmark->getAverageExecutionTimeInMs()}ms\n";
    }
}
