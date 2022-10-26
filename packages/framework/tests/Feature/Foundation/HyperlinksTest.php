<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Hyperlinks
 */
class HyperlinksTest extends TestCase
{
    protected \Hyde\Foundation\Hyperlinks $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new \Hyde\Foundation\Hyperlinks(HydeKernel::getInstance());
    }

    public function test_image_helper_gets_relative_web_link_to_image_stored_in_site_media_folder()
    {
        $tests = [
            'test.jpg' => 'media/test.jpg',
            'foo' => 'media/foo',
            'http://example.com/test.jpg' => 'http://example.com/test.jpg',
            'https://example.com/test.jpg' => 'https://example.com/test.jpg',
        ];

        foreach ($tests as $input => $expected) {
            $this->assertEquals($this->class->image($input), $expected);
        }
    }

    public function test_image_helper_resolves_paths_for_nested_pages()
    {
        $tests = [
            'test.jpg' => '../media/test.jpg',
            'foo' => '../media/foo',
            'http://example.com/test.jpg' => 'http://example.com/test.jpg',
            'https://example.com/test.jpg' => 'https://example.com/test.jpg',
        ];

        foreach ($tests as $input => $expected) {
            $this->mockCurrentPage('foo/bar');
            $this->assertEquals($this->class->image($input), $expected);
        }
    }

    public function test_image_helper_returns_qualified_absolute_uri_when_requested_and_site_has_base_url()
    {
        $this->assertEquals('http://localhost/media/test.jpg', $this->class->image('test.jpg', true));
    }

    public function test_image_helper_returns_default_relative_path_when_qualified_absolute_uri_is_requested_but_site_has_no_base_url()
    {
        config(['site.url' => null]);
        $this->assertEquals('media/test.jpg', $this->class->image('test.jpg', true));
    }

    public function test_image_helper_returns_input_when_qualified_absolute_uri_is_requested_but_image_is_already_qualified()
    {
        $this->assertEquals('http://localhost/media/test.jpg', $this->class->image('http://localhost/media/test.jpg', true));
    }
}
