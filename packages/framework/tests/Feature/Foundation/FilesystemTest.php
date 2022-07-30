<?php

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;
use Hyde\Framework\Foundation\Filesystem;

/**
 * @covers \Hyde\Framework\Foundation\Filesystem
 */
class FilesystemTest extends TestCase
{
    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem(new HydeKernel());
    }

    public function test_get_base_path_returns_kernels_base_path()
    {
        $kernel = $this->mock(HydeKernel::class);
        $kernel->shouldReceive('getBasePath')->andReturn('/path/to/project');
        $filesystem = new Filesystem($kernel);
        $this->assertEquals('/path/to/project', $filesystem->getBasePath());
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
        $kernel = $this->mock(HydeKernel::class);
        $kernel->shouldReceive('getBasePath')->andReturn('/path/to/project');
        $filesystem = new Filesystem($kernel);
        $this->assertEquals('/path/to/project', $filesystem->path());
    }

    public function test_path_method_returns_path_relative_to_base_path_when_supplied_with_argument()
    {
        $kernel = $this->mock(HydeKernel::class);
        $kernel->shouldReceive('getBasePath')->andReturn('/path/to/project');
        $filesystem = new Filesystem($kernel);
        $this->assertEquals('/path/to/project'.DIRECTORY_SEPARATOR.'foo/bar.php', $filesystem->path('foo/bar.php'));
    }

    public function test_path_method_returns_qualified_file_path_when_supplied_with_argument()
    {
        $this->assertEquals($this->filesystem->path('file.php'), $this->filesystem->path().DIRECTORY_SEPARATOR.'file.php');
    }

    public function test_path_method_strips_trailing_directory_separators_from_argument()
    {
        $this->assertEquals($this->filesystem->path('\\/file.php/'), $this->filesystem->path().DIRECTORY_SEPARATOR.'file.php');
    }

    public function test_path_method_returns_expected_value_for_nested_path_arguments()
    {
        $this->assertEquals($this->filesystem->path('directory/file.php'), $this->filesystem->path().DIRECTORY_SEPARATOR.'directory/file.php');
    }

    public function test_path_method_returns_expected_value_regardless_of_trailing_directory_separators_in_argument()
    {
        $this->assertEquals($this->filesystem->path('directory/file.php/'), $this->filesystem->path().DIRECTORY_SEPARATOR.'directory/file.php');
        $this->assertEquals($this->filesystem->path('/directory/file.php/'), $this->filesystem->path().DIRECTORY_SEPARATOR.'directory/file.php');
        $this->assertEquals($this->filesystem->path('\\/directory/file.php/'), $this->filesystem->path().DIRECTORY_SEPARATOR.'directory/file.php');
    }

    public function test_vendor_path()
    {

    }

    public function test_copy()
    {

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
