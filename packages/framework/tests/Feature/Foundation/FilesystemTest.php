<?php

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;
use Hyde\Framework\Foundation\Filesystem;

/**
 * @covers \Hyde\Framework\Foundation\Filesystem
 *
 * @see \Hyde\Framework\Testing\Unit\Foundation\FilesystemSafeCopyHelperTest
 * @see \Hyde\Framework\Testing\Unit\Foundation\FluentFilesystemModelPathHelpersTest
 */
class FilesystemTest extends TestCase
{
    // Filesystem with mocked Kernel path. Use the real one if you actually need to access the filesystem.
    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = $this->mock(HydeKernel::class);
        $kernel->shouldReceive('getBasePath')->andReturn('/foo');
        $this->filesystem = new Filesystem($kernel);
    }

    public function test_get_base_path_returns_kernels_base_path()
    {
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
        $this->assertEquals('/foo', $this->filesystem->path());
    }

    public function test_path_method_returns_path_relative_to_base_path_when_supplied_with_argument()
    {
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'foo/bar.php', $this->filesystem->path('foo/bar.php'));
    }

    public function test_path_method_returns_qualified_file_path_when_supplied_with_argument()
    {
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'file.php', $this->filesystem->path('file.php'));
    }

    public function test_path_method_returns_expected_value_for_nested_path_arguments()
    {
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'directory/file.php', $this->filesystem->path('directory/file.php'));
    }

    public function test_path_method_strips_trailing_directory_separators_from_argument()
    {
        $this->assertEquals('/foo'.DIRECTORY_SEPARATOR.'file.php', $this->filesystem->path('\\/file.php/'));
    }

    public function test_path_method_returns_expected_value_regardless_of_trailing_directory_separators_in_argument()
    {
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
        $this->assertEquals('/foo' . DIRECTORY_SEPARATOR . 'vendor/hyde/framework/file.php', $this->filesystem->vendorPath('\\//file.php/'));
    }

    public function test_copy_method()
    {
        $this->assertTrue(method_exists(Filesystem::class, 'copy'));
    }

    public function test_copy_method_returns_404_when_file_does_not_exist()
    {
        $this->assertEquals(404, $this->filesystem->copy('foo/bar.php', 'foo/baz.php'));
    }

    public function test_copy_method_returns_409_when_destination_file_exists()
    {
        touch('foo');
        touch('bar');
        $this->assertEquals(409, $this->filesystem->copy('foo', 'bar'));
        unlink('foo');
        unlink('bar');
    }

    public function test_copy_method_overwrites_destination_file_when_overwrite_is_true()
    {
        touch('foo');
        touch('bar');
        $this->assertTrue($this->filesystem->copy('foo', 'bar', true));
        unlink('foo');
        unlink('bar');
    }

    public function test_get_model_source_path()
    {

    }

    public function test_get_blade_page_path()
    {

    }

    public function test_get_markdown_page_path()
    {

    }

    public function test_get_markdown_post_path()
    {

    }

    public function test_get_documentation_page_path()
    {

    }

    public function test_get_site_output_path()
    {

    }

    public function test_path_to_relative()
    {

    }
}
