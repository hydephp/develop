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

    public function testHelperReturnsStringAsIsIfPrettyUrlsIsNotTrue()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);

        $this->assertEquals('foo/bar.html', Hyde::formatLink('foo/bar.html'));
    }

    public function testHelperReturnsPrettyUrlIfPrettyUrlsIsTrue()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);

        $this->assertEquals('foo/bar', Hyde::formatLink('foo/bar.html'));
    }

    public function testHelperRespectsAbsoluteUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('/foo/bar.html', Hyde::formatLink('/foo/bar.html'));
    }

    public function testHelperRespectsPrettyAbsoluteUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/foo/bar', Hyde::formatLink('/foo/bar.html'));
    }

    public function testHelperRespectsRelativeUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('../foo/bar.html', Hyde::formatLink('../foo/bar.html'));
    }

    public function testHelperRespectsPrettyRelativeUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('../foo/bar', Hyde::formatLink('../foo/bar.html'));
    }

    public function testNonHtmlLinksAreNotModified()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/foo/bar.jpg', Hyde::formatLink('/foo/bar.jpg'));
    }

    public function testHelperRespectsAbsoluteUrlsWithPrettyUrlsEnabled()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/foo/bar.jpg', Hyde::formatLink('/foo/bar.jpg'));
    }

    public function testHelperRewritesIndexWhenUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('/', Hyde::formatLink('index.html'));
    }

    public function testHelperDoesNotRewriteIndexWhenNotUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('index.html', Hyde::formatLink('index.html'));
    }

    public function testHelperRewritesDocumentationPageIndexWhenUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('docs/', Hyde::formatLink('docs/index.html'));
    }

    public function testHelperDoesNotRewriteDocumentationPageIndexWhenNotUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('docs/index.html', Hyde::formatLink('docs/index.html'));
    }

    public function testHelpersRewritesArbitraryNestedIndexPagesWhenUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->assertEquals('foo/bar/', Hyde::formatLink('foo/bar/index.html'));
    }

    public function testHelpersDoesNotRewriteArbitraryNestedIndexPagesWhenNotUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->assertEquals('foo/bar/index.html', Hyde::formatLink('foo/bar/index.html'));
    }
}
