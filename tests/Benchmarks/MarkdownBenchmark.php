<?php

namespace Tests\Benchmarks;

use Hyde\Framework\Facades\Markdown;

class MarkdownBenchmark extends BenchCase
{
    /**
     * Results history:
     * #f4d8d452b 'avg_iteration_time': '0.42493788ms',.
     */
    public function testMarkdownParserFacadeShort()
    {
        $this->benchmark(function () {
            return Markdown::render('Hello World!');
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
            return Markdown::render($markdown);
        }, 500);
    }
}
