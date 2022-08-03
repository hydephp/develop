<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\MarkdownDocument;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;
use Hyde\Testing\TestCase;

class MarkdownFileParserTest extends TestCase
{
    protected function makeTestPost(): void
    {
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
        unlink(Hyde::path('_posts/test-post.md'));

        parent::tearDown();
    }

    public function test_can_parse_markdown_file()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), 'Foo bar');

        $document = (new MarkdownFileParser(Hyde::path('_posts/test-post.md')))->get();
        $this->assertInstanceOf(MarkdownDocument::class, $document);

        $this->assertEquals('Foo bar', $document->body);
    }

    public function test_can_parse_markdown_file_with_front_matter()
    {
        $this->makeTestPost();

        $document = (new MarkdownFileParser(Hyde::path('_posts/test-post.md')))->get();
        $this->assertInstanceOf(MarkdownDocument::class, $document);

        $this->assertEquals([
            'title' => 'My New Post',
            'category' => 'blog',
            'author' => 'Mr. Hyde',
        ], $document->matter);

        $this->assertEquals(
            '# My New PostThis is a post stub used in the automated tests',
            str_replace(["\n", "\r"], '', $document->body)
        );
    }

    public function test_parsed_markdown_post_contains_valid_front_matter()
    {
        $this->makeTestPost();

        $post = (new MarkdownFileParser(Hyde::path('_posts/test-post.md')))->get();
        $this->assertEquals('My New Post', $post->matter['title']);
        $this->assertEquals('Mr. Hyde', $post->matter['author']);
        $this->assertEquals('blog', $post->matter['category']);
    }

    public function test_parsed_front_matter_does_not_contain_slug_key()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), "---\nslug: foo\n---\n");

        $post = (new MarkdownFileParser(Hyde::path('_posts/test-post.md')))->get();
        $this->assertArrayNotHasKey('slug', $post->matter);
        $this->assertEquals([], $post->matter);
    }
}
