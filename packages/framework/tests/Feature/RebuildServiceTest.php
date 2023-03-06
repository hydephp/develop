<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Services\RebuildService;
use Hyde\Testing\TestCase;

/**
 * Note that we don't fully test the created files since the service is
 * just a proxy for the actual builders, which have their own tests.
 *
 * @covers \Hyde\Framework\Services\RebuildService
 */
class RebuildServiceTest extends TestCase
{
    public function test_can_rebuild_blade_page()
    {
        $this->file('_pages/foo.blade.php');
        $service = new RebuildService('_pages/foo.blade.php');
        $service->__invoke();

        $this->assertFileExists('_site/foo.html');
    }

    public function test_can_rebuild_markdown_page()
    {
        $this->file('_pages/foo.md');
        $service = new RebuildService('_pages/foo.md');
        $service->__invoke();

        $this->assertFileExists('_site/foo.html');
    }

    public function test_can_rebuild_markdown_post()
    {
        $this->file('_posts/foo.md');
        $service = new RebuildService('_posts/foo.md');
        $service->__invoke();

        $this->assertFileExists('_site/posts/foo.html');
    }

    public function test_can_rebuild_documentation_page()
    {
        $this->file('_pages/foo.md');
        $service = new RebuildService('_pages/foo.md');
        $service->__invoke();

        $this->assertFileExists('_site/foo.html');
    }
}
