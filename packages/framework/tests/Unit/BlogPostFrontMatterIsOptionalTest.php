<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

class BlogPostFrontMatterIsOptionalTest extends TestCase
{
    public function test_blog_post_can_be_created_without_front_matter()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), '# My New Post');

        new StaticPageBuilder(MarkdownPost::get('test-post'), true);

        $this->assertFileExists(Hyde::path('_site/posts/test-post.html'));

        Hyde::unlink('_posts/test-post.md');
        Hyde::unlink('_site/posts/test-post.html');
    }

    public function test_blog_post_feed_can_be_rendered_when_post_has_no_front_matter()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), '# My New Post');

        // Create a temporary page to test the feed
        copy(Hyde::vendorPath('resources/views/components/blog-post-feed.blade.php'),
            Hyde::path('_pages/feed-test.blade.php')
        );

        new StaticPageBuilder(BladePage::get('feed-test'), true);

        $this->assertFileExists(Hyde::path('_site/feed-test.html'));

        Hyde::unlink('_posts/test-post.md');
        Hyde::unlink('_pages/feed-test.blade.php');
        Hyde::unlink('_site/feed-test.html');
    }
}
