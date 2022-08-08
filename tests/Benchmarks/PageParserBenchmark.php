<?php

namespace Tests\Benchmarks;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;

class PageParserBenchmark extends BenchCase
{
    public function testParseMarkdownPostFile()
    {
        $this->mockConsoleOutput = false;
        $this->artisan('make:post -n');

        $this->benchmark(function () {
            return MarkdownPost::parse('my-new-post');
        });

        Hyde::unlink('_posts/my-new-post.md');
    }
}
