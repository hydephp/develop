<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;
use Hyde\Framework\Services\CollectionService;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\CollectionService
 */
class CollectionServiceTest extends TestCase
{
    public function test_class_exists()
    {
        $this->assertTrue(class_exists(CollectionService::class));
    }

    public function test_get_source_file_list_for_blade_page() {
        $this->assertEquals(['404', 'index'], CollectionService::getBladePageList());
    }

    public function test_get_source_file_list_for_markdown_page() {
        touch(Hyde::path('_pages/foo.md'));
        $this->assertEquals(['foo'], CollectionService::getMarkdownPageList());
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_get_source_file_list_for_markdown_post() {
        touch(Hyde::path('_posts/foo.md'));
        $this->assertEquals(['foo'], CollectionService::getMarkdownPostList());
        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_get_source_file_list_for_documentation_page() {
        touch(Hyde::path('_docs/foo.md'));
        $this->assertEquals(['foo'], CollectionService::getDocumentationPageList());
        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_get_source_file_list_for_model_method()
    {
        $this->unitTestMarkdownBasedPageList(MarkdownPage::class, '_pages/foo.md');
        $this->unitTestMarkdownBasedPageList(MarkdownPost::class, '_posts/foo.md');
        $this->unitTestMarkdownBasedPageList(DocumentationPage::class, '_docs/foo.md');
    }

    public function test_get_source_file_list_returns_false_for_invalid_method()
    {
        $this->assertFalse(CollectionService::getSourceFileListForModel('NonExistentModel'));
    }

    public function test_get_media_asset_files()
    {
        $this->assertTrue(is_array(CollectionService::getMediaAssetFiles()));
    }

    public function test_get_media_asset_files_discovers_files()
    {
        $testFiles = [
            'png',
            'svg',
            'jpg',
            'jpeg',
            'gif',
            'ico',
            'css',
            'js',
        ];
        foreach ($testFiles as $fileType) {
            $path = Hyde::path('_media/test.'.$fileType);
            touch($path);
            $this->assertContains($path, CollectionService::getMediaAssetFiles());
            unlink($path);
        }
    }

    public function test_get_media_asset_files_discovers_custom_file_types()
    {
        $path = Hyde::path('_media/test.custom');
        touch($path);
        $this->assertNotContains($path, CollectionService::getMediaAssetFiles());
        config(['hyde.media_extensions' => 'custom']);
        $this->assertContains($path, CollectionService::getMediaAssetFiles());
        unlink($path);
    }

    public function test_files_starting_with_underscore_are_ignored()
    {
        touch(Hyde::path('_posts/_foo.md'));
        $this->assertNotContains('_foo', CollectionService::getMarkdownPostList());
        $this->assertNotContains('foo', CollectionService::getMarkdownPostList());
        unlink(Hyde::path('_posts/_foo.md'));
    }

    protected function unitTestMarkdownBasedPageList(string $model, string $path)
    {
        touch(Hyde::path($path));

        $expected = basename($path,'.md');

        $this->assertEquals([$expected], CollectionService::getSourceFileListForModel($model));

        unlink(Hyde::path($path));
    }
}
