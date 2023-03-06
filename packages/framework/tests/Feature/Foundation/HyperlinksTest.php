<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\Hyperlinks;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Kernel\Hyperlinks
 */
class HyperlinksTest extends TestCase
{
    protected Hyperlinks $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new Hyperlinks(HydeKernel::getInstance());
    }

    public function test_asset_helper_gets_relative_web_link_to_image_stored_in_site_media_folder()
    {
        $tests = [
            'test.jpg' => 'media/test.jpg',
            'foo' => 'media/foo',
            'http://example.com/test.jpg' => 'http://example.com/test.jpg',
            'https://example.com/test.jpg' => 'https://example.com/test.jpg',
        ];

        foreach ($tests as $input => $expected) {
            $this->assertEquals($this->class->asset($input), $expected);
        }
    }

    public function test_asset_helper_resolves_paths_for_nested_pages()
    {
        $tests = [
            'test.jpg' => '../media/test.jpg',
            'foo' => '../media/foo',
            'http://example.com/test.jpg' => 'http://example.com/test.jpg',
            'https://example.com/test.jpg' => 'https://example.com/test.jpg',
        ];

        foreach ($tests as $input => $expected) {
            $this->mockCurrentPage('foo/bar');
            $this->assertEquals($this->class->asset($input), $expected);
        }
    }

    public function test_asset_helper_returns_qualified_absolute_uri_when_requested_and_site_has_base_url()
    {
        $this->assertEquals('http://localhost/media/test.jpg', $this->class->asset('test.jpg', true));
    }

    public function test_asset_helper_returns_default_relative_path_when_qualified_absolute_uri_is_requested_but_site_has_no_base_url()
    {
        config(['hyde.url' => null]);
        $this->assertEquals('media/test.jpg', $this->class->asset('test.jpg', true));
    }

    public function test_asset_helper_returns_input_when_qualified_absolute_uri_is_requested_but_image_is_already_qualified()
    {
        $this->assertEquals('http://localhost/media/test.jpg', $this->class->asset('http://localhost/media/test.jpg', true));
    }

    public function test_asset_helper_uses_configured_media_directory()
    {
        Hyde::setMediaDirectory('_assets');
        $this->assertEquals('assets/test.jpg', $this->class->asset('test.jpg'));
    }

    public function test_media_link_helper()
    {
        $this->assertSame('media/foo', $this->class->mediaLink('foo'));
    }

    public function test_media_link_helper_with_relative_path()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertSame('../media/foo', $this->class->mediaLink('foo'));
    }

    public function test_media_link_helper_uses_configured_media_directory()
    {
        Hyde::setMediaDirectory('_assets');
        $this->assertSame('assets/foo', $this->class->mediaLink('foo'));
    }

    public function test_media_link_helper_with_validation_and_existing_file()
    {
        $this->file('_media/foo');
        $this->assertSame('media/foo', $this->class->mediaLink('foo', true));
    }

    public function test_media_link_helper_with_validation_and_non_existing_file()
    {
        $this->expectException(FileNotFoundException::class);
        $this->class->mediaLink('foo', true);
    }
}
