<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Testing\UnitTestCase;

class MarkdownFileParserTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    public function testCanParseMarkdownFile()
    {
        $this->mockFilesystem(['get' => 'Foo bar']);

        $document = MarkdownFileParser::parse('_posts/test-post.md');

        $this->assertInstanceOf(MarkdownDocument::class, $document);
        $this->assertEquals('Foo bar', $document->markdown);
        $this->assertSame('Foo bar', $document->markdown->body());
    }

    public function testCanParseMarkdownFileWithFrontMatter()
    {
        $this->mockFilesystem(['get' => <<<'MD'
            ---
            title: My New Post
            category: blog
            author: Mr. Hyde
            ---

            # My New Post

            This is a post stub used in the automated tests
            MD]);

        $document = MarkdownFileParser::parse('_posts/test-post.md');

        $this->assertInstanceOf(MarkdownDocument::class, $document);

        $this->assertEquals(FrontMatter::fromArray([
            'title' => 'My New Post',
            'category' => 'blog',
            'author' => 'Mr. Hyde',
        ]), $document->matter);

        $this->assertSame(
            <<<'MARKDOWN'
            # My New Post

            This is a post stub used in the automated tests
            MARKDOWN,
            (string) $document->markdown
        );
    }

    public function testParsedMarkdownPostContainsValidFrontMatter()
    {
        $this->mockFilesystem(['get' => <<<'MD'
            ---
            title: My New Post
            category: blog
            author: Mr. Hyde
            ---

            # My New Post

            This is a post stub used in the automated tests
            MD]);

        $post = MarkdownFileParser::parse('_posts/test-post.md');

        $this->assertSame('My New Post', $post->matter('title'));
        $this->assertSame('Mr. Hyde', $post->matter('author'));
        $this->assertSame('blog', $post->matter('category'));
    }

    public function testCanParseMarkdownFileWithFrontMatterAndNoMarkdownBody()
    {
        $this->mockFilesystem(['get' => "---\nfoo: bar\n---"]);

        $document = MarkdownFileParser::parse('_posts/test-post.md');

        $this->assertInstanceOf(MarkdownDocument::class, $document);
        $this->assertEquals('', $document->markdown);
        $this->assertSame('', $document->markdown->body());

        $this->assertEquals(FrontMatter::fromArray([
            'foo' => 'bar',
        ]), $document->matter);
    }
}
