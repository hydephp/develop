<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Framework\Foundation\Filesystem;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\HydeKernel
 * @covers \Hyde\Framework\Foundation\Filesystem
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
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'foo/bar.php', $this->filesystem->path('foo/bar.php'));
    }

    public function test_path_method_returns_qualified_file_path_when_supplied_with_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'file.php', $this->filesystem->path('file.php'));
    }

    public function test_path_method_returns_expected_value_for_nested_path_arguments()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'directory/file.php', $this->filesystem->path('directory/file.php'));
    }

    public function test_path_method_strips_trailing_directory_separators_from_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'file.php', $this->filesystem->path('\\/file.php/'));
    }

    public function test_path_method_returns_expected_value_regardless_of_trailing_directory_separators_in_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'bar/file.php', $this->filesystem->path('\\/bar/file.php/'));
    }

    public function test_vendor_path_method_exists()
    {
        $this->assertTrue(method_exists(Filesystem::class, 'vendorPath'));
    }

    public function test_vendor_path_method_returns_string()
    {
        $this->assertIsString($this->filesystem->vendorPath());
    }

    public function test_vendor_path_method_returns_qualified_file_path_when_supplied_with_argument()
    {
        $this->assertEquals($this->filesystem->vendorPath('file.php'), $this->filesystem->vendorPath().'/file.php');
    }

    public function test_vendor_path_method_returns_expected_value_regardless_of_trailing_directory_separators_in_argument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'vendor/hyde/framework/file.php', $this->filesystem->vendorPath('\\//file.php/'));
    }

    public function test_copy_method()
    {
        touch(Hyde::path('foo'));
        $this->assertTrue(method_exists(Filesystem::class, 'copy'));
        $this->assertTrue(Hyde::copy('foo', 'bar'));
        $this->assertFileExists(Hyde::path('bar'));
        unlink(Hyde::path('foo'));
        unlink(Hyde::path('bar'));
    }

    public function test_touch_helper_creates_file_at_given_path()
    {
        $this->assertTrue(Hyde::touch('foo'));
        $this->assertFileExists(Hyde::path('foo'));
        unlink(Hyde::path('foo'));
    }

    public function test_touch_helper_creates_multiple_files_at_given_paths()
    {
        $this->assertTrue(Hyde::touch(['foo', 'bar']));
        $this->assertFileExists(Hyde::path('foo'));
        $this->assertFileExists(Hyde::path('bar'));
        unlink(Hyde::path('foo'));
        unlink(Hyde::path('bar'));
    }

    public function test_unlink_helper_deletes_file_at_given_path()
    {
        touch(Hyde::path('foo'));
        $this->assertTrue(Hyde::unlink('foo'));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function test_unlink_helper_deletes_multiple_files_at_given_paths()
    {
        touch(Hyde::path('foo'));
        touch(Hyde::path('bar'));
        $this->assertTrue(Hyde::unlink(['foo', 'bar']));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
        $this->assertFileDoesNotExist(Hyde::path('bar'));
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
            Hyde::path('_posts'.DIRECTORY_SEPARATOR.'foo.md'),
            Hyde::getModelSourcePath(MarkdownPost::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_pages'.DIRECTORY_SEPARATOR.'foo.md'),
            Hyde::getModelSourcePath(MarkdownPage::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_docs'.DIRECTORY_SEPARATOR.'foo.md'),
            Hyde::getModelSourcePath(DocumentationPage::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_pages'.DIRECTORY_SEPARATOR.'foo.md'),
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
            Hyde::path('_site'.DIRECTORY_SEPARATOR.'foo.html'),
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
            Hyde::path('_site'.DIRECTORY_SEPARATOR.'foo.html'),
            Hyde::sitePath('/foo.html/')
        );
    }

    public function test_path_to_relative_helper_decodes_hyde_path_into_relative()
    {
        $s = DIRECTORY_SEPARATOR;
        $this->assertEquals('foo', Hyde::pathToRelative(Hyde::path('foo')));
        $this->assertEquals('foo', Hyde::pathToRelative(Hyde::path('/foo/')));
        $this->assertEquals('foo.md', Hyde::pathToRelative(Hyde::path('foo.md')));
        $this->assertEquals("foo{$s}bar", Hyde::pathToRelative(Hyde::path("foo{$s}bar")));
        $this->assertEquals("foo{$s}bar.md", Hyde::pathToRelative(Hyde::path("foo{$s}bar.md")));
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
            $this->assertEquals(
                $this->systemPath(($testString)),
                Hyde::pathToRelative(
                    $this->systemPath($testString)
                )
            );
        }
    }

    protected function systemPath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
