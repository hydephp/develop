<?php

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Framework\Foundation\Filesystem;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Foundation\Filesystem
 *
 * @see \Hyde\Framework\Testing\Unit\Foundation\FluentFilesystemModelPathHelpersTest
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
}
