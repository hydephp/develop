<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\HydeServiceProvider;
use Hyde\Framework\Services\DiscoveryService;
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
    public function test_baselines()
    {
        $this->assertEquals('_pages', HtmlPage::$sourceDirectory);
        $this->assertEquals('_pages', BladePage::$sourceDirectory);
        $this->assertEquals('_pages', MarkdownPage::$sourceDirectory);
        $this->assertEquals('_posts', MarkdownPost::$sourceDirectory);
        $this->assertEquals('_docs', DocumentationPage::$sourceDirectory);
    }

    public function test_source_directories_can_be_changed_programmatically()
    {
        HtmlPage::$sourceDirectory = '.source/pages';
        BladePage::$sourceDirectory = '.source/pages';
        MarkdownPage::$sourceDirectory = '.source/pages';
        MarkdownPost::$sourceDirectory = '.source/posts';
        DocumentationPage::$sourceDirectory = '.source/docs';

        $this->assertEquals('.source/pages', HtmlPage::$sourceDirectory);
        $this->assertEquals('.source/pages', BladePage::$sourceDirectory);
        $this->assertEquals('.source/pages', MarkdownPage::$sourceDirectory);
        $this->assertEquals('.source/posts', MarkdownPost::$sourceDirectory);
        $this->assertEquals('.source/docs', DocumentationPage::$sourceDirectory);
    }

    public function test_source_directories_can_be_changed_in_config()
    {
        config(['hyde.source_directories' => [
            HtmlPage::class => '.source/pages',
            BladePage::class => '.source/pages',
            MarkdownPage::class => '.source/pages',
            MarkdownPost::class => '.source/posts',
            DocumentationPage::class => '.source/docs',
        ]]);

        (new HydeServiceProvider($this->app))->register();

        $this->assertEquals('.source/pages', HtmlPage::$sourceDirectory);
        $this->assertEquals('.source/pages', BladePage::$sourceDirectory);
        $this->assertEquals('.source/pages', MarkdownPage::$sourceDirectory);
        $this->assertEquals('.source/posts', MarkdownPost::$sourceDirectory);
        $this->assertEquals('.source/docs', DocumentationPage::$sourceDirectory);
    }

    public function test_build_service_recognizes_changed_directory()
    {
        MarkdownPost::$sourceDirectory = '_source/posts';

        $this->assertEquals(
            '_source/posts',
            DiscoveryService::getModelSourceDirectory(MarkdownPost::class)
        );
    }

    public function test_autodiscovery_discovers_posts_in_custom_directory()
    {
        $this->directory('_source');
        $this->file('_source/test.md');

        MarkdownPost::$sourceDirectory = '_source';

        $this->assertEquals(
            ['test'],
            DiscoveryService::getSourceFileListForModel(MarkdownPost::class)
        );
    }

    public function test_autodiscovery_discovers_posts_in_custom_subdirectory()
    {
        $this->directory('_source/posts');
        $this->file('_source/posts/test.md');

        MarkdownPost::$sourceDirectory = '_source/posts';

        $this->assertEquals(
            ['test'],
            DiscoveryService::getSourceFileListForModel(MarkdownPost::class)
        );
    }
}
