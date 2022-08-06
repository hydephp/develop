<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Actions\Constructors\ConfiguresFeaturedImageForPost;
use Hyde\Framework\Models\Image;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\Constructors\ConfiguresFeaturedImageForPost
 */
class ConfiguresFeaturedImageForPostTest extends TestCase
{
    // test returns null when no image is set in the page matter
    public function test_action_returns_null_when_no_image_is_set_in_the_page_matter()
    {
        $page = new MarkdownPost();
        $this->assertNull(ConfiguresFeaturedImageForPost::run($page));
    }

    // test returns null when image is set in the page matter but is not a string or array
    public function test_action_returns_null_when_image_is_set_in_the_page_matter_but_is_not_a_string_or_array()
    {
        $page = MarkdownPost::make(matter: ['image' => 123]);
        $this->assertNull(ConfiguresFeaturedImageForPost::run($page));
    }

    // test returns image object with local path when matter is string
    public function test_action_returns_image_object_with_local_path_when_matter_is_string()
    {
        $page = MarkdownPost::make(matter: ['image' => 'foo.png']);
        $image = ConfiguresFeaturedImageForPost::run($page);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals('foo.png', $image->path);
    }

    // test returns image object with remote path when matter is string
    public function test_action_returns_image_object_with_remote_path_when_matter_is_string()
    {
        $page = MarkdownPost::make(matter: ['image' => 'https://example.com/foo.png']);
        $image = ConfiguresFeaturedImageForPost::run($page);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals('https://example.com/foo.png', $image->uri);
    }

    // test returns image object with supplied data when matter is array
    public function test_action_returns_image_object_with_supplied_data_when_matter_is_array()
    {
        $page = MarkdownPost::make(matter: ['image' => ['path' => 'foo.png', 'title' => 'bar']]);
        $image = ConfiguresFeaturedImageForPost::run($page);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals('foo.png', $image->path);
        $this->assertEquals('bar', $image->title);
    }

    public function test_action_requires_markdown_post_object_as_argument()
    {
        $this->expectException(\TypeError::class);
        ConfiguresFeaturedImageForPost::run(new MarkdownPage());
    }
}
