<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Models\MarkdownPost::latest()
 */
class GetLatestMarkdownPostsTest extends TestCase
{
    public function test_markdown_page_get_latest_helper_returns_sorted_markdown_page_collection()
    {
        file_put_contents(Hyde::path('_posts/new.md'), "---\ndate: '2022-01-01 12:00'\n---\n");
        file_put_contents(Hyde::path('_posts/old.md'), "---\ndate: '2021-01-01 12:00'\n---\n");

        $collection = MarkdownPost::getLatestPosts();
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, $collection);

        $this->assertEquals('new', $collection->first()->slug);
        $this->assertEquals('old', $collection->last()->slug);

        unlink(Hyde::path('_posts/new.md'));
        unlink(Hyde::path('_posts/old.md'));
    }
}
