<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\Kernel\Hyperlinks::formatLink
 */
class HyperlinkFormatHtmlPathTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function test_helper_returns_string_as_is_if_pretty_urls_is_not_true()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);

        $this->assertEquals('foo/bar.html', Hyde::formatLink('foo/bar.html'));
    }

    public function test_helper_returns_pretty_url_if_pretty_urls_is_true()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);

        $this->assertEquals('foo/bar', Hyde::formatLink('foo/bar.html'));
    }

    public function test_non_pretty_urls_is_default_value_when_config_is_not_set()
    {
        self::mockConfig(['hyde.pretty_urls' => null]);

        $this->assertEquals('foo/bar.html', Hyde::formatLink('foo/bar.html'));
    }

    public function test_helper_respects_absolute_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('/foo/bar.html', Hyde::formatLink('/foo/bar.html'));
    }

    public function test_helper_respects_pretty_absolute_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/foo/bar', Hyde::formatLink('/foo/bar.html'));
    }

    public function test_helper_respects_relative_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('../foo/bar.html', Hyde::formatLink('../foo/bar.html'));
    }

    public function test_helper_respects_pretty_relative_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('../foo/bar', Hyde::formatLink('../foo/bar.html'));
    }

    public function test_non_html_links_are_not_modified()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/foo/bar.jpg', Hyde::formatLink('/foo/bar.jpg'));
    }

    public function test_helper_respects_absolute_urls_with_pretty_urls_enabled()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/foo/bar.jpg', Hyde::formatLink('/foo/bar.jpg'));
    }

    public function test_helper_rewrites_index_when_using_pretty_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/', Hyde::formatLink('index.html'));
    }

    public function test_helper_does_not_rewrite_index_when_not_using_pretty_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('index.html', Hyde::formatLink('index.html'));
    }

    public function test_helper_rewrites_documentation_page_index_when_using_pretty_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('docs/', Hyde::formatLink('docs/index.html'));
    }

    public function test_helper_does_not_rewrite_documentation_page_index_when_not_using_pretty_urls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('docs/index.html', Hyde::formatLink('docs/index.html'));
    }
}
