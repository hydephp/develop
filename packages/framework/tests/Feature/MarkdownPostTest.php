<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Models\Markdown\FrontMatter;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Support\Author;
use Hyde\Framework\Models\Support\DateString;
use Hyde\Framework\Models\Support\Image;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Pages\MarkdownPost
 * @covers \Hyde\Framework\Concerns\Internal\ConstructsPageSchemas
 */
class MarkdownPostTest extends TestCase
{
    public function test_constructor_can_create_a_new_author_instance_from_username_string()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'author' => 'John Doe',
        ]));

        $this->assertInstanceOf(Author::class, $post->author);
        $this->assertEquals('John Doe', $post->author->username);
        $this->assertNull($post->author->name);
        $this->assertNull($post->author->website);
    }

    public function test_constructor_can_create_a_new_author_instance_from_user_array()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'author' => [
                'username' => 'john_doe',
                'name' => 'John Doe',
                'website' => 'https://example.com',
            ],
        ]));

        $this->assertInstanceOf(Author::class, $post->author);
        $this->assertEquals('john_doe', $post->author->username);
        $this->assertEquals('John Doe', $post->author->name);
        $this->assertEquals('https://example.com', $post->author->website);
    }

    public function test_constructor_can_create_a_new_image_instance_from_a_string()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'image' => 'https://example.com/image.jpg',
        ]));

        $this->assertInstanceOf(Image::class, $post->image);
        $this->assertEquals('https://example.com/image.jpg', $post->image->url);
    }

    public function test_constructor_can_create_a_new_image_instance_from_an_array()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'image' => [
                'url' => 'https://example.com/image.jpg',
            ],
        ]));

        $this->assertInstanceOf(Image::class, $post->image);
        $this->assertEquals('https://example.com/image.jpg', $post->image->url);
    }

    public function test_constructor_can_create_a_new_date_string_instance_from_matter()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'date' => '2022-01-01',
        ]));

        $this->assertInstanceOf(DateString::class, $post->date);
        $this->assertEquals('Jan 1st, 2022', $post->date->short);
    }

    public function test_featured_image_can_be_constructed_returns_null_when_no_image_is_set_in_the_page_matter()
    {
        $page = new MarkdownPost();
        $this->assertNull($page->image);
    }

    public function test_featured_image_can_be_constructed_returns_image_object_with_local_path_when_matter_is_string()
    {
        $page = MarkdownPost::make(matter: ['image' => 'foo.png']);
        $image = $page->image;
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals('foo.png', $image->path);
    }

    public function test_featured_image_can_be_constructed_returns_image_object_with_remote_path_when_matter_is_string()
    {
        $page = MarkdownPost::make(matter: ['image' => 'https://example.com/foo.png']);
        $image = $page->image;
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals('https://example.com/foo.png', $image->url);
    }

    public function test_featured_image_can_be_constructed_returns_image_object_with_supplied_data_when_matter_is_array()
    {
        $page = MarkdownPost::make(matter: ['image' => ['path' => 'foo.png', 'title' => 'bar']]);
        $image = $page->image;
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals('foo.png', $image->path);
        $this->assertEquals('bar', $image->title);
    }
}
