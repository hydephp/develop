<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * Class SourceDirectoriesCanBeChangedTest.
 */
class SourceDirectoriesCanBeChangedTest extends TestCase
{
    public function test_baselines()
    {
        $this->assertEquals('_pages', BladePage::$sourceDirectory);
        $this->assertEquals('_pages', MarkdownPage::$sourceDirectory);
        $this->assertEquals('_posts', MarkdownPost::$sourceDirectory);
        $this->assertEquals('_docs', DocumentationPage::$sourceDirectory);
    }

    public function test_source_directories_can_be_changed_programmatically()
    {
        BladePage::$sourceDirectory = '.source/pages';
        MarkdownPage::$sourceDirectory = '.source/pages';
        MarkdownPost::$sourceDirectory = '.source/posts';
        DocumentationPage::$sourceDirectory = '.source/docs';

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

    public function test_autodiscovery_discovers_posts_in_changed_directory()
    {
        // Using a subdirectory in a directory we know exists, to make cleanup easier.
        mkdir(Hyde::path('_posts/test'));
        Hyde::touch(('_posts/test/test.md'));

        MarkdownPost::$sourceDirectory = '_posts/test';

        $this->assertEquals(
            ['test'],
            DiscoveryService::getSourceFileListForModel(MarkdownPost::class)
        );

        unlink(Hyde::path('_posts/test/test.md'));
        rmdir(Hyde::path('_posts/test'));
    }
}
