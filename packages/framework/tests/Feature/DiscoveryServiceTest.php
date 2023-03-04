<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Services\DiscoveryService
 */
class DiscoveryServiceTest extends UnitTestCase
{
    protected array $filesToDelete = [];

    protected function setUp(): void
    {
        self::setupKernel();
        self::mockConfig();
    }

    protected function tearDown(): void
    {
        foreach ($this->filesToDelete as $file) {
            if (is_dir($file)) {
                Filesystem::deleteDirectory($file);
            } else {
                @unlink($file);
            }
        }
        $this->filesToDelete = [];
    }

    protected function file(string $path): void
    {
        $this->filesToDelete[] = Hyde::path($path);
        touch(Hyde::path($path));
    }

    protected function directory(string $path, bool $recursive = false): void
    {
        $this->filesToDelete[] = Hyde::path($path);
        @mkdir(Hyde::path($path), recursive: $recursive);
    }

    public function test_get_source_file_list_for_model_method_finds_customized_model_properties()
    {
        // Setup
        $this->directory('foo');
        $sourceDirectoryBackup = MarkdownPage::sourceDirectory();
        $fileExtensionBackup = MarkdownPage::fileExtension();

        // Test baseline
        $this->unitTestMarkdownBasedPageList(MarkdownPage::class, MarkdownPage::sourceDirectory().'/foo.md');

        // Set the source directory to a custom value
        MarkdownPage::setSourceDirectory('foo');

        // Test customized source directory
        $this->unitTestMarkdownBasedPageList(MarkdownPage::class, 'foo/foo.md');

        // Set file extension to a custom value
        MarkdownPage::setFileExtension('.foo');

        // Test customized file extension
        $this->unitTestMarkdownBasedPageList(MarkdownPage::class, 'foo/foo.foo', 'foo');

        // Cleanup
        MarkdownPage::setSourceDirectory($sourceDirectoryBackup);
        MarkdownPage::setFileExtension($fileExtensionBackup);
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
            $this->file($path);
            $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
        }
    }

    public function test_get_media_asset_files_discovers_custom_file_types()
    {
        $path = Hyde::path('_media/test.custom');
        $this->file($path);
        $this->assertNotContains($path, DiscoveryService::getMediaAssetFiles());
        self::mockConfig(['hyde.media_extensions' => ['custom']]);
        $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
    }

    public function test_get_media_asset_files_discovers_files_recursively()
    {
        $path = Hyde::path('_media/foo/bar.png');
        $this->directory('_media/foo');
        $this->file($path);
        $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
    }

    public function test_get_media_asset_files_discovers_files_very_recursively()
    {
        $path = Hyde::path('_media/foo/bar/img.png');
        $this->directory(dirname($path), recursive: true);
        $this->file($path);
        $this->assertContains($path, DiscoveryService::getMediaAssetFiles());
        Filesystem::deleteDirectory(Hyde::path('_media/foo'));
    }

    public function test_media_asset_extensions_can_be_added_by_comma_separated_values()
    {
        self::mockConfig(['hyde.media_extensions' => []]);
        $this->file('_media/test.1');
        $this->file('_media/test.2');
        $this->file('_media/test.3');

        $this->assertEquals([], DiscoveryService::getMediaAssetFiles());

        self::mockConfig(['hyde.media_extensions' => ['1,2,3']]);
        $this->assertEquals([
            Hyde::path('_media/test.1'),
            Hyde::path('_media/test.2'),
            Hyde::path('_media/test.3'),
        ], DiscoveryService::getMediaAssetFiles());
    }

    public function test_media_asset_extensions_can_be_added_by_array()
    {
        self::mockConfig(['hyde.media_extensions' => []]);
        $this->file('_media/test.1');
        $this->file('_media/test.2');
        $this->file('_media/test.3');

        $this->assertEquals([], DiscoveryService::getMediaAssetFiles());
        self::mockConfig(['hyde.media_extensions' => ['1', '2', '3']]);
        $this->assertEquals([
            Hyde::path('_media/test.1'),
            Hyde::path('_media/test.2'),
            Hyde::path('_media/test.3'),
        ], DiscoveryService::getMediaAssetFiles());
    }

    public function test_blade_page_files_starting_with_underscore_are_ignored()
    {
        $this->file('_pages/_foo.blade.php');
        $this->assertEquals(['404', 'index'], BladePage::files());
    }

    public function test_markdown_page_files_starting_with_underscore_are_ignored()
    {
        $this->file('_pages/_foo.md');
        $this->assertEquals([], MarkdownPage::files());
    }

    public function test_post_files_starting_with_underscore_are_ignored()
    {
        $this->file('_posts/_foo.md');
        $this->assertEquals([], MarkdownPost::files());
    }

    public function test_documentation_page_files_starting_with_underscore_are_ignored()
    {
        $this->file('_docs/_foo.md');
        $this->assertEquals([], DocumentationPage::files());
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
        $this->file($path);
        Hyde::boot(); // Reboot to rediscover new pages

        $expected = $expected ?? basename($path, '.md');

        $this->assertEquals([$expected], $model::files());
    }
}
