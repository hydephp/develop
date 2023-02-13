<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;

class BlogPostFrontMatterIsOptionalTest extends TestCase
{
    public function test_blog_post_can_be_created_without_front_matter()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), '# My New Post');

        Artisan::call('rebuild _posts/test-post.md');

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

        Artisan::call('rebuild _pages/feed-test.blade.php');

        $this->assertFileExists(Hyde::path('_site/feed-test.html'));

        Hyde::unlink('_posts/test-post.md');
        Hyde::unlink('_pages/feed-test.blade.php');
        Hyde::unlink('_site/feed-test.html');
    }
}
