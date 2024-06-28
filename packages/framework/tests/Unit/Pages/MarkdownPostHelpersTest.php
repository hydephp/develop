<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Pages\MarkdownPost
 */
class MarkdownPostHelpersTest extends TestCase
{
    public function testGetCurrentPagePathReturnsLocalUriPathForPostSlug()
    {
        $post = new MarkdownPost('foo-bar');
        $this->assertSame('posts/foo-bar', $post->getRouteKey());
    }

    public function testGetCanonicalLinkReturnsCanonicalUriPathForPostSlug()
    {
        $this->withSiteUrl();
        $post = new MarkdownPost('foo-bar');
        $this->assertSame('https://example.com/posts/foo-bar.html', $post->getCanonicalUrl());
    }

    public function testGetCanonicalLinkReturnsPrettyUrlWhenEnabled()
    {
        config(['hyde.url' => 'https://example.com', 'hyde.pretty_urls' => true]);
        $post = new MarkdownPost('foo-bar');
        $this->assertSame('https://example.com/posts/foo-bar', $post->getCanonicalUrl());
    }

    public function testGetPostDescriptionReturnsPostDescriptionWhenSetInFrontMatter()
    {
        $post = MarkdownPost::make('foo-bar', ['description' => 'This is a post description']);
        $this->assertSame('This is a post description', $post->description);
    }

    public function testGetPostDescriptionReturnsPostBodyWhenNoDescriptionSetInFrontMatter()
    {
        $post = MarkdownPost::make('foo-bar', [], 'This is a post body');
        $this->assertSame('This is a post body', $post->description);
    }

    public function testDynamicDescriptionIsTruncatedWhenLongerThan128Characters()
    {
        $post = MarkdownPost::make('foo-bar', [], str_repeat('a', 128));
        $this->assertSame(str_repeat('a', 125).'...', $post->description);
    }

    public function testDynamicDescriptionStripsMarkdown()
    {
        $post = MarkdownPost::make('foo-bar', [], '## This is a **bold** description with [a link](https://example.com) and <code>more</code>');
        $this->assertSame('This is a bold description with a link and more', $post->description);
    }
}
