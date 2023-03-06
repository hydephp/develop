<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
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
        (new StaticPageBuilder(Pages::getPage('_pages/foo.blade.php')))->__invoke();

        $this->assertFileExists('_site/foo.html');
        unlink(Hyde::path('_site/foo.html'));
    }

    public function test_can_rebuild_markdown_page()
    {
        $this->file('_pages/foo.md');
        (new StaticPageBuilder(Pages::getPage('_pages/foo.md')))->__invoke();

        $this->assertFileExists('_site/foo.html');
        unlink(Hyde::path('_site/foo.html'));
    }

    public function test_can_rebuild_markdown_post()
    {
        $this->file('_posts/foo.md');
        (new StaticPageBuilder(Pages::getPage('_posts/foo.md')))->__invoke();

        $this->assertFileExists('_site/posts/foo.html');
        unlink(Hyde::path('_site/posts/foo.html'));
    }

    public function test_can_rebuild_documentation_page()
    {
        $this->file('_pages/foo.md');
        (new StaticPageBuilder(Pages::getPage('_pages/foo.md')))->__invoke();

        $this->assertFileExists('_site/foo.html');
        unlink(Hyde::path('_site/foo.html'));
    }
}
