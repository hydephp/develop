<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * This tests ensures all metadata is rendered correctly in the compiled pages.
 * Please see the MetadataTest class which tests the construction of the metadata;
 * as this test does not cover all configuration cases and possible formatting options.
 *
 * This test is useful both as a regression test, and also to ensure all tags are covered.
 *
 * @covers \Hyde\Framework\Modules\Metadata\MetadataBag
 */
class MetadataViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => 'http://localhost']);
        config(['hyde.cache_busting' => false]);
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

    protected function assertAllTagsWereCovered(string $page, array $tags): void
    {
        $expected = file_get_contents(Hyde::path("_site/$page.html"));
        $actual = json_encode($tags);

        $this->assertEquals(
            substr_count($expected, '<meta'),
            substr_count($actual, '<meta'),
            "Failed asserting that all meta tags were covered in the page '$page'"
        );

        $this->assertEquals(
            substr_count($expected, '<link'),
            substr_count($actual, '<link'),
            "Failed asserting that all link tags were covered in the page '$page'"
        );
    }

    protected function getDefaultTags(): array
    {
        return [
            '<meta charset="utf-8">',
            '<meta name="viewport" content="width=device-width, initial-scale=1">',
            '<meta id="meta-color-scheme" name="color-scheme" content="light">',
            '<link rel="sitemap" href="http://localhost/sitemap.xml" type="application/xml" title="Sitemap">',
            '<meta name="generator" content="HydePHP '.HydeKernel::VERSION.'">',
            '<meta property="og:site_name" content="HydePHP">',
        ];
    }

    public function test_metadata_tags_in_empty_blade_page()
    {
        $this->file('_pages/test.blade.php', '@extends(\'hyde::layouts.app\')');
        $this->build('_pages/test.blade.php');

        $assertions = $this->assertSee('test', array_merge($this->getDefaultTags(), [
            '<title>HydePHP - Test</title>',
            '<link rel="stylesheet" href="media/app.css">',
            '<link rel="canonical" href="http://localhost/test.html">',
            '<meta name="twitter:title" content="HydePHP - Test">',
            '<meta property="og:title" content="HydePHP - Test">',
        ]));

        $this->assertAllTagsWereCovered('test', $assertions);
    }

    public function test_metadata_tags_in_empty_markdown_page()
    {
        $this->markdown('_pages/test.md');
        $this->build('_pages/test.md');

        $assertions = $this->assertSee('test', array_merge($this->getDefaultTags(), [
            '<title>HydePHP - Test</title>',
            '<link rel="stylesheet" href="media/app.css">',
            '<link rel="canonical" href="http://localhost/test.html">',
            '<meta name="twitter:title" content="HydePHP - Test">',
            '<meta property="og:title" content="HydePHP - Test">',
        ]));

        $this->assertAllTagsWereCovered('test', $assertions);
    }

    public function test_metadata_tags_in_empty_documentation_page()
    {
        $this->markdown('_docs/test.md');
        $this->build('_docs/test.md');

        $assertions = $this->assertSee('docs/test', array_merge($this->getDefaultTags(), [
            '<title>HydePHP - Test</title>',
            '<link rel="stylesheet" href="../media/app.css">',
            '<link rel="canonical" href="http://localhost/docs/test.html">',
            '<meta name="twitter:title" content="HydePHP - Test">',
            '<meta property="og:title" content="HydePHP - Test">',
        ]));

        $this->assertAllTagsWereCovered('docs/test', $assertions);
    }

    public function test_metadata_tags_in_empty_markdown_post()
    {
        $this->markdown('_posts/test.md');
        $this->build('_posts/test.md');

        $assertions = $this->assertSee('posts/test', array_merge($this->getDefaultTags(), [
            '<title>HydePHP - Test</title>',
            '<link rel="alternate" href="http://localhost/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">',
            '<link rel="stylesheet" href="../media/app.css">',
            '<link rel="canonical" href="http://localhost/posts/test.html">',
            '<meta name="twitter:title" content="HydePHP - Test">',
            '<meta name="url" content="http://localhost/posts/test.html">',
            '<meta property="og:title" content="HydePHP - Test">',
            '<meta property="og:url" content="http://localhost/posts/test.html">',
            '<meta property="og:type" content="article">',
            '<meta itemprop="identifier" content="test">',
            '<meta itemprop="url" content="http://localhost/posts/test">',
        ]));

        $this->assertAllTagsWereCovered('posts/test', $assertions);
    }

    public function test_metadata_tags_in_markdown_post_with_flat_front_matter()
    {
        // Run the test above, but with all front matter properties (without array notation)
        $this->file('_posts/test.md', <<<'MARKDOWN'
            ---
            title: "My title"
            description: "My description"
            category: "My category"
            date: "2022-01-01"
            author: "Mr. Hyde"
            image: image.jpg
            ---
            
            ## Hello World 
            
            Lorem Ipsum Dolor Amet.
            MARKDOWN
        );
        $this->build('_posts/test.md');

        $assertions = $this->assertSee('posts/test', array_merge($this->getDefaultTags(), [
            '<title>HydePHP - My title</title>',
            '<link rel="alternate" href="http://localhost/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">',
            '<link rel="stylesheet" href="../media/app.css">',
            '<link rel="canonical" href="http://localhost/posts/test.html">',
            '<meta name="twitter:title" content="HydePHP - My title">',
            '<meta name="description" content="My description">',
            '<meta name="author" content="Mr. Hyde">',
            '<meta name="keywords" content="My category">',
            '<meta name="url" content="http://localhost/posts/test.html">',
            '<meta property="og:title" content="HydePHP - My title">',
            '<meta property="og:url" content="http://localhost/posts/test.html">',
            '<meta property="og:type" content="article">',
            '<meta property="og:article:published_time" content="2022-01-01T00:00:00+00:00">',
            '<meta property="og:image" content="../media/image.jpg">',
            '<meta itemprop="identifier" content="test">',
            '<meta itemprop="url" content="http://localhost/posts/test">',
            '<meta itemprop="url" content="../media/image.jpg">',
            '<meta itemprop="contentUrl" content="../media/image.jpg">',
        ]));

        $this->assertAllTagsWereCovered('posts/test', $assertions);
    }
}
