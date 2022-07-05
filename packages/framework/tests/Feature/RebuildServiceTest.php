<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RebuildService;
use Hyde\Framework\StaticPageBuilder;
use Hyde\Testing\TestCase;

/**
 * Note that we don't fully test the created files since the service is
 * just a proxy for the actual builders, which have their own tests.
 *
 * @covers \Hyde\Framework\Services\RebuildService
 */
class RebuildServiceTest extends TestCase
{
    public function test_execute_methods()
    {
        $this->runExecuteTest('_posts');
        $this->runExecuteTest('_pages');
        $this->runExecuteTest('_docs');
        $this->runExecuteTest('_pages', '.blade.php');

        unlink(Hyde::path('_site/foo.html'));
        unlink(Hyde::path('_site/docs/foo.html'));
        unlink(Hyde::path('_site/posts/foo.html'));
    }

    protected function runExecuteTest(string $prefix, string $suffix = '.md')
    {
        $path = $prefix.'/foo'.$suffix;
        Hyde::touch(($path));
        $service = new RebuildService($path);
        $result = $service->execute();
        $this->assertInstanceOf(StaticPageBuilder::class, $result);
        unlink(Hyde::path($path));
    }
}
