<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Hyde;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Testing\InteractsWithPages;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Facades\View;
use Illuminate\View\Factory;
use Mockery;

/**
 * @covers \Hyde\Foundation\Kernel\Hyperlinks::relativeLink
 */
class HyperlinkFileHelperRelativeLinkTest extends UnitTestCase
{
    use InteractsWithPages;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        Render::swap(new RenderData());
        View::swap(Mockery::mock(Factory::class)->makePartial());
    }

    public function testHelperReturnsStringAsIsIfCurrentIsNotSet()
    {
        $this->assertEquals('foo/bar.html', Hyde::relativeLink('foo/bar.html'));
    }

    public function testHelperInjectsProperNumberOfDoublesSlash()
    {
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo.html', Hyde::relativeLink('foo.html'));
    }

    public function testHelperInjectsProperNumberOfDoublesSlashForDeeplyNestedPaths()
    {
        $this->mockCurrentPage('foo/bar/baz/qux.html');
        $this->assertEquals('../../../foo.html', Hyde::relativeLink('foo.html'));
    }

    public function testHelperHandlesDestinationWithoutFileExtension()
    {
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo', Hyde::relativeLink('foo'));
    }

    public function testHelperHandlesCurrentWithoutFileExtension()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo.html', Hyde::relativeLink('foo.html'));
    }

    public function testHelperHandlesCaseWithoutAnyFileExtensions()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo', Hyde::relativeLink('foo'));
    }

    public function testHelperHandlesCaseWithMixedFileExtensions()
    {
        $this->mockCurrentPage('foo/bar.md');
        $this->assertEquals('../foo.md', Hyde::relativeLink('foo.md'));
        $this->mockCurrentPage('foo/bar.txt');
        $this->assertEquals('../foo.txt', Hyde::relativeLink('foo.txt'));
    }

    public function testHelperHandlesDifferentFileExtensions()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo.png', Hyde::relativeLink('foo.png'));
        $this->assertEquals('../foo.css', Hyde::relativeLink('foo.css'));
        $this->assertEquals('../foo.js', Hyde::relativeLink('foo.js'));
    }

    public function testHelperReturnsPrettyUrlIfEnabledAndDestinationIsAHtmlFile()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo', Hyde::relativeLink('foo.html'));
    }

    public function testHelperMethodDoesNotRequireCurrentPathToBeHtmlToUsePrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo', Hyde::relativeLink('foo.html'));
    }

    public function testHelperReturnsDoesNotReturnPrettyUrlIfWhenEnabledButAndDestinationIsNotAHtmlFile()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo.png', Hyde::relativeLink('foo.png'));
    }

    public function testHelperRewritesIndexWhenUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('./', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar/baz.html');
        $this->assertEquals('../../', Hyde::relativeLink('index.html'));
    }

    public function testHelperDoesNotRewriteIndexWhenNotUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('index.html', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../index.html', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar/baz.html');
        $this->assertEquals('../../index.html', Hyde::relativeLink('index.html'));
    }

    public function testHelperRewritesDocumentationPageIndexWhenUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('docs/', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs.html');
        $this->assertEquals('docs/', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../docs/', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs/foo.html');
        $this->assertEquals('../docs/', Hyde::relativeLink('docs/index.html'));
    }

    public function testHelperDoesNotRewriteDocumentationPageIndexWhenNotUsingPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => false]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('docs/index.html', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs.html');
        $this->assertEquals('docs/index.html', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../docs/index.html', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs/foo.html');
        $this->assertEquals('../docs/index.html', Hyde::relativeLink('docs/index.html'));
    }

    public function testHelperDoesNotRewriteAlreadyProcessedLinks()
    {
        $this->assertEquals('../foo', Hyde::relativeLink('../foo'));
    }
}
