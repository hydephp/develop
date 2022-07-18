<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Models\Pages\MarkdownPost
 */
class MarkdownPostHelpersTest extends TestCase
{
    public function test_get_current_page_path_returns_local_uri_path_for_post_slug()
    {
        $post = new MarkdownPost([], '', '', 'foo-bar');
        $this->assertEquals('posts/foo-bar', $post->getCurrentPagePath());
    }

    public function test_get_canonical_link_returns_canonical_uri_path_for_post_slug()
    {
        config(['site.site_url' => 'https://example.com']);
        $post = new MarkdownPost([], '', '', 'foo-bar');
        $this->assertEquals('https://example.com/posts/foo-bar.html', $post->getCanonicalLink());
    }

    public function test_get_canonical_link_returns_pretty_url_when_enabled()
    {
        config(['site.site_url' => 'https://example.com', 'hyde.pretty_urls' => true]);
        $post = new MarkdownPost([], '', '', 'foo-bar');
        $this->assertEquals('https://example.com/posts/foo-bar', $post->getCanonicalLink());
    }

    public function test_get_post_description_returns_post_description_when_set_in_front_matter()
    {
        $post = new MarkdownPost(['description' => 'This is a post description'], '', '', 'foo-bar');
        $this->assertEquals('This is a post description', $post->getPostDescription());
    }

    public function test_get_post_description_returns_truncated_post_body_when_no_description_set_in_front_matter()
    {
        $post = new MarkdownPost([], 'This is a post body', '', 'foo-bar');
        $this->assertEquals('This is a post body...', $post->getPostDescription());
    }
}
