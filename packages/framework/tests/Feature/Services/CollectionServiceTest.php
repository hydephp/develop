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

    public function test_get_source_file_list_for_model_method()
    {
        $this->testListUnit(BladePage::class, '_pages/a8a7b7ce.blade.php');
        $this->testListUnit(MarkdownPage::class, '_pages/a8a7b7ce.md');
        $this->testListUnit(MarkdownPost::class, '_posts/a8a7b7ce.md');
        $this->testListUnit(DocumentationPage::class, '_docs/a8a7b7ce.md');

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

    private function testListUnit(string $model, string $path)
    {
        touch(Hyde::path($path));

        $expected = str_replace(['.md', '.blade.php'], '', basename($path));

        $this->assertContains($expected, CollectionService::getSourceFileListForModel($model));

        unlink(Hyde::path($path));
    }
}
