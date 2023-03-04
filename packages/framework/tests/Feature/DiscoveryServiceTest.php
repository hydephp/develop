<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Services\DiscoveryService
 *
 * @see \Hyde\Framework\Testing\Unit\DiscoveryServiceUnitTest
 */
class DiscoveryServiceTest extends TestCase
{
    public function test_get_source_file_list_for_markdown_page_model()
    {
        $this->file('_pages/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getSourceFileListForModel(MarkdownPage::class));
    }

    public function test_get_source_file_list_for_blade_page_model()
    {
        $this->file('_pages/foo.blade.php');
        $this->assertEquals(['404', 'foo', 'index'], DiscoveryService::getSourceFileListForModel(BladePage::class));
    }

    public function test_get_source_file_list_for_markdown_post_model()
    {
        $this->file('_posts/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getSourceFileListForModel(MarkdownPost::class));
    }

    public function test_get_source_file_list_for_documentation_page_model()
    {
        $this->file('_docs/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getSourceFileListForModel(DocumentationPage::class));
    }

    public function test_get_source_file_list_for_model_method_finds_customized_model_properties()
    {
        $matrix = [
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ];

        /** @var \Hyde\Pages\MarkdownPage $model */
        foreach ($matrix as $model) {
            // Setup
            @mkdir(Hyde::path('foo'));
            $sourceDirectoryBackup = $model::sourceDirectory();
            $fileExtensionBackup = $model::fileExtension();

            // Test baseline
            $this->unitTestMarkdownBasedPageList($model, $model::sourceDirectory().'/foo.md');

            // Set the source directory to a custom value
            $model::setSourceDirectory('foo');

            // Test customized source directory
            $this->unitTestMarkdownBasedPageList($model, 'foo/foo.md');

            // Set file extension to a custom value
            $model::setFileExtension('.foo');

            // Test customized file extension
            $this->unitTestMarkdownBasedPageList($model, 'foo/foo.foo', 'foo');

            // Cleanup
            File::deleteDirectory(Hyde::path('foo'));
            $model::setSourceDirectory($sourceDirectoryBackup);
            $model::setFileExtension($fileExtensionBackup);
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

    public function test_get_media_asset_files_discovers_files_recursively()
    {
        $path = Hyde::path('_media/foo/bar.png');
        mkdir(dirname($path));
        touch($path);
        $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
        File::deleteDirectory(Hyde::path('_media/foo'));
    }

    public function test_get_media_asset_files_discovers_files_very_recursively()
    {
        $path = Hyde::path('_media/foo/bar/img.png');
        mkdir(dirname($path), recursive: true);
        touch($path);
        $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
        File::deleteDirectory(Hyde::path('_media/foo'));
    }

    public function test_media_asset_extensions_can_be_added_by_comma_separated_values()
    {
        config(['hyde.media_extensions' => null]);
        Filesystem::touch('_media/test.1');
        Filesystem::touch('_media/test.2');
        Filesystem::touch('_media/test.3');

        $this->assertEquals([], DiscoveryService::getMediaAssetFiles());

        config(['hyde.media_extensions' => '1,2,3']);
        $this->assertEquals([
            Hyde::path('_media/test.1'),
            Hyde::path('_media/test.2'),
            Hyde::path('_media/test.3'),
        ], DiscoveryService::getMediaAssetFiles());

        Filesystem::unlink('_media/test.1');
        Filesystem::unlink('_media/test.2');
        Filesystem::unlink('_media/test.3');
    }

    public function test_media_asset_extensions_can_be_added_by_comma_separated_values_containing_spaces()
    {
        config(['hyde.media_extensions' => null]);
        Filesystem::touch('_media/test.1');
        Filesystem::touch('_media/test.2');
        Filesystem::touch('_media/test.3');

        $this->assertEquals([], DiscoveryService::getMediaAssetFiles());
        config(['hyde.media_extensions' => '1, 2, 3']);
        $this->assertEquals([
            Hyde::path('_media/test.1'),
            Hyde::path('_media/test.2'),
            Hyde::path('_media/test.3'),
        ], DiscoveryService::getMediaAssetFiles());

        Filesystem::unlink('_media/test.1');
        Filesystem::unlink('_media/test.2');
        Filesystem::unlink('_media/test.3');
    }

    public function test_media_asset_extensions_can_be_added_by_array()
    {
        config(['hyde.media_extensions' => null]);
        Filesystem::touch('_media/test.1');
        Filesystem::touch('_media/test.2');
        Filesystem::touch('_media/test.3');

        $this->assertEquals([], DiscoveryService::getMediaAssetFiles());
        config(['hyde.media_extensions' => ['1', '2', '3']]);
        $this->assertEquals([
            Hyde::path('_media/test.1'),
            Hyde::path('_media/test.2'),
            Hyde::path('_media/test.3'),
        ], DiscoveryService::getMediaAssetFiles());

        Filesystem::unlink('_media/test.1');
        Filesystem::unlink('_media/test.2');
        Filesystem::unlink('_media/test.3');
    }

    public function test_path_to_identifier_helper_formats_path_to_identifier()
    {
        foreach ([MarkdownPage::class, MarkdownPost::class, DocumentationPage::class] as $page) {
            $this->assertEquals('foo', DiscoveryService::pathToIdentifier($page, 'foo'));
            $this->assertEquals('foo', DiscoveryService::pathToIdentifier($page, 'foo.md'));
            $this->assertEquals('foo/bar', DiscoveryService::pathToIdentifier($page, 'foo/bar.md'));
        }

        $this->assertEquals('foo', DiscoveryService::pathToIdentifier(BladePage::class, 'foo'));
        $this->assertEquals('foo', DiscoveryService::pathToIdentifier(BladePage::class, 'foo.blade.php'));
        $this->assertEquals('foo/bar', DiscoveryService::pathToIdentifier(BladePage::class, 'foo/bar.blade.php'));

        $this->assertEquals('foo', DiscoveryService::pathToIdentifier(BladePage::class, Hyde::path('_pages/foo.blade.php')));
        $this->assertEquals('foo', DiscoveryService::pathToIdentifier(BladePage::class, '_pages/foo.blade.php'));
    }

    protected function unitTestMarkdownBasedPageList(string $model, string $path, ?string $expected = null)
    {
        Filesystem::touch($path);
        Hyde::boot(); // Reboot to rediscover new pages

        $expected = $expected ?? basename($path, '.md');

        $this->assertEquals([$expected], DiscoveryService::getSourceFileListForModel($model));

        Filesystem::unlink($path);
    }
}
