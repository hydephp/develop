<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Models\Support\Site;
use Hyde\Framework\Modules\Metadata\Models\GlobalMetadataBag;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Support\Site
 * @covers \Hyde\Framework\Modules\Metadata\Models\GlobalMetadataBag
 */
class SiteTest extends TestCase
{
    public function testConstruct()
    {
        $site = new Site();

        $this->assertNotNull($site->name);
        $this->assertNotNull($site->language);
        $this->assertNotNull($site->url);

        $this->assertEquals(config('site.name'), $site->name);
        $this->assertEquals(config('site.language'), $site->language);
        $this->assertEquals(config('site.url'), $site->url);

        $this->assertSame(Site::name(), $site->name);
        $this->assertSame(Site::language(), $site->language);
        $this->assertSame(Site::url(), $site->url);
    }

    public function testUrl()
    {
        $this->assertSame('http://localhost', Site::url());

        config(['site.url' => null]);
        $this->assertNull(Site::url());

        config(['site.url' => 'https://example.com']);
        $this->assertSame('https://example.com', Site::url());
    }

    public function testName()
    {
        $this->assertSame('HydePHP', Site::name());

        config(['site.name' => null]);
        $this->assertNull(Site::name());

        config(['site.name' => 'foo']);
        $this->assertSame('foo', Site::name());
    }

    public function testLanguage()
    {
        $this->assertSame('en', Site::language());

        config(['site.language' => null]);
        $this->assertNull(Site::language());

        config(['site.language' => 'foo']);
        $this->assertSame('foo', Site::language());
    }

    public function testMetadata()
    {
        $this->assertEquals(GlobalMetadataBag::make(), Site::metadata());
    }

    public function test_site_metadata_adds_config_defined_metadata()
    {
        $this->resetConfig();

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
        ], Site::metadata()->get());
    }

    public function test_site_metadata_automatically_adds_sitemap_when_enabled()
    {
        $this->resetConfig();

        config(['site.url' => 'foo']);
        config(['site.generate_sitemap' => true]);

        $this->assertEquals('<link rel="sitemap" href="foo/sitemap.xml" type="application/xml" title="Sitemap">', Site::metadata()->render());
    }

    public function test_site_metadata_sitemap_uses_configured_site_url()
    {
        $this->resetConfig();

        config(['site.url' => 'bar']);
        config(['site.generate_sitemap' => true]);

        $this->assertEquals('<link rel="sitemap" href="bar/sitemap.xml" type="application/xml" title="Sitemap">', Site::metadata()->render());
    }

    public function test_site_metadata_automatically_adds_rss_feed_when_enabled()
    {
        $this->resetConfig();

        config(['site.url' => 'foo']);
        config(['hyde.generate_rss_feed' => true]);
        $this->file('_posts/foo.md');

        $this->assertEquals('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', Site::metadata()->render());
    }

    public function test_site_metadata_rss_feed_uses_configured_site_url()
    {
        $this->resetConfig();

        config(['site.url' => 'bar']);
        config(['hyde.generate_rss_feed' => true]);
        $this->file('_posts/foo.md');

        $this->assertEquals('<link rel="alternate" href="bar/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', Site::metadata()->render());
    }

    public function test_site_metadata_rss_feed_uses_configured_site_name()
    {
        $this->resetConfig();

        config(['site.url' => 'foo']);
        config(['site.name' => 'Site']);
        config(['hyde.generate_rss_feed' => true]);
        $this->file('_posts/foo.md');

        $this->assertEquals('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="Site RSS Feed">', Site::metadata()->render());
    }

    public function test_site_metadata_rss_feed_uses_configured_rss_file_name()
    {
        $this->resetConfig();

        config(['site.url' => 'foo']);
        config(['hyde.rss_filename' => 'posts.rss']);
        config(['hyde.generate_rss_feed' => true]);
        $this->file('_posts/foo.md');

        $this->assertStringContainsString(
            '<link rel="alternate" href="foo/posts.rss" type="application/rss+xml" title="HydePHP RSS Feed">',
            Site::metadata()->render()
        );
    }

    protected function resetConfig(): void
    {
        config(['site.url' => null]);
        config(['hyde.meta' => []]);
        config(['hyde.generate_rss_feed' => false]);
        config(['site.generate_sitemap' => false]);
    }
}
