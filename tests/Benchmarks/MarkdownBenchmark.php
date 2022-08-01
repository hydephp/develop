<?php

namespace Tests\Benchmarks;

use Hyde\Framework\Actions\MarkdownConverter;

class MarkdownBenchmark extends BenchCase
{
    public function testMarkdownParserFacadeShort()
    {
        $this->benchmark(function () {
            return MarkdownConverter::parse('Hello World!');
        }, 1500);
    }

    public function testMarkdownParserFacadeFull()
    {
        $markdown = file_get_contents(__DIR__ . '/../fixtures/markdown.md');
        $this->benchmark(function () use ($markdown) {
            return MarkdownConverter::parse($markdown);
        }, 500);
    }
}
