<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\BuildWarnings;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

/**
 * Tests publication filtering and realtime preview behavior for unpublished posts,
 * meaning both drafts and posts scheduled by way of a future date.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(MarkdownPost::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HydeCoreExtension::class)]
class UnpublishedBlogPostDiscoveryTest extends TestCase
{
    public function testPostDatedInTheFutureIsNotDiscovered()
    {
        app()->forgetInstance(BuildWarnings::class);

        $this->markdown('_posts/published.md', matter: ['date' => '2020-01-01']);
        $this->markdown('_posts/scheduled.md', matter: ['date' => '2100-01-01']);

        Hyde::boot();

        $pages = Hyde::pages()->getPages(MarkdownPost::class);

        $this->assertTrue($pages->has('_posts/published.md'));
        $this->assertFalse($pages->has('_posts/scheduled.md'));

        // Scheduled posts are silently and intentionally withheld; they are not build problems.
        $this->assertEmpty(BuildWarnings::getWarnings());
    }

    public function testPostDatedInTheFutureDoesNotGetARoute()
    {
        $this->markdown('_posts/scheduled.md', matter: ['date' => '2100-01-01']);

        Hyde::boot();

        $this->assertFalse(Hyde::routes()->has('posts/scheduled'));
    }

    public function testPostDatedInTheFutureIsNotIncludedInTheRssFeed()
    {
        $this->markdown('_posts/published.md', matter: ['date' => '2020-01-01']);
        $this->markdown('_posts/scheduled.md', matter: ['date' => '2100-01-01']);

        Hyde::boot();

        $feed = (new RssFeedGenerator())->generate()->getXml();

        // Assert on the item titles, as the item links are only present when the site URL is configured.
        $this->assertStringContainsString('<title>Published</title>', $feed);
        $this->assertStringNotContainsString('<title>Scheduled</title>', $feed);
    }

    public function testPostDatedInThePastIsDiscovered()
    {
        $this->markdown('_posts/published.md', matter: ['date' => '2020-01-01']);

        Hyde::boot();

        $this->assertTrue(Hyde::routes()->has('posts/published'));
    }

    public function testPostWithoutADateIsDiscovered()
    {
        $this->markdown('_posts/undated.md');

        Hyde::boot();

        $this->assertTrue(Hyde::routes()->has('posts/undated'));
    }

    public function testPostWithAFutureDatePrefixIsNotDiscovered()
    {
        $this->markdown('_posts/2100-01-01-scheduled.md');

        Hyde::boot();

        $this->assertFalse(Hyde::routes()->has('posts/scheduled'));
    }

    public function testPostDatedInTheFutureIsDiscoveredWhenServing()
    {
        config(['hyde.server.running' => true]);

        $this->markdown('_posts/scheduled.md', matter: ['date' => '2100-01-01']);

        Hyde::boot();

        $this->assertTrue(Hyde::pages()->getPages(MarkdownPost::class)->has('_posts/scheduled.md'));
        $this->assertTrue(Hyde::routes()->has('posts/scheduled'));
    }

    public function testPostWithAFutureDatePrefixIsDiscoveredWhenServing()
    {
        config(['hyde.server.running' => true]);

        $this->markdown('_posts/2100-01-01-scheduled.md');

        Hyde::boot();

        $this->assertTrue(Hyde::routes()->has('posts/scheduled'));
    }

    public function testScheduledPostIsIncludedInTheRssFeedWhenServing()
    {
        config(['hyde.server.running' => true]);

        $this->markdown('_posts/scheduled.md', matter: ['date' => '2100-01-01']);

        Hyde::boot();

        $this->assertStringContainsString('<title>Scheduled</title>', (new RssFeedGenerator())->generate()->getXml());
    }

    public function testDraftPostIsNotDiscovered()
    {
        app()->forgetInstance(BuildWarnings::class);

        $this->markdown('_posts/published.md');
        $this->markdown('_posts/draft.md', matter: ['draft' => true]);

        Hyde::boot();

        $pages = Hyde::pages()->getPages(MarkdownPost::class);

        $this->assertTrue($pages->has('_posts/published.md'));
        $this->assertFalse($pages->has('_posts/draft.md'));

        // Drafts are silently and intentionally withheld; they are not build problems.
        $this->assertEmpty(BuildWarnings::getWarnings());
    }

    public function testDraftPostDoesNotGetARoute()
    {
        $this->markdown('_posts/draft.md', matter: ['draft' => true]);

        Hyde::boot();

        $this->assertFalse(Hyde::routes()->has('posts/draft'));
    }

    public function testDraftPostIsNotIncludedInTheRssFeed()
    {
        $this->markdown('_posts/published.md');
        $this->markdown('_posts/draft.md', matter: ['draft' => true]);

        Hyde::boot();

        $feed = (new RssFeedGenerator())->generate()->getXml();

        $this->assertStringContainsString('<title>Published</title>', $feed);
        $this->assertStringNotContainsString('<title>Draft</title>', $feed);
    }

    public function testDraftPostWithAPastDateIsNotDiscovered()
    {
        $this->markdown('_posts/draft.md', matter: ['date' => '2020-01-01', 'draft' => true]);

        Hyde::boot();

        // The explicit draft status is stronger than the date, so the post stays withheld.
        $this->assertFalse(Hyde::routes()->has('posts/draft'));
    }

    public function testPostWithDraftSetToFalseIsDiscovered()
    {
        $this->markdown('_posts/published.md', matter: ['draft' => false]);

        Hyde::boot();

        $this->assertTrue(Hyde::routes()->has('posts/published'));
    }

    public function testDraftPostIsDiscoveredWhenServing()
    {
        config(['hyde.server.running' => true]);

        $this->markdown('_posts/draft.md', matter: ['draft' => true]);

        Hyde::boot();

        $this->assertTrue(Hyde::pages()->getPages(MarkdownPost::class)->has('_posts/draft.md'));
        $this->assertTrue(Hyde::routes()->has('posts/draft'));
    }

    public function testDraftPostIsIncludedInTheRssFeedWhenServing()
    {
        config(['hyde.server.running' => true]);

        $this->markdown('_posts/draft.md', matter: ['draft' => true]);

        Hyde::boot();

        $this->assertStringContainsString('<title>Draft</title>', (new RssFeedGenerator())->generate()->getXml());
    }
}
