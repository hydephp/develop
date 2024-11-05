<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Blogging\DatePrefixHelper;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

class BlogPostDatePrefixTest extends TestCase
{
    public function testCanDetectDatePrefix()
    {
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-my-post'));
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-10-30-my-post'));
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('my-post'));
    }

    public function testCanExtractDateFromPrefix()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-my-post');
        $this->assertNotNull($date);
        $this->assertEquals('2024-11-05', $date->format('Y-m-d'));

        $date = DatePrefixHelper::extractDate('2024-11-05-10-30-my-post');
        $this->assertNotNull($date);
        $this->assertEquals('2024-11-05 10:30', $date->format('Y-m-d H:i'));
    }

    public function testCanGetDateFromBlogPostFilename()
    {
        $this->file('_posts/2024-11-05-my-post.md', '# Hello World');
        $post = MarkdownPost::parse('2024-11-05-my-post');

        $this->assertInstanceOf(\DateTimeInterface::class, $post->date);
    }

    public function testDatePrefixIsStrippedFromRouteKey()
    {
        $this->file('_posts/2024-11-05-my-post.md', '# Hello World');
        $post = MarkdownPost::parse('2024-11-05-my-post');
        
        $this->assertEquals('posts/my-post', $post->getRouteKey());
    }

    public function testDateFromPrefixIsUsedWhenNoFrontMatterDate()
    {
        $this->file('_posts/2024-11-05-my-post.md', '# Hello World');
        $post = MarkdownPost::parse('2024-11-05-my-post');
        
        $this->assertEquals('2024-11-05 00:00', $post->date->format('Y-m-d H:i'));
    }

    public function testFrontMatterDateTakesPrecedenceOverPrefix()
    {
        $this->file('_posts/2024-11-05-my-post.md', <<<MD
        ---
        date: 2024-12-25
        ---
        # Hello World
        MD);
        
        $post = MarkdownPost::parse('2024-11-05-my-post');
        $this->assertEquals('2024-12-25', $post->date->format('Y-m-d'));
    }
}
