<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Services\DiscoveryService
 */
class CollectionServiceTest extends TestCase
{
    public function test_class_exists()
    {
        $this->assertTrue(class_exists(DiscoveryService::class));
    }

    public function test_get_source_file_list_for_blade_page()
    {
        $this->assertEquals(['404', 'index'], DiscoveryService::getBladePageFiles());
    }

    public function test_get_source_file_list_for_markdown_page()
    {
        Hyde::touch(('_pages/foo.md'));
        $this->assertEquals(['foo'], DiscoveryService::getMarkdownPageFiles());
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_get_source_file_list_for_markdown_post()
    {
        Hyde::touch(('_posts/foo.md'));
        $this->assertEquals(['foo'], DiscoveryService::getMarkdownPostFiles());
        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_get_source_file_list_for_documentation_page()
    {
        Hyde::touch(('_docs/foo.md'));
        $this->assertEquals(['foo'], DiscoveryService::getDocumentationPageFiles());
        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_get_source_file_list_for_model_method()
    {
        $this->unitTestMarkdownBasedPageList(MarkdownPage::class, '_pages/foo.md');
        $this->unitTestMarkdownBasedPageList(MarkdownPost::class, '_posts/foo.md');
        $this->unitTestMarkdownBasedPageList(DocumentationPage::class, '_docs/foo.md');
    }

    public function test_get_source_file_list_for_model_method_finds_customized_model_properties()
    {
        $matrix = [
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ];

        /** @var MarkdownPage $model */
        foreach ($matrix as $model) {
            // Setup
            @mkdir(Hyde::path('foo'));
            $sourceDirectoryBackup = $model::$sourceDirectory;
            $fileExtensionBackup = $model::$fileExtension;

            // Test baseline
            $this->unitTestMarkdownBasedPageList($model, $model::$sourceDirectory.'/foo.md');

            // Set the source directory to a custom value
            $model::$sourceDirectory = 'foo';

            // Test customized source directory
            $this->unitTestMarkdownBasedPageList($model, 'foo/foo.md');

            // Set file extension to a custom value
            $model::$fileExtension = '.foo';

            // Test customized file extension
            $this->unitTestMarkdownBasedPageList($model, 'foo/foo.foo', 'foo');

            // Cleanup
            File::deleteDirectory(Hyde::path('foo'));
            $model::$sourceDirectory = $sourceDirectoryBackup;
            $model::$fileExtension = $fileExtensionBackup;
        }
    }

    public function test_get_source_file_list_returns_false_for_invalid_method()
    {
        $this->assertFalse(DiscoveryService::getSourceFileListForModel('NonExistentModel'));
    }

    public function test_get_media_asset_files()
    {
        $this->assertTrue(is_array(DiscoveryService::getMediaAssetFiles()));
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
            $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
            unlink($path);
        }
    }

    public function test_get_media_asset_files_discovers_custom_file_types()
    {
        $path = Hyde::path('_media/test.custom');
        touch($path);
        $this->assertNotContains($path, DiscoveryService::getMediaAssetFiles());
        config(['hyde.media_extensions' => 'custom']);
        $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
        unlink($path);
    }

    public function test_blade_page_files_starting_with_underscore_are_ignored()
    {
        Hyde::touch(('_pages/_foo.blade.php'));
        $this->assertEquals([
            '404',
            'index',
        ], DiscoveryService::getBladePageFiles());
        unlink(Hyde::path('_pages/_foo.blade.php'));
    }

    public function test_markdown_page_files_starting_with_underscore_are_ignored()
    {
        Hyde::touch(('_pages/_foo.md'));
        $this->assertEquals([], DiscoveryService::getMarkdownPageFiles());
        unlink(Hyde::path('_pages/_foo.md'));
    }

    public function test_post_files_starting_with_underscore_are_ignored()
    {
        Hyde::touch(('_posts/_foo.md'));
        $this->assertEquals([], DiscoveryService::getMarkdownPostFiles());
        unlink(Hyde::path('_posts/_foo.md'));
    }

    public function test_documentation_page_files_starting_with_underscore_are_ignored()
    {
        Hyde::touch(('_docs/_foo.md'));
        $this->assertEquals([], DiscoveryService::getDocumentationPageFiles());
        unlink(Hyde::path('_docs/_foo.md'));
    }

    protected function unitTestMarkdownBasedPageList(string $model, string $path, ?string $expected = null)
    {
        Hyde::touch(($path));

        $expected = $expected ?? basename($path, '.md');

        $this->assertEquals([$expected], DiscoveryService::getSourceFileListForModel($model));

        unlink(Hyde::path($path));
    }
}
