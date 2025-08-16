<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Illuminate\Support\Facades\File;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

class SourceDirectoriesCanBeChangedTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory('_source');

        parent::tearDown();
    }

    public function testBaselines()
    {
        $this->assertSame('_pages', HtmlPage::sourceDirectory());
        $this->assertSame('_pages', BladePage::sourceDirectory());
        $this->assertSame('_pages', MarkdownPage::sourceDirectory());
        $this->assertSame('_posts', MarkdownPost::sourceDirectory());
        $this->assertSame('_docs', DocumentationPage::sourceDirectory());
    }

    public function testSourceDirectoriesCanBeChangedProgrammatically()
    {
        HtmlPage::setSourceDirectory('.source/pages');
        BladePage::setSourceDirectory('.source/pages');
        MarkdownPage::setSourceDirectory('.source/pages');
        MarkdownPost::setSourceDirectory('.source/posts');
        DocumentationPage::setSourceDirectory('.source/docs');

        $this->assertSame('.source/pages', HtmlPage::sourceDirectory());
        $this->assertSame('.source/pages', BladePage::sourceDirectory());
        $this->assertSame('.source/pages', MarkdownPage::sourceDirectory());
        $this->assertSame('.source/posts', MarkdownPost::sourceDirectory());
        $this->assertSame('.source/docs', DocumentationPage::sourceDirectory());
    }

    public function testSourceDirectoriesCanBeChangedInConfig()
    {
        config(['hyde.source_directories' => [
            HtmlPage::class => '.source/pages',
            BladePage::class => '.source/pages',
            MarkdownPage::class => '.source/pages',
            MarkdownPost::class => '.source/posts',
            DocumentationPage::class => '.source/docs',
        ]]);

        (new HydeServiceProvider($this->app))->register();

        $this->assertSame('.source/pages', HtmlPage::sourceDirectory());
        $this->assertSame('.source/pages', BladePage::sourceDirectory());
        $this->assertSame('.source/pages', MarkdownPage::sourceDirectory());
        $this->assertSame('.source/posts', MarkdownPost::sourceDirectory());
        $this->assertSame('.source/docs', DocumentationPage::sourceDirectory());
    }

    public function testBuildServiceRecognizesChangedDirectory()
    {
        MarkdownPost::setSourceDirectory('_source/posts');

        $this->assertSame('_source/posts', MarkdownPost::sourceDirectory());
    }

    public function testAutodiscoveryDiscoversPostsInCustomDirectory()
    {
        $this->directory('_source');
        $this->file('_source/test.md');

        MarkdownPost::setSourceDirectory('_source');

        $this->assertSame(['test'], MarkdownPost::files());
    }

    public function testAutodiscoveryDiscoversPostsInCustomSubdirectory()
    {
        $this->directory('_source/posts');
        $this->file('_source/posts/test.md');

        MarkdownPost::setSourceDirectory('_source/posts');

        $this->assertSame(['test'], MarkdownPost::files());
    }
}
