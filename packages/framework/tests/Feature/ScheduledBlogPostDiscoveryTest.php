<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPost;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

/**
 * Tests publication filtering and realtime preview behavior for scheduled posts.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(MarkdownPost::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HydeCoreExtension::class)]
class ScheduledBlogPostDiscoveryTest extends TestCase
{
    public function testPostDatedInTheFutureIsNotDiscovered()
    {
        $this->markdown('_posts/published.md', matter: ['date' => '2020-01-01']);
        $this->markdown('_posts/scheduled.md', matter: ['date' => '2100-01-01']);

        Hyde::boot();

        $pages = Hyde::pages()->getPages(MarkdownPost::class);

        $this->assertTrue($pages->has('_posts/published.md'));
        $this->assertFalse($pages->has('_posts/scheduled.md'));
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
}
