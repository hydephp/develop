<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Pages\Concerns\HydePage::all()
 */
class PageModelGetHelperTest extends UnitTestCase
{
    protected static bool $needsConfig = true;

    /** @var \Illuminate\Filesystem\Filesystem&\Mockery\MockInterface */
    protected $filesystem;

    protected function setUp(): void
    {
        self::setupKernel();

        $this->filesystem = $this->mockFilesystemStrict()
            ->shouldReceive('glob')->once()->with(Hyde::path('_pages/{*,**/*}.html'), GLOB_BRACE)->andReturn([])->byDefault()
            ->shouldReceive('glob')->once()->with(Hyde::path('_pages/{*,**/*}.blade.php'), GLOB_BRACE)->andReturn([])->byDefault()
            ->shouldReceive('glob')->once()->with(Hyde::path('_pages/{*,**/*}.md'), GLOB_BRACE)->andReturn([])->byDefault()
            ->shouldReceive('glob')->once()->with(Hyde::path('_posts/{*,**/*}.md'), GLOB_BRACE)->andReturn([])->byDefault()
            ->shouldReceive('glob')->once()->with(Hyde::path('_docs/{*,**/*}.md'), GLOB_BRACE)->andReturn([])->byDefault();
    }

    public function testBladePageGetHelperReturnsBladePageCollection()
    {
        $this->filesystem->shouldReceive('glob')->once()->with(Hyde::path('_pages/{*,**/*}.blade.php'), GLOB_BRACE)->andReturn(['_pages/test-page.blade.php']);
        $this->filesystem->shouldReceive('missing')->once()->with(Hyde::path('_pages/test-page.blade.php'))->andReturnFalse();
        $this->filesystem->shouldReceive('get')->once()->with(Hyde::path('_pages/test-page.blade.php'))->andReturn('content');

        $collection = BladePage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(BladePage::class, $collection);
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageCollection()
    {
        $this->filesystem->shouldReceive('glob')->once()->with(Hyde::path('_pages/{*,**/*}.md'), GLOB_BRACE)->andReturn(['_pages/test-page.md']);
        $this->filesystem->shouldReceive('missing')->once()->with(Hyde::path('_pages/test-page.md'))->andReturnFalse();
        $this->filesystem->shouldReceive('get')->once()->with(Hyde::path('_pages/test-page.md'))->andReturn('content');

        $collection = MarkdownPage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPage::class, $collection);
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostCollection()
    {
        $this->filesystem->shouldReceive('glob')->once()->with(Hyde::path('_posts/{*,**/*}.md'), GLOB_BRACE)->andReturn(['_posts/test-post.md']);
        $this->filesystem->shouldReceive('missing')->once()->with(Hyde::path('_posts/test-post.md'))->andReturnFalse();
        $this->filesystem->shouldReceive('get')->once()->with(Hyde::path('_posts/test-post.md'))->andReturn('content');

        $collection = MarkdownPost::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, $collection);
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageCollection()
    {
        $this->filesystem->shouldReceive('glob')->once()->with(Hyde::path('_docs/{*,**/*}.md'), GLOB_BRACE)->andReturn(['_docs/test-page.md']);
        $this->filesystem->shouldReceive('missing')->once()->with(Hyde::path('_docs/test-page.md'))->andReturnFalse();
        $this->filesystem->shouldReceive('get')->once()->with(Hyde::path('_docs/test-page.md'))->andReturn('content');

        $collection = DocumentationPage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(DocumentationPage::class, $collection);
    }
}
