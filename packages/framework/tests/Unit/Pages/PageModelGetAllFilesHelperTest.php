<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Testing\UnitTestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Mockery\ExpectationInterface;

/**
 * @see \Hyde\Pages\Concerns\HydePage::files()
 */
class PageModelGetAllFilesHelperTest extends UnitTestCase
{
    protected static bool $needsConfig = true;

    /** @var \Illuminate\Filesystem\Filesystem&\Mockery\MockInterface */
    protected $filesystem;

    protected function setUp(): void
    {
        self::setupKernel();

        $this->filesystem = $this->mockFilesystemStrict()
            ->shouldReceive('missing')->withAnyArgs()->andReturn(false)->byDefault()
            ->shouldReceive('get')->withAnyArgs()->andReturn('foo')->byDefault()
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

    public function testBladePageGetHelperReturnsBladePageArray()
    {
        $this->shouldReceiveGlob('_pages/{*,**/*}.blade.php')->andReturn(['_pages/test-page.blade.php']);

        $array = BladePage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageArray()
    {
        $this->shouldReceiveGlob('_pages/{*,**/*}.md')->andReturn(['_pages/test-page.md']);

        $array = MarkdownPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostArray()
    {
        $this->shouldReceiveGlob('_posts/{*,**/*}.md')->andReturn(['_posts/test-post.md']);

        $array = MarkdownPost::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-post'], $array);
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageArray()
    {
        $this->shouldReceiveGlob('_docs/{*,**/*}.md')->andReturn(['_docs/test-page.md']);

        $array = DocumentationPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);
    }

    protected function shouldReceiveGlob(string $withPath): ExpectationInterface
    {
        return $this->filesystem->shouldReceive('glob')->once()->with(Hyde::path($withPath), GLOB_BRACE);
    }
}
