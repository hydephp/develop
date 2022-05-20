<?php

namespace Tests\Feature\Concerns;

use Hyde\Framework\Concerns\HasPageMetadata;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Helpers\Meta;
use Tests\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\HasPageMetadata
 * @see \Tests\Unit\HasPageMetadataRssFeedLinkTest
 */
class HasPageMetadataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['hyde.meta' => []]);
        config(['hyde.site_url' => null]);
        config(['hyde.prettyUrls' => false]);
        config(['hyde.generateSitemap' => false]);
    }

    public function test_get_canonical_url_returns_url_for_top_level_page()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };
        config(['hyde.site_url' => 'https://example.com']);

        $this->assertEquals('https://example.com/foo.html', $page->getCanonicalUrl());
    }

    public function test_get_canonical_url_returns_pretty_url_for_top_level_page()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };
        config(['hyde.site_url' => 'https://example.com']);
        config(['hyde.prettyUrls' => true]);

        $this->assertEquals('https://example.com/foo', $page->getCanonicalUrl());
    }

    public function test_get_canonical_url_returns_url_for_nested_page()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';

            public function getCurrentPagePath(): string
            {
                return 'bar/'.$this->slug;
            }
        };
        config(['hyde.site_url' => 'https://example.com']);

        $this->assertEquals('https://example.com/bar/foo.html', $page->getCanonicalUrl());
    }

    public function test_get_canonical_url_returns_url_for_deeply_nested_page()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';

            public function getCurrentPagePath(): string
            {
                return 'bar/baz/'.$this->slug;
            }
        };
        config(['hyde.site_url' => 'https://example.com']);

        $this->assertEquals('https://example.com/bar/baz/foo.html', $page->getCanonicalUrl());
    }

    public function test_can_use_canonical_url_returns_true_when_both_uri_path_and_slug_is_set()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };
        config(['hyde.site_url' => 'https://example.com']);

        $this->assertTrue($page->canUseCanonicalUrl());
    }

    public function test_can_use_canonical_url_returns_false_no_conditions_are_met()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug;
        };

        $this->assertFalse($page->canUseCanonicalUrl());
    }

    public function test_can_use_canonical_url_returns_false_when_only_one_condition_is_met()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug;
        };
        config(['hyde.site_url' => 'https://example.com']);

        $this->assertFalse($page->canUseCanonicalUrl());

        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };
        config(['hyde.site_url' => null]);

        $this->assertFalse($page->canUseCanonicalUrl());
    }

    public function test_render_page_metadata_returns_string_with_merged_metadata()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };
        config(['hyde.site_url' => 'https://example.com']);

        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);

        $this->assertEquals(
            '<meta name="foo" content="bar">'."\n".
            '<link rel="canonical" href="https://example.com/foo.html" />',
            $page->renderPageMetadata()
        );
    }

    public function test_render_page_metadata_only_adds_canonical_if_conditions_are_met()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };

        $this->assertEquals(
            '',
            $page->renderPageMetadata()
        );
    }

    public function test_get_dynamic_metadata_only_adds_canonical_if_conditions_are_met()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };

        $this->assertEquals(
            [],
            $page->getDynamicMetadata()
        );
    }

    public function test_get_dynamic_metadata_adds_canonical_url_when_conditions_are_met()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;

            public string $slug = 'foo';
        };
        config(['hyde.site_url' => 'https://example.com']);

        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);

        $this->assertEquals(['<link rel="canonical" href="https://example.com/foo.html" />'],
            $page->getDynamicMetadata()
        );
    }

    public function test_get_dynamic_metadata_adds_sitemap_link_when_conditions_are_met()
    {
        $page = new class extends AbstractPage
        {
            use HasPageMetadata;
        };
        config(['hyde.site_url' => 'https://example.com']);
        config(['hyde.generateSitemap' => true]);

        $this->assertEquals(['<link rel="sitemap" type="application/xml" title="Sitemap" href="https://example.com/sitemap.xml" />'],
            $page->getDynamicMetadata()
        );
    }
}
