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
        $this->assertEquals('posts/foo-bar', $post->getRouteKey());
    }

    public function testGetCanonicalLinkReturnsCanonicalUriPathForPostSlug()
    {
        config(['hyde.url' => 'https://example.com']);
        $post = new MarkdownPost('foo-bar');
        $this->assertEquals('https://example.com/posts/foo-bar.html', $post->getCanonicalUrl());
    }

    public function testGetCanonicalLinkReturnsPrettyUrlWhenEnabled()
    {
        config(['hyde.url' => 'https://example.com', 'hyde.pretty_urls' => true]);
        $post = new MarkdownPost('foo-bar');
        $this->assertEquals('https://example.com/posts/foo-bar', $post->getCanonicalUrl());
    }

    public function testGetPostDescriptionReturnsPostDescriptionWhenSetInFrontMatter()
    {
        $post = MarkdownPost::make('foo-bar', ['description' => 'This is a post description']);
        $this->assertEquals('This is a post description', $post->description);
    }

    public function testGetPostDescriptionReturnsPostBodyWhenNoDescriptionSetInFrontMatter()
    {
        $post = MarkdownPost::make('foo-bar', [], 'This is a post body');
        $this->assertEquals('This is a post body', $post->description);
    }

    public function testDynamicDescriptionIsTruncatedWhenLongerThan128Characters()
    {
        $post = MarkdownPost::make('foo-bar', [], str_repeat('a', 128));
        $this->assertEquals(str_repeat('a', 125).'...', $post->description);
    }
}
