<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;

use function is_array;
use function touch;
use function unlink;

/**
 * @covers \Hyde\Framework\Services\DiscoveryService
 *
 * @see \Hyde\Framework\Testing\Feature\DiscoveryServiceTest
 */
class DiscoveryServiceUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::setupKernel();
        self::mockConfig();
    }

    public function test_get_file_extension_for_model_files()
    {
        $this->assertEquals('.md', DiscoveryService::getModelFileExtension(MarkdownPage::class));
        $this->assertEquals('.md', DiscoveryService::getModelFileExtension(MarkdownPost::class));
        $this->assertEquals('.md', DiscoveryService::getModelFileExtension(DocumentationPage::class));
        $this->assertEquals('.blade.php', DiscoveryService::getModelFileExtension(BladePage::class));
    }

    public function test_get_file_path_for_model_class_files()
    {
        $this->assertEquals('_posts', DiscoveryService::getModelSourceDirectory(MarkdownPost::class));
        $this->assertEquals('_pages', DiscoveryService::getModelSourceDirectory(MarkdownPage::class));
        $this->assertEquals('_docs', DiscoveryService::getModelSourceDirectory(DocumentationPage::class));
        $this->assertEquals('_pages', DiscoveryService::getModelSourceDirectory(BladePage::class));
    }


    public function test_get_source_file_list_for_blade_page()
    {
        $this->assertEquals(['404', 'index'], DiscoveryService::getBladePageFiles());
    }

    public function test_get_source_file_list_for_markdown_page()
    {
        Filesystem::touch('_pages/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getMarkdownPageFiles());
        Filesystem::unlink('_pages/foo.md');
    }

    public function test_get_source_file_list_for_markdown_post()
    {
        Filesystem::touch('_posts/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getMarkdownPostFiles());
        Filesystem::unlink('_posts/foo.md');
    }

    public function test_get_source_file_list_for_documentation_page()
    {
        Filesystem::touch('_docs/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getDocumentationPageFiles());
        Filesystem::unlink('_docs/foo.md');
    }


    public function test_get_source_file_list_throws_exception_for_invalid_model_class()
    {
        $this->expectException(UnsupportedPageTypeException::class);

        DiscoveryService::getSourceFileListForModel('NonExistentModel');
    }

    public function test_get_media_asset_files()
    {
        $this->assertTrue(is_array(DiscoveryService::getMediaAssetFiles()));
    }

    public function test_get_media_asset_files_discovers_files()
    {
        $testFiles = ['png', 'svg', 'jpg', 'jpeg', 'gif', 'ico', 'css', 'js'];

        foreach ($testFiles as $fileType) {
            $path = Hyde::path('_media/test.'.$fileType);
            touch($path);
            $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
            unlink($path);
        }
    }

    public function test_blade_page_files_starting_with_underscore_are_ignored()
    {
        Filesystem::touch('_pages/_foo.blade.php');
        $this->assertEquals(['404', 'index'], DiscoveryService::getBladePageFiles());
        Filesystem::unlink('_pages/_foo.blade.php');
    }

    public function test_markdown_page_files_starting_with_underscore_are_ignored()
    {
        Filesystem::touch('_pages/_foo.md');
        $this->assertEquals([], DiscoveryService::getMarkdownPageFiles());
        Filesystem::unlink('_pages/_foo.md');
    }

    public function test_post_files_starting_with_underscore_are_ignored()
    {
        Filesystem::touch('_posts/_foo.md');
        $this->assertEquals([], DiscoveryService::getMarkdownPostFiles());
        Filesystem::unlink('_posts/_foo.md');
    }

    public function test_documentation_page_files_starting_with_underscore_are_ignored()
    {
        Filesystem::touch('_docs/_foo.md');
        $this->assertEquals([], DiscoveryService::getDocumentationPageFiles());
        Filesystem::unlink('_docs/_foo.md');
    }
}
