<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * This tests ensures all metadata is rendered correctly in the compiled pages.
 * Please see the MetadataTest class which tests the construction of the metadata;
 * as this test does not cover all configuration cases and possible formatting options.
 *
 * @covers \Hyde\Framework\Models\Metadata\Metadata
 */
class MetadataViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => 'http://localhost']);
    }

    protected function build(?string $page = null): void
    {
        if ($page) {
            $this->artisan("rebuild $page");
        } else {
            $this->artisan('build');
        }
    }

    protected function assertSee(string $page, string|array $text): string|array
    {
        if (is_array($text)) {
            foreach ($text as $string) {
                $this->assertSee($page, $string);
            }

            return $text;
        }

        $this->assertStringContainsString($text,
            file_get_contents(Hyde::path("_site/$page.html")),
            "Failed asserting that the page '$page' contains the text '$text'");

        return $text;
    }

    protected function getDefaultTags(): array
    {
        return [
            '<meta charset="utf-8">',
            '<meta name="viewport" content="width=device-width, initial-scale=1">',
            '<meta id="meta-color-scheme" name="color-scheme" content="light">',
            '<link rel="stylesheet" href="media/app.css">',
            '<link rel="sitemap" href="http://localhost/sitemap.xml" type="application/xml" title="Sitemap">',
            '<link rel="alternate" href="http://localhost/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">',
            '<meta name="generator" content="HydePHP dev-master">',
            '<meta property="og:site_name" content="HydePHP">',
        ];
    }

    public function test_metadata_tags_in_empty_markdown_page()
    {
        $this->markdown('_pages/test.md');
        $this->build('_pages/test.md');

        $this->assertSee('test', array_merge($this->getDefaultTags(), [
            '<title>HydePHP - Test</title>',
            '<meta name="twitter:title" content="HydePHP - Test">',
            '<meta property="og:title" content="HydePHP - Test">'
        ]));
    }
}
