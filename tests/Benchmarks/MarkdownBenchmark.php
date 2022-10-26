<?php
declare(strict_types=1);

namespace Tests\Benchmarks;

use Hyde\Framework\Models\Markdown\Markdown;

class MarkdownBenchmark extends BenchCase
{
    /**
     * Results history:
     * - #f4d8d452b: 0.42493788ms
     * - #fd90ee987: 0.09853745ms.
     */
    public function testMarkdownParserFacadeShort()
    {
        $this->benchmark(function () {
            return Markdown::render('Hello World!');
        }, 1500);
    }

    /**
     * Results history:
     * - #f4d8d452b 2.62538815ms
     * - #fd90ee987: 2.07124043ms.
     */
    public function testMarkdownParserFacadeFull()
    {
        $markdown = file_get_contents(__DIR__.'/../fixtures/markdown.md');
        $this->benchmark(function () use ($markdown) {
            return Markdown::render($markdown);
        }, 500);
    }
}
