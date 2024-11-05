<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Support\Models\DateString;
use Hyde\Framework\Features\Blogging\DatePrefixHelper;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

class BlogPostDatePrefixHelperTest extends TestCase
{
    public function testCanDetectDatePrefix()
    {
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-my-post.md'));
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-10-30-my-post.md'));
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('my-post.md'));
    }

    public function testCanExtractDateFromPrefix()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-my-post.md');
        $this->assertNotNull($date);
        $this->assertSame('2024-11-05', $date->format('Y-m-d'));

        $date = DatePrefixHelper::extractDate('2024-11-05-10-30-my-post.md');
        $this->assertNotNull($date);
        $this->assertSame('2024-11-05 10:30', $date->format('Y-m-d H:i'));
    }

    public function testCanGetDateFromBlogPostFilename()
    {
        $this->file('_posts/2024-11-05-my-post.md', '# Hello World');
        $post = MarkdownPost::parse('2024-11-05-my-post');

        $this->assertInstanceOf(DateString::class, $post->date);
        $this->assertSame('2024-11-05 00:00', $post->date->string);
    }

    public function testCanGetDateFromBlogPostFilenameWithTime()
    {
        $this->file('_posts/2024-11-05-10-30-my-post.md', '# Hello World');
        $post = MarkdownPost::parse('2024-11-05-10-30-my-post');

        $this->assertInstanceOf(DateString::class, $post->date);
        $this->assertSame('2024-11-05 10:30', $post->date->string);
    }

    public function testDatePrefixIsStrippedFromRouteKey()
    {
        $this->file('_posts/2024-11-05-my-post.md', '# Hello World');
        $post = MarkdownPost::parse('2024-11-05-my-post');

        $this->assertSame('posts/my-post', $post->getRouteKey());
    }

    public function testDateFromPrefixIsUsedWhenNoFrontMatterDate()
    {
        $this->file('_posts/2024-11-05-my-post.md', '# Hello World');
        $post = MarkdownPost::parse('2024-11-05-my-post');

        $this->assertSame('2024-11-05 00:00', $post->date->string);
    }

    public function testFrontMatterDateTakesPrecedenceOverPrefix()
    {
        $this->file('_posts/2024-11-05-my-post.md', <<<'MD'
        ---
        date: "2024-12-25"
        ---
        # Hello World
        MD);

        $post = MarkdownPost::parse('2024-11-05-my-post');
        $this->assertSame('2024-12-25', $post->date->string);
    }
}
