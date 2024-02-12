<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\DateString;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\MarkdownPost
 * @covers \Hyde\Framework\Factories\Concerns\HasFactory
 */
class MarkdownPostTest extends TestCase
{
    public function testConstructorCanCreateANewAuthorInstanceFromUsernameString()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'author' => 'John Doe',
        ]));

        $this->assertInstanceOf(PostAuthor::class, $post->author);
        $this->assertEquals('John Doe', $post->author->username);
        $this->assertNull($post->author->name);
        $this->assertNull($post->author->website);
    }

    public function testConstructorCanCreateANewAuthorInstanceFromUserArray()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'author' => [
                'username' => 'john_doe',
                'name' => 'John Doe',
                'website' => 'https://example.com',
            ],
        ]));

        $this->assertInstanceOf(PostAuthor::class, $post->author);
        $this->assertEquals('john_doe', $post->author->username);
        $this->assertEquals('John Doe', $post->author->name);
        $this->assertEquals('https://example.com', $post->author->website);
    }

    public function testConstructorCanCreateANewImageInstanceFromAString()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'image' => 'https://example.com/image.jpg',
        ]));

        $this->assertInstanceOf(FeaturedImage::class, $post->image);
        $this->assertEquals('https://example.com/image.jpg', $post->image->getSource());
    }

    public function testConstructorCanCreateANewImageInstanceFromAnArray()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'image' => [
                'source' => 'https://example.com/image.jpg',
            ],
        ]));

        $this->assertInstanceOf(FeaturedImage::class, $post->image);
        $this->assertEquals('https://example.com/image.jpg', $post->image->getSource());
    }

    public function testConstructorCanCreateANewDateStringInstanceFromMatter()
    {
        $post = new MarkdownPost(matter: FrontMatter::fromArray([
            'date' => '2022-01-01',
        ]));

        $this->assertInstanceOf(DateString::class, $post->date);
        $this->assertEquals('Jan 1st, 2022', $post->date->short);
    }

    public function testFeaturedImageCanBeConstructedReturnsNullWhenNoImageIsSetInThePageMatter()
    {
        $page = new MarkdownPost();
        $this->assertNull($page->image);
    }

    public function testFeaturedImageCanBeConstructedReturnsImageObjectWithLocalPathWhenMatterIsString()
    {
        $page = MarkdownPost::make(matter: ['image' => 'foo.png']);
        $image = $page->image;
        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertEquals('media/foo.png', $image->getSource());
    }

    public function testFeaturedImageCanBeConstructedReturnsImageObjectWithRemotePathWhenMatterIsString()
    {
        $page = MarkdownPost::make(matter: ['image' => 'https://example.com/foo.png']);
        $image = $page->image;
        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertEquals('https://example.com/foo.png', $image->getSource());
    }

    public function testFeaturedImageCanBeConstructedReturnsImageObjectWithSuppliedDataWhenMatterIsArray()
    {
        $page = MarkdownPost::make(matter: ['image' => ['source' => 'foo.png', 'titleText' => 'bar']]);
        $image = $page->image;
        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertEquals('media/foo.png', $image->getSource());
        $this->assertEquals('bar', $image->getTitleText());
    }
}
