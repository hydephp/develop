<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Pages\MarkdownPost::latest()
 */
class GetLatestMarkdownPostsTest extends TestCase
{
    public function testMarkdownPageGetLatestHelperReturnsSortedMarkdownPageCollection()
    {
        $this->file('_posts/new.md', "---\ndate: '2022-01-01 12:00'\n---\n");
        $this->file('_posts/old.md', "---\ndate: '2021-01-01 12:00'\n---\n");

        $collection = MarkdownPost::getLatestPosts();

        $this->assertCount(2, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, $collection);

        $this->assertSame('new', $collection->first()->identifier);
        $this->assertSame('old', $collection->last()->identifier);
    }
}
