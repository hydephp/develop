<?php

namespace Tests\Benchmarks;

use Hyde\Framework\Actions\MarkdownConverter;

class MarkdownBenchmark extends BenchCase
{
    /**
     * Results history:
     * #f4d8d452b 'avg_iteration_time': '0.42493788ms',.
     */
    public function testMarkdownParserFacadeShort()
    {
        $this->benchmark(function () {
            return MarkdownConverter::parse('Hello World!');
        }, 1500);
    }

    /**
     * Results history:
     * #f4d8d452b 'avg_iteration_time': '2.62538815ms',.
     */
    public function testMarkdownParserFacadeFull()
    {
        $markdown = file_get_contents(__DIR__.'/../fixtures/markdown.md');
        $this->benchmark(function () use ($markdown) {
            return MarkdownConverter::parse($markdown);
        }, 500);
    }
}
