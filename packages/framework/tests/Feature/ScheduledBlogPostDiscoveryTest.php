<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\BuildWarnings;
use Hyde\Support\Models\DateString;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Exceptions\BuildWarning;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

/**
 * Tests that blog posts dated in the future are skipped during auto-discovery.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(MarkdownPost::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HydeCoreExtension::class)]
class ScheduledBlogPostDiscoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->forgetInstance(BuildWarnings::class);
    }

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
        $this->assertEmpty(BuildWarnings::getWarnings());
    }

    public function testPostWithoutADateIsDiscovered()
    {
        $this->markdown('_posts/undated.md');

        Hyde::boot();

        $this->assertTrue(Hyde::routes()->has('posts/undated'));
        $this->assertEmpty(BuildWarnings::getWarnings());
    }

    public function testPostWithAFutureDatePrefixIsNotDiscovered()
    {
        $this->markdown('_posts/2100-01-01-scheduled.md');

        Hyde::boot();

        $this->assertFalse(Hyde::routes()->has('posts/scheduled'));
    }

    public function testSkippedPostIsReportedAsABuildWarning()
    {
        $this->markdown('_posts/scheduled.md', matter: ['date' => '2100-01-01']);

        Hyde::boot();

        $date = (new DateString('2100-01-01'))->datetime;

        $this->assertSame([
            'Skipping blog post "_posts/scheduled.md" as its date is set in the future ('.$date.'). '.
            'Since Hyde is a static site generator, it will be included in the first site build made after that date.',
        ], array_map(fn (BuildWarning $warning): string => $warning->getMessage(), BuildWarnings::getWarnings()));
    }
}
