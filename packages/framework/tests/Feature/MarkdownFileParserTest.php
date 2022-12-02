<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\MarkdownDocument;
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
            '# My New Post

This is a post stub used in the automated tests
',
            str_replace("\r", '', (string) $document->markdown)
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

    public function test_static_parse_shorthand()
    {
        $this->makeTestPost();

        $post = MarkdownFileParser::parse(('_posts/test-post.md'));
        $this->assertEquals('My New Post', $post->matter('title'));
        $this->assertEquals('Mr. Hyde', $post->matter('author'));
        $this->assertEquals('blog', $post->matter('category'));

        $this->assertEquals(
            '# My New Post

This is a post stub used in the automated tests
',
            str_replace("\r", '', (string) $post->markdown)
        );
    }
}
