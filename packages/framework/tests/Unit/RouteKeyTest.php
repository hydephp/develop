<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RouteKey;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Support\Models\RouteKey
 */
class RouteKeyTest extends UnitTestCase
{
    protected static bool $needsConfig = true;

    public function testMake()
    {
        $this->assertEquals(RouteKey::make('foo'), new RouteKey('foo'));
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(RouteKey::class, new RouteKey('test'));
    }

    public function testToString()
    {
        $this->assertSame('foo', (new RouteKey('foo'))->__toString());
    }

    public function testGet()
    {
        $this->assertSame('foo', (new RouteKey('foo'))->get());
    }

    public function testCast()
    {
        $this->assertSame('foo', (string) new RouteKey('foo'));
    }

    public function testConstructorValuesAreNormalized()
    {
        $this->assertEquals(new RouteKey('foo'), new RouteKey('foo'));
        $this->assertEquals(new RouteKey('foo/bar'), new RouteKey('foo/bar'));
        $this->assertEquals(new RouteKey('foo.bar'), new RouteKey('foo.bar'));
    }

    public function testStaticConstructorValuesAreNormalized()
    {
        $this->assertEquals(RouteKey::make('foo'), RouteKey::make('foo'));
        $this->assertEquals(RouteKey::make('foo/bar'), RouteKey::make('foo/bar'));
        $this->assertEquals(RouteKey::make('foo.bar'), RouteKey::make('foo.bar'));
    }

    public function testFromPage()
    {
        $this->assertEquals(new RouteKey('foo'), RouteKey::fromPage(HtmlPage::class, 'foo'));
        $this->assertEquals(new RouteKey('foo'), RouteKey::fromPage(BladePage::class, 'foo'));
        $this->assertEquals(new RouteKey('foo'), RouteKey::fromPage(MarkdownPage::class, 'foo'));
        $this->assertEquals(new RouteKey('posts/foo'), RouteKey::fromPage(MarkdownPost::class, 'foo'));
        $this->assertEquals(new RouteKey('docs/foo'), RouteKey::fromPage(DocumentationPage::class, 'foo'));
    }

    public function testFromPageWithNestedIdentifier()
    {
        $this->assertEquals(new RouteKey('foo/bar'), RouteKey::fromPage(HtmlPage::class, 'foo/bar'));
        $this->assertEquals(new RouteKey('foo/bar'), RouteKey::fromPage(BladePage::class, 'foo/bar'));
        $this->assertEquals(new RouteKey('foo/bar'), RouteKey::fromPage(MarkdownPage::class, 'foo/bar'));
        $this->assertEquals(new RouteKey('posts/foo/bar'), RouteKey::fromPage(MarkdownPost::class, 'foo/bar'));
        $this->assertEquals(new RouteKey('docs/foo/bar'), RouteKey::fromPage(DocumentationPage::class, 'foo/bar'));
    }

    public function testFromPageWithInMemoryPage()
    {
        $this->assertEquals(new RouteKey('foo'), RouteKey::fromPage(InMemoryPage::class, 'foo'));
        $this->assertEquals(new RouteKey('foo/bar'), RouteKey::fromPage(InMemoryPage::class, 'foo/bar'));
    }

    public function testFromPageWithCustomOutputDirectory()
    {
        MarkdownPage::setOutputDirectory('foo');
        $this->assertEquals(new RouteKey('foo/bar'), RouteKey::fromPage(MarkdownPage::class, 'bar'));
    }

    public function testFromPageWithCustomNestedOutputDirectory()
    {
        MarkdownPage::setOutputDirectory('foo/bar');
        $this->assertEquals(new RouteKey('foo/bar/baz'), RouteKey::fromPage(MarkdownPage::class, 'baz'));
    }

    public function testItExtractsCoreIdentifierPartFromNumericalFilenamePrefix()
    {
        $this->assertSame('test', RouteKey::fromPage(InMemoryPage::class, '01-test')->get());
    }

    public function testItExtractsCoreIdentifierPartFromNumericalFilenamePrefixWithKebabCaseSyntax()
    {
        $this->assertSame('foo', RouteKey::fromPage(InMemoryPage::class, '01-foo')->get());
        $this->assertSame('bar', RouteKey::fromPage(InMemoryPage::class, '02-bar')->get());
        $this->assertSame('baz', RouteKey::fromPage(InMemoryPage::class, '03-baz')->get());
    }

    public function testItExtractsCoreIdentifierPartFromNumericalFilenamePrefixWithSnakeCaseSyntax()
    {
        $this->assertSame('foo', RouteKey::fromPage(InMemoryPage::class, '01_foo')->get());
        $this->assertSame('bar', RouteKey::fromPage(InMemoryPage::class, '02_bar')->get());
        $this->assertSame('baz', RouteKey::fromPage(InMemoryPage::class, '03_baz')->get());
    }

    public function testItExtractsCoreIdentifierPartFromNumericalFilenamePrefixRegardlessOfLeadingZeroes()
    {
        $this->assertSame('foo', RouteKey::fromPage(InMemoryPage::class, '123-foo')->get());
        $this->assertSame('foo', RouteKey::fromPage(InMemoryPage::class, '0123-foo')->get());
        $this->assertSame('foo', RouteKey::fromPage(InMemoryPage::class, '00123-foo')->get());
        $this->assertSame('foo', RouteKey::fromPage(InMemoryPage::class, '000123-foo')->get());
        $this->assertSame('foo', RouteKey::fromPage(InMemoryPage::class, '0000123-foo')->get());
    }

    public function testItExtractsCoreIdentifierPartFromNumericalFilenamePrefixForNestedIdentifiers()
    {
        $this->assertSame('foo/bar', RouteKey::fromPage(InMemoryPage::class, 'foo/01-bar')->get());
        $this->assertSame('foo/bar/baz', RouteKey::fromPage(InMemoryPage::class, 'foo/bar/02-baz')->get());
        $this->assertSame('foo/bar/baz', RouteKey::fromPage(InMemoryPage::class, 'foo/01-bar/03-baz')->get());
    }

    public function testItDoesNotExtractCoreIdentifierPartFromNumericalFilenamePrefixWhenFeatureIsDisabled()
    {
        self::mockConfig(['hyde.numerical_page_ordering' => false]);

        $this->assertSame('01-test', RouteKey::fromPage(InMemoryPage::class, '01-test')->get());
        $this->assertSame('01-home', RouteKey::fromPage(InMemoryPage::class, '01-home')->get());
        $this->assertSame('01-404', RouteKey::fromPage(InMemoryPage::class, '01-404')->get());
    }

    public function testItDoesNotExtractNonNumericalFilenamePrefixes()
    {
        $this->assertSame('foo-bar', RouteKey::fromPage(InMemoryPage::class, 'foo-bar')->get());
        $this->assertSame('abc-bar', RouteKey::fromPage(InMemoryPage::class, 'abc-bar')->get());
    }

    public static function tearDownAfterClass(): void
    {
        MarkdownPage::setOutputDirectory('');
    }
}
