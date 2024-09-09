<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;
use Mockery\ExpectationInterface;
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

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();
    }

    public function testBladePageGetHelperReturnsBladePageCollection()
    {
        $this->shouldReceiveGlob('_pages/{*,**/*}.blade.php')->andReturn(['_pages/test-page.blade.php']);
        $this->shouldFindFile('_pages/test-page.blade.php');

        $collection = BladePage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(BladePage::class, $collection);
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageCollection()
    {
        $this->shouldReceiveGlob('_pages/{*,**/*}.md')->andReturn(['_pages/test-page.md']);
        $this->shouldFindFile('_pages/test-page.md');

        $collection = MarkdownPage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPage::class, $collection);
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostCollection()
    {
        $this->shouldReceiveGlob('_posts/{*,**/*}.md')->andReturn(['_posts/test-post.md']);
        $this->shouldFindFile('_posts/test-post.md');

        $collection = MarkdownPost::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, $collection);
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageCollection()
    {
        $this->shouldReceiveGlob('_docs/{*,**/*}.md')->andReturn(['_docs/test-page.md']);
        $this->shouldFindFile('_docs/test-page.md');

        $collection = DocumentationPage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(DocumentationPage::class, $collection);
    }

    protected function shouldReceiveGlob(string $withPath): ExpectationInterface
    {
        return $this->filesystem->shouldReceive('glob')->once()->with(Hyde::path($withPath), GLOB_BRACE);
    }

    protected function shouldFindFile(string $file): void
    {
        $this->filesystem->shouldReceive('missing')->once()->with(Hyde::path($file))->andReturnFalse();
        $this->filesystem->shouldReceive('get')->once()->with(Hyde::path($file))->andReturn('content');
    }
}
