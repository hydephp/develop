<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Markdown\FrontMatter;
use Hyde\Framework\Models\Markdown\MarkdownDocument;
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

        $document = (new MarkdownFileParser(('_posts/test-post.md')))->get();
        $this->assertInstanceOf(MarkdownDocument::class, $document);

        $this->assertEquals('Foo bar', $document->markdown);
    }

    public function test_can_parse_markdown_file_with_front_matter()
    {
        $this->makeTestPost();

        $document = (new MarkdownFileParser(('_posts/test-post.md')))->get();
        $this->assertInstanceOf(MarkdownDocument::class, $document);

        $this->assertEquals(FrontMatter::fromArray([
            'title' => 'My New Post',
            'category' => 'blog',
            'author' => 'Mr. Hyde',
        ]), $document->matter);

        $this->assertEquals(
            '# My New PostThis is a post stub used in the automated tests',
            str_replace(["\n", "\r"], '', $document->markdown)
        );
    }

    public function test_parsed_markdown_post_contains_valid_front_matter()
    {
        $this->makeTestPost();

        $post = (new MarkdownFileParser(('_posts/test-post.md')))->get();
        $this->assertEquals('My New Post', $post->matter('title'));
        $this->assertEquals('Mr. Hyde', $post->matter('author'));
        $this->assertEquals('blog', $post->matter('category'));
    }

    public function test_parsed_front_matter_does_not_contain_slug_key()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), "---\nslug: foo\n---\n");

        $post = (new MarkdownFileParser(('_posts/test-post.md')))->get();
        $this->assertNull($post->matter('slug'));
        $this->assertEquals(FrontMatter::fromArray([]), $post->matter);
    }

    public function test_static_parse_shorthand()
    {
        $this->makeTestPost();

        $post = MarkdownFileParser::parse(('_posts/test-post.md'));
        $this->assertEquals('My New Post', $post->matter('title'));
        $this->assertEquals('Mr. Hyde', $post->matter('author'));
        $this->assertEquals('blog', $post->matter('category'));

        $this->assertEquals(
            '# My New PostThis is a post stub used in the automated tests',
            str_replace(["\n", "\r"], '', $post->markdown)
        );
    }
}
