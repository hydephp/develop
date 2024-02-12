<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Meta;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Metadata\GlobalMetadataBag
 */
class GlobalMetadataBagTest extends TestCase
{
    public function testSiteMetadataAddsConfigDefinedMetadata()
    {
        $this->emptyConfig();

        config(['hyde.meta' => [
            Meta::link('foo', 'bar'),
            Meta::name('foo', 'bar'),
            Meta::property('foo', 'bar'),
            'foo' => 'bar',
            'baz',
        ]]);
        $this->assertEquals([
            'links:foo' => Meta::link('foo', 'bar'),
            'metadata:foo' => Meta::name('foo', 'bar'),
            'properties:foo' => Meta::property('foo', 'bar'),
            'generics:0' => 'bar',
            'generics:1' => 'baz',
        ], GlobalMetadataBag::make()->get());
    }

    public function testSiteMetadataAutomaticallyAddsSitemapWhenEnabled()
    {
        $this->emptyConfig();

        config(['hyde.url' => 'foo']);
        config(['hyde.generate_sitemap' => true]);

        $this->assertEquals('<link rel="sitemap" href="foo/sitemap.xml" type="application/xml" title="Sitemap">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataSitemapUsesConfiguredSiteUrl()
    {
        $this->emptyConfig();

        config(['hyde.url' => 'bar']);
        config(['hyde.generate_sitemap' => true]);

        $this->assertEquals('<link rel="sitemap" href="bar/sitemap.xml" type="application/xml" title="Sitemap">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataAutomaticallyAddsRssFeedWhenEnabled()
    {
        $this->emptyConfig();

        config(['hyde.url' => 'foo']);
        config(['hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertEquals('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataRssFeedUsesConfiguredSiteUrl()
    {
        $this->emptyConfig();

        config(['hyde.url' => 'bar']);
        config(['hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertEquals('<link rel="alternate" href="bar/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataRssFeedUsesConfiguredSiteName()
    {
        $this->emptyConfig();

        config(['hyde.url' => 'foo']);
        config(['hyde.name' => 'Site']);
        config(['hyde.rss.enabled' => true]);
        $config = config('hyde');
        unset($config['rss']['description']);
        config(['hyde' => $config]);
        $this->file('_posts/foo.md');

        $this->assertEquals('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="Site RSS Feed">', GlobalMetadataBag::make()->render());
    }

    public function testSiteMetadataRssFeedUsesConfiguredRssFileName()
    {
        $this->emptyConfig();

        config(['hyde.url' => 'foo']);
        config(['hyde.rss.filename' => 'posts.rss']);
        config(['hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertStringContainsString(
            '<link rel="alternate" href="foo/posts.rss" type="application/rss+xml" title="HydePHP RSS Feed">',
            GlobalMetadataBag::make()->render()
        );
    }

    public function testMetadataExistingInTheCurrentPageIsNotAdded()
    {
        $this->emptyConfig();

        $duplicate = Meta::name('remove', 'me');
        $keep = Meta::name('keep', 'this');

        config(['hyde.meta' => [
            $duplicate,
            $keep,
        ]]);

        $page = new MarkdownPage('foo');
        $page->metadata->add($duplicate);

        Render::share('routeKey', 'foo');
        Render::share('page', $page);

        $this->assertEquals(['metadata:keep' => $keep], GlobalMetadataBag::make()->get());
    }

    public function testMetadataExistingInTheCurrentPageIsNotAddedRegardlessOfItsValue()
    {
        $this->emptyConfig();

        config(['hyde.meta' => [Meta::name('foo', 'bar')]]);

        $page = new MarkdownPage('foo');
        $page->metadata->add(Meta::name('foo', 'baz'));

        Render::share('routeKey', 'foo');
        Render::share('page', $page);

        $this->assertEquals([], GlobalMetadataBag::make()->get());
    }

    protected function emptyConfig(): void
    {
        config(['hyde.url' => null]);
        config(['hyde.meta' => []]);
        config(['hyde.rss.enabled' => false]);
        config(['hyde.generate_sitemap' => false]);
    }
}
