<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Testing\Feature\StaticSiteBuilderPostModuleTest for the compiler test.
 */
class MarkdownPostParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(Hyde::path('_posts/test-post.md'), '---
title: My New Post
category: blog
author: Mr. Hyde
---

# My New Post

This is a post stub used in the automated tests
');
    }

    protected function tearDown(): void
    {
        Filesystem::unlink('_posts/test-post.md');

        parent::tearDown();
    }

    public function testCanParseMarkdownFile()
    {
        $post = MarkdownPost::parse('test-post');
        $this->assertInstanceOf(MarkdownPost::class, $post);
        $this->assertCount(3, $post->matter->toArray());
        $this->assertInstanceOf(FrontMatter::class, $post->matter);
        $this->assertInstanceOf(Markdown::class, $post->markdown);
        $this->assertIsString($post->markdown->body());
        $this->assertIsString($post->identifier);
        $this->assertTrue(strlen((string) $post->markdown) > 32);
        $this->assertTrue(strlen($post->identifier) > 8);
    }

    public function testParsedMarkdownPostContainsValidFrontMatter()
    {
        $post = MarkdownPost::parse('test-post');
        $this->assertEquals('My New Post', $post->data('title'));
        $this->assertEquals('Mr. Hyde', $post->data('author'));
        $this->assertEquals('blog', $post->data('category'));
    }
}
