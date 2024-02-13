<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\HydeServiceProvider;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * Class SourceDirectoriesCanBeChangedTest.
 */
class SourceDirectoriesCanBeChangedTest extends TestCase
{
    public function testBaselines()
    {
        $this->assertEquals('_pages', HtmlPage::sourceDirectory());
        $this->assertEquals('_pages', BladePage::sourceDirectory());
        $this->assertEquals('_pages', MarkdownPage::sourceDirectory());
        $this->assertEquals('_posts', MarkdownPost::sourceDirectory());
        $this->assertEquals('_docs', DocumentationPage::sourceDirectory());
    }

    public function testSourceDirectoriesCanBeChangedProgrammatically()
    {
        HtmlPage::setSourceDirectory('.source/pages');
        BladePage::setSourceDirectory('.source/pages');
        MarkdownPage::setSourceDirectory('.source/pages');
        MarkdownPost::setSourceDirectory('.source/posts');
        DocumentationPage::setSourceDirectory('.source/docs');

        $this->assertEquals('.source/pages', HtmlPage::sourceDirectory());
        $this->assertEquals('.source/pages', BladePage::sourceDirectory());
        $this->assertEquals('.source/pages', MarkdownPage::sourceDirectory());
        $this->assertEquals('.source/posts', MarkdownPost::sourceDirectory());
        $this->assertEquals('.source/docs', DocumentationPage::sourceDirectory());
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

        $this->assertEquals('.source/pages', HtmlPage::sourceDirectory());
        $this->assertEquals('.source/pages', BladePage::sourceDirectory());
        $this->assertEquals('.source/pages', MarkdownPage::sourceDirectory());
        $this->assertEquals('.source/posts', MarkdownPost::sourceDirectory());
        $this->assertEquals('.source/docs', DocumentationPage::sourceDirectory());
    }

    public function testBuildServiceRecognizesChangedDirectory()
    {
        MarkdownPost::setSourceDirectory('_source/posts');

        $this->assertEquals(
            '_source/posts',
            MarkdownPost::sourceDirectory()
        );
    }

    public function testAutodiscoveryDiscoversPostsInCustomDirectory()
    {
        $this->directory('_source');
        $this->file('_source/test.md');

        MarkdownPost::setSourceDirectory('_source');

        $this->assertEquals(
            ['test'],
            MarkdownPost::files()
        );
    }

    public function testAutodiscoveryDiscoversPostsInCustomSubdirectory()
    {
        $this->directory('_source/posts');
        $this->file('_source/posts/test.md');

        MarkdownPost::setSourceDirectory('_source/posts');

        $this->assertEquals(
            ['test'],
            MarkdownPost::files()
        );
    }
}
