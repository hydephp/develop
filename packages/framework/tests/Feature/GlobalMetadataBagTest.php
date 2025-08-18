<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Meta;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Metadata\GlobalMetadataBag::class)]
class GlobalMetadataBagTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withEmptyConfig();
    }

    public function testSiteMetadataAddsConfigDefinedMetadata()
    {
        config(['hyde.meta' => [
            Meta::link('foo', 'bar'),
            Meta::name('foo', 'bar'),
            Meta::property('foo', 'bar'),
            'foo' => 'bar',
            'baz',
        ]]);

        $expected = [
            'links:foo' => Meta::link('foo', 'bar'),
            'metadata:foo' => Meta::name('foo', 'bar'),
            'properties:foo' => Meta::property('foo', 'bar'),
            'generics:0' => 'bar',
            'generics:1' => 'baz',
        ];

        $actual = GlobalMetadataBag::make()->get();

        $this->assertEquals($expected, $actual);
        $this->assertSame(array_keys($expected), array_keys($actual));
    }

    public function testSiteMetadataAutomaticallyAddsSitemapWhenEnabled()
    {
        config(['hyde.url' => 'foo', 'hyde.generate_sitemap' => true]);

        $this->assertSame('<link rel="sitemap" href="foo/sitemap.xml" type="application/xml" title="Sitemap">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataSitemapUsesConfiguredSiteUrl()
    {
        config(['hyde.url' => 'bar', 'hyde.generate_sitemap' => true]);

        $this->assertSame('<link rel="sitemap" href="bar/sitemap.xml" type="application/xml" title="Sitemap">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataAutomaticallyAddsRssFeedWhenEnabled()
    {
        config(['hyde.url' => 'foo', 'hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertSame('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataRssFeedUsesConfiguredSiteUrl()
    {
        config(['hyde.url' => 'bar', 'hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertSame('<link rel="alternate" href="bar/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataRssFeedUsesConfiguredSiteName()
    {
        config(['hyde.url' => 'foo', 'hyde.name' => 'Site', 'hyde.rss.enabled' => true]);
        $config = config('hyde');
        unset($config['rss']['description']);
        config(['hyde' => $config]);
        $this->file('_posts/foo.md');

        $this->assertSame('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="Site RSS Feed">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataRssFeedUsesConfiguredRssFileName()
    {
        config(['hyde.url' => 'foo', 'hyde.rss.filename' => 'posts.rss', 'hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertStringContainsString(
            '<link rel="alternate" href="foo/posts.rss" type="application/rss+xml" title="HydePHP RSS Feed">',
            GlobalMetadataBag::make()->render()
        );
    }

    public function testMetadataExistingInTheCurrentPageIsNotAdded()
    {
        $duplicate = Meta::name('remove', 'me');
        $keep = Meta::name('keep', 'this');

        config(['hyde.meta' => [$duplicate, $keep]]);

        $page = new MarkdownPage('foo');
        $page->metadata->add($duplicate);

        Render::share('routeKey', 'foo');
        Render::share('page', $page);

        $this->assertSame(['metadata:keep' => $keep], GlobalMetadataBag::make()->get());
    }

    public function testMetadataExistingInTheCurrentPageIsNotAddedRegardlessOfItsValue()
    {
        config(['hyde.meta' => [Meta::name('foo', 'bar')]]);

        $page = new MarkdownPage('foo');
        $page->metadata->add(Meta::name('foo', 'baz'));

        Render::share('routeKey', 'foo');
        Render::share('page', $page);

        $this->assertSame([], GlobalMetadataBag::make()->get());
    }

    protected function withEmptyConfig(): void
    {
        config([
            'hyde.url' => null,
            'hyde.meta' => [],
            'hyde.rss.enabled' => false,
            'hyde.generate_sitemap' => false,
        ]);
    }
}
