<?php

namespace Tests\Benchmarks;

use Hyde\Framework\Hyde;

class SearchIndexBenchmark extends BenchCase
{
    /**
     * Results history:
     * #5555b676b 'avg_iteration_time': '26.75ms'
     * #2d73931ad 'avg_iteration_time': '19.41ms'.
     */
    public function testBuildSearchIndexCommandNoFiles()
    {
        $this->benchmark(function () {
            $this->artisan('build:search');
        }, 50);

        unlink(Hyde::path('_site/docs/search.json'));
        unlink(Hyde::path('_site/docs/search.html'));
    }

    /**
     * Results history:
     * #5555b676b 'avg_iteration_time': '120.45ms'
     * #2d73931ad 'avg_iteration_time': '105.91ms'.
     */
    public function testBuildSearchIndexCommandWithFiles()
    {
        $this->mockRoute();

        copy(Hyde::path('tests/fixtures/_posts/typography-simple.md'), Hyde::path('_docs/typography-simple.md'));
        copy(Hyde::path('tests/fixtures/_posts/typography-front-matter.md'), Hyde::path('_docs/typography-front-matter.md'));
        copy(Hyde::path('tests/fixtures/markdown-features.md'), Hyde::path('_docs/markdown-features.md'));
        copy(Hyde::path('tests/fixtures/markdown.md'), Hyde::path('_docs/markdown.md'));
        touch(Hyde::path('_docs/blank.md'));

        $this->benchmark(function () {
            $this->artisan('build:search');
        }, 10);

        $this->resetDocs();
        unlink(Hyde::path('_site/docs/search.json'));
        unlink(Hyde::path('_site/docs/search.html'));
    }
}
