<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Pages\MarkdownPost
 */
class MarkdownPostHelpersTest extends TestCase
{
    public function test_get_current_page_path_returns_local_uri_path_for_post_slug()
    {
        $post = new MarkdownPost('foo-bar');
        $this->assertEquals('posts/foo-bar', $post->getRouteKey());
    }

    public function test_get_canonical_link_returns_canonical_uri_path_for_post_slug()
    {
        config(['site.url' => 'https://example.com']);
        $post = new MarkdownPost('foo-bar');
        $this->assertEquals('https://example.com/posts/foo-bar.html', $post->canonicalUrl);
    }

    public function test_get_canonical_link_returns_pretty_url_when_enabled()
    {
        config(['site.url' => 'https://example.com', 'site.pretty_urls' => true]);
        $post = new MarkdownPost('foo-bar');
        $this->assertEquals('https://example.com/posts/foo-bar', $post->canonicalUrl);
    }

    public function test_get_post_description_returns_post_description_when_set_in_front_matter()
    {
        $post = MarkdownPost::make('foo-bar', ['description' => 'This is a post description']);
        $this->assertEquals('This is a post description', $post->description);
    }

    public function test_get_post_description_returns_post_body_when_no_description_set_in_front_matter()
    {
        $post = MarkdownPost::make('foo-bar', [], 'This is a post body');
        $this->assertEquals('This is a post body', $post->description);
    }

    public function test_dynamic_description_is_truncated_when_longer_than_128_characters()
    {
        $post = MarkdownPost::make('foo-bar', [], str_repeat('a', 128));
        $this->assertEquals(str_repeat('a', 125).'...', $post->description);
    }
}
