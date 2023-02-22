<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Foundation\PharSupport;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use function Hyde\normalize_slashes;

/**
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Kernel\Filesystem
 */
class FilesystemTest extends TestCase
{
    protected string $originalBasePath;

    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalBasePath = Hyde::getBasePath();
        $this->filesystem = new Filesystem(Hyde::getInstance());
    }

    protected function tearDown(): void
    {
        Hyde::getInstance()->setBasePath($this->originalBasePath);

        parent::tearDown();
    }

    public function test_get_base_path_returns_kernels_base_path()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo', $this->filesystem->getBasePath());
    }

    public function test_path_method_exists()
    {
        $this->assertTrue(method_exists(Filesystem::class, 'path'));
    }

    public function test_path_method_returns_string()
    {
        $this->assertIsString($this->filesystem->path());
    }

    public function test_path_method_returns_base_path_when_not_supplied_with_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo', $this->filesystem->path());
    }

    public function test_path_method_returns_path_relative_to_base_path_when_supplied_with_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo/foo/bar.php', $this->filesystem->path('foo/bar.php'));
    }

    public function test_path_method_returns_qualified_file_path_when_supplied_with_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo/file.php', $this->filesystem->path('file.php'));
    }

    public function test_path_method_returns_expected_value_for_nested_path_arguments()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo/directory/file.php', $this->filesystem->path('directory/file.php'));
    }

    public function test_path_method_strips_trailing_directory_separators_from_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo/file.php', $this->filesystem->path('\\/file.php/'));
    }

    public function test_path_method_returns_expected_value_regardless_of_trailing_directory_separators_in_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo/bar/file.php', $this->filesystem->path('\\/bar/file.php/'));
    }

    public function test_vendor_path_method_exists()
    {
        $this->assertTrue(method_exists(Filesystem::class, 'vendorPath'));
    }

    public function test_vendor_path_method_returns_string()
    {
        $this->assertIsString($this->filesystem->vendorPath());
    }

    public function test_vendor_path_method_returns_the_vendor_path()
    {
        $this->assertSame(Hyde::path('vendor/hyde/framework'), $this->filesystem->vendorPath());
    }

    public function test_vendor_path_method_returns_qualified_file_path_when_supplied_with_argument()
    {
        $this->assertEquals($this->filesystem->vendorPath('file.php'), $this->filesystem->vendorPath().'/file.php');
    }

    public function test_vendor_path_method_returns_expected_value_regardless_of_trailing_directory_separators_in_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo/vendor/hyde/framework/file.php', $this->filesystem->vendorPath('\\//file.php/'));
    }

    public function test_vendor_path_can_specify_which_hyde_package_to_use()
    {
        $this->assertDirectoryExists(Hyde::vendorPath(package: 'realtime-compiler'));
        $this->assertFileExists(Hyde::vendorPath('composer.json', 'realtime-compiler'));
    }

    public function test_vendor_path_can_run_in_phar()
    {
        PharSupport::mock('running', true);
        PharSupport::mock('hasVendorDirectory', false);

        $this->assertContains($this->filesystem->vendorPath(), [
            // Monorepo support for symlinked packages directory
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('packages/framework')),
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('vendor/hyde/framework')),
        ]);

        PharSupport::clearMocks();
    }

    public function test_touch_helper_creates_file_at_given_path()
    {
        $this->assertTrue($this->filesystem->touch('foo'));
        $this->assertFileExists(Hyde::path('foo'));
        $this->filesystem->unlink('foo');
    }

    public function test_touch_helper_creates_multiple_files_at_given_paths()
    {
        $this->assertTrue($this->filesystem->touch(['foo', 'bar']));
        $this->assertFileExists(Hyde::path('foo'));
        $this->assertFileExists(Hyde::path('bar'));
        $this->filesystem->unlink('foo');
        $this->filesystem->unlink('bar');
    }

    public function test_unlink_helper_deletes_file_at_given_path()
    {
        touch(Hyde::path('foo'));
        $this->assertTrue($this->filesystem->unlink('foo'));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function test_unlink_helper_deletes_multiple_files_at_given_paths()
    {
        touch(Hyde::path('foo'));
        touch(Hyde::path('bar'));
        $this->assertTrue($this->filesystem->unlink(['foo', 'bar']));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
        $this->assertFileDoesNotExist(Hyde::path('bar'));
    }

    public function test_unlinkIfExists_helper_deletes_file_at_given_path()
    {
        touch(Hyde::path('foo'));
        $this->assertTrue($this->filesystem->unlinkIfExists('foo'));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function test_unlinkIfExists_handles_non_existent_files_gracefully()
    {
        $this->assertFalse($this->filesystem->unlinkIfExists('foo'));
    }

    public function test_get_model_source_path_method_returns_path_for_model_classes()
    {
        $this->assertEquals(
            Hyde::path('_posts'),
            Hyde::getModelSourcePath(MarkdownPost::class)
        );

        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getModelSourcePath(MarkdownPage::class)
        );

        $this->assertEquals(
            Hyde::path('_docs'),
            Hyde::getModelSourcePath(DocumentationPage::class)
        );

        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getModelSourcePath(BladePage::class)
        );
    }

    public function test_get_model_source_path_method_returns_path_to_file_for_model_classes()
    {
        $this->assertEquals(
            Hyde::path('_posts/foo.md'),
            Hyde::getModelSourcePath(MarkdownPost::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_pages/foo.md'),
            Hyde::getModelSourcePath(MarkdownPage::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_docs/foo.md'),
            Hyde::getModelSourcePath(DocumentationPage::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_pages/foo.md'),
            Hyde::getModelSourcePath(BladePage::class, 'foo.md')
        );
    }

    public function test_helper_for_blade_pages()
    {
        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getBladePagePath()
        );
    }

    public function test_helper_for_markdown_pages()
    {
        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getMarkdownPagePath()
        );
    }

    public function test_helper_for_markdown_posts()
    {
        $this->assertEquals(
            Hyde::path('_posts'),
            Hyde::getMarkdownPostPath()
        );
    }

    public function test_helper_for_documentation_pages()
    {
        $this->assertEquals(
            Hyde::path('_docs'),
            Hyde::getDocumentationPagePath()
        );
    }

    public function test_helper_for_media_path()
    {
        $this->assertEquals(
            Hyde::path('_media'),
            Hyde::mediaPath()
        );
    }

    public function test_helper_for_media_path_returns_path_to_file_within_the_directory()
    {
        $this->assertEquals(
            Hyde::path('_media/foo.css'),
            Hyde::mediaPath('foo.css')
        );
    }

    public function test_get_media_path_returns_absolute_path()
    {
        $this->assertEquals(
            Hyde::path('_media'),
            Hyde::mediaPath()
        );
    }

    public function test_helper_for_media_output_path()
    {
        $this->assertEquals(
            Hyde::path('_site/media'),
            Hyde::siteMediaPath()
        );
    }

    public function test_helper_for_media_output_path_returns_path_to_file_within_the_directory()
    {
        $this->assertEquals(
            Hyde::path('_site/media/foo.css'),
            Hyde::siteMediaPath('foo.css')
        );
    }

    public function test_get_media_output_path_returns_absolute_path()
    {
        $this->assertEquals(
            Hyde::path('_site/media'),
            Hyde::siteMediaPath()
        );
    }

    public function test_helper_for_site_output_path()
    {
        $this->assertEquals(
            Hyde::path('_site'),
            Hyde::sitePath()
        );
    }

    public function test_helper_for_site_output_path_returns_path_to_file_within_the_directory()
    {
        $this->assertEquals(
            Hyde::path('_site/foo.html'),
            Hyde::sitePath('foo.html')
        );
    }

    public function test_get_site_output_path_returns_absolute_path()
    {
        $this->assertEquals(
            Hyde::path('_site'),
            Hyde::sitePath()
        );
    }

    public function test_site_output_path_helper_ignores_trailing_slashes()
    {
        $this->assertEquals(
            Hyde::path('_site/foo.html'),
            Hyde::sitePath('/foo.html/')
        );
    }

    public function test_pathToAbsolute()
    {
        $this->assertSame(
            Hyde::path('foo'),
            Hyde::pathToAbsolute('foo')
        );
    }

    public function test_path_to_absolute_helper_is_alias_for_path_helper()
    {
        $this->assertSame(
            Hyde::path('foo'),
            $this->filesystem->pathToAbsolute('foo')
        );
    }

    public function test_pathToAbsolute_can_convert_array_of_paths()
    {
        $this->assertSame(
            [Hyde::path('foo'), Hyde::path('bar')],
            $this->filesystem->pathToAbsolute(['foo', 'bar'])
        );
    }

    public function test_path_to_relative_helper_decodes_hyde_path_into_relative()
    {
        $this->assertEquals('foo', Hyde::pathToRelative(Hyde::path('foo')));
        $this->assertEquals('foo', Hyde::pathToRelative(Hyde::path('/foo/')));
        $this->assertEquals('foo.md', Hyde::pathToRelative(Hyde::path('foo.md')));
        $this->assertEquals('foo/bar', Hyde::pathToRelative(Hyde::path('foo/bar')));
        $this->assertEquals('foo/bar.md', Hyde::pathToRelative(Hyde::path('foo/bar.md')));
    }

    public function test_path_to_relative_helper_does_not_modify_already_relative_paths()
    {
        $this->assertEquals('foo', Hyde::pathToRelative('foo'));
        $this->assertEquals('foo/', Hyde::pathToRelative('foo/'));
        $this->assertEquals('../foo', Hyde::pathToRelative('../foo'));
        $this->assertEquals('../foo/', Hyde::pathToRelative('../foo/'));
        $this->assertEquals('foo.md', Hyde::pathToRelative('foo.md'));
        $this->assertEquals('foo/bar', Hyde::pathToRelative('foo/bar'));
        $this->assertEquals('foo/bar.md', Hyde::pathToRelative('foo/bar.md'));
    }

    public function test_path_to_relative_helper_does_not_modify_non_project_paths()
    {
        $testStrings = [
            'C:\Documents\Newsletters\Summer2018.pdf',
            '\Program Files\Custom Utilities\StringFinder.exe',
            '2018\January.xlsx',
            '..\Publications\TravelBrochure.pdf',
            'C:\Projects\library\library.sln',
            'C:Projects\library\library.sln',
            '/home/seth/Pictures/penguin.jpg',
            '~/Pictures/penguin.jpg',
        ];

        foreach ($testStrings as $testString) {
            $this->assertEquals(normalize_slashes($testString), Hyde::pathToRelative($testString));
        }
    }
}
