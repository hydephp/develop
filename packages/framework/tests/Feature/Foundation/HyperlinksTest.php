<?php

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Framework\Foundation\Hyperlinks;
use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Foundation\Hyperlinks
 */
class HyperlinksTest extends TestCase
{
    protected Hyperlinks $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new Hyperlinks(HydeKernel::getInstance());
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
}
