<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

class BlogPostFrontMatterIsOptionalTest extends TestCase
{
    public function testBlogPostCanBeCreatedWithoutFrontMatter()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), '# My New Post');

        StaticPageBuilder::handle(MarkdownPost::get('test-post'));

        $this->assertFileExists(Hyde::path('_site/posts/test-post.html'));

        Filesystem::unlink('_posts/test-post.md');
        Filesystem::unlink('_site/posts/test-post.html');
    }

    public function testBlogPostFeedCanBeRenderedWhenPostHasNoFrontMatter()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), '# My New Post');

        // Create a temporary page to test the feed
        copy(Hyde::vendorPath('resources/views/components/blog-post-feed.blade.php'),
            Hyde::path('_pages/feed-test.blade.php')
        );

        StaticPageBuilder::handle(BladePage::get('feed-test'));

        $this->assertFileExists(Hyde::path('_site/feed-test.html'));

        Filesystem::unlink('_posts/test-post.md');
        Filesystem::unlink('_pages/feed-test.blade.php');
        Filesystem::unlink('_site/feed-test.html');
    }
}
