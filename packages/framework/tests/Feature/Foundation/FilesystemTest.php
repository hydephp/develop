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

    public function test_path()
    {

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
