<?php

declare(strict_types=1);

namespace Tests\Benchmarks;

use Hyde\Hyde;

class SearchIndexBenchmark extends BenchCase
{
    /**
     * Results history:
     * - #5555b676b: 26.75ms (with xdebug)
     * - #2d73931ad: 19.41ms (with xdebug)
     * - #fd90ee987: 10.0887394ms.
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
     * - #5555b676b: 120.45ms (with xdebug)
     * - #2d73931ad: 105.91ms (with xdebug)
     * - #fd90ee987: 36.29438877ms.
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
