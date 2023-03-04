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
        $this->assertEquals(['404', 'index'], BladePage::files());
    }

    public function test_get_source_file_list_for_markdown_page()
    {
        $this->file('_pages/foo.md');
        $this->assertEquals(['foo'], MarkdownPage::files());
    }

    public function test_get_source_file_list_for_markdown_post()
    {
        $this->file('_posts/foo.md');
        $this->assertEquals(['foo'], MarkdownPost::files());
    }

    public function test_get_source_file_list_for_documentation_page()
    {
        $this->file('_docs/foo.md');
        $this->assertEquals(['foo'], DocumentationPage::files());
    }

    public function test_get_source_file_list_for_markdown_page_model()
    {
        $this->file('_pages/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getModelIdentifiers(MarkdownPage::class));
    }

    public function test_get_source_file_list_for_blade_page_model()
    {
        $this->file('_pages/foo.blade.php');
        $this->assertEquals(['404', 'foo', 'index'], DiscoveryService::getModelIdentifiers(BladePage::class));
    }

    public function test_get_source_file_list_for_markdown_post_model()
    {
        $this->file('_posts/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getModelIdentifiers(MarkdownPost::class));
    }

    public function test_get_source_file_list_for_documentation_page_model()
    {
        $this->file('_docs/foo.md');
        $this->assertEquals(['foo'], DiscoveryService::getModelIdentifiers(DocumentationPage::class));
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
            Filesystem::deleteDirectory('foo');
            $model::setSourceDirectory($sourceDirectoryBackup);
            $model::setFileExtension($fileExtensionBackup);
        }
    }

    public function test_get_source_file_list_throws_exception_for_invalid_model_class()
    {
        $this->expectException(UnsupportedPageTypeException::class);

        DiscoveryService::getModelIdentifiers('NonExistentModel');
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
        self::mockConfig(['hyde.media_extensions' => 'custom']);
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
    }

    public function test_media_asset_extensions_can_be_added_by_comma_separated_values()
    {
        self::mockConfig(['hyde.media_extensions' => null]);
        $this->file('_media/test.1');
        $this->file('_media/test.2');
        $this->file('_media/test.3');

        $this->assertEquals([], DiscoveryService::getMediaAssetFiles());

        self::mockConfig(['hyde.media_extensions' => '1,2,3']);
        $this->assertEquals([
            Hyde::path('_media/test.1'),
            Hyde::path('_media/test.2'),
            Hyde::path('_media/test.3'),
        ], DiscoveryService::getMediaAssetFiles());
    }

    public function test_media_asset_extensions_can_be_added_by_comma_separated_values_containing_spaces()
    {
        self::mockConfig(['hyde.media_extensions' => null]);
        $this->file('_media/test.1');
        $this->file('_media/test.2');
        $this->file('_media/test.3');

        $this->assertEquals([], DiscoveryService::getMediaAssetFiles());
        self::mockConfig(['hyde.media_extensions' => '1, 2, 3']);
        $this->assertEquals([
            Hyde::path('_media/test.1'),
            Hyde::path('_media/test.2'),
            Hyde::path('_media/test.3'),
        ], DiscoveryService::getMediaAssetFiles());
    }

    public function test_media_asset_extensions_can_be_added_by_array()
    {
        self::mockConfig(['hyde.media_extensions' => null]);
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

        $this->assertEquals([$expected], DiscoveryService::getModelIdentifiers($model));
    }
}
