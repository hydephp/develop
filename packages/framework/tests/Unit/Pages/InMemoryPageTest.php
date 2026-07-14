<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use BadMethodCallException;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Testing\Unit\Pages\InMemoryPageUnitTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Pages\InMemoryPage::class)]
class InMemoryPageTest extends TestCase
{
    public function testConstructWithContentsString()
    {
        $this->assertInstanceOf(InMemoryPage::class, new InMemoryPage('foo', contents: 'bar'));
    }

    public function testMakeWithContentsString()
    {
        $this->assertInstanceOf(InMemoryPage::class, InMemoryPage::make('foo', contents: 'bar'));
        $this->assertEquals(InMemoryPage::make('foo', contents: 'bar'), new InMemoryPage('foo', contents: 'bar'));
    }

    public function testFileWithContentsString()
    {
        $this->assertInstanceOf(InMemoryPage::class, InMemoryPage::file('robots.txt', contents: 'bar'));
        $this->assertSame('robots.txt', InMemoryPage::file('robots.txt', contents: 'bar')->getOutputPath());
    }

    public function testContentsMethod()
    {
        $this->assertSame('bar', (new InMemoryPage('foo', contents: 'bar'))->getContents());
    }

    public function testViewMethod()
    {
        $this->assertSame('bar', (new InMemoryPage('foo', view: 'bar'))->getBladeView());
    }

    public function testCompileMethodUsesContentsProperty()
    {
        $this->assertSame('bar', (new InMemoryPage('foo', contents: 'bar'))->compile());
    }

    public function testCompileMethodUsesViewProperty()
    {
        $this->file('_pages/foo.blade.php', 'bar');
        $this->assertSame('bar', (new InMemoryPage('foo', view: 'foo'))->compile());
    }

    public function testCompileMethodUsingViewCompileAndFrontMatter()
    {
        $this->file('_pages/foo.blade.php', 'foo {{ $bar }}');
        $this->assertSame('foo baz', (new InMemoryPage('foo', ['bar' => 'baz'], view: 'foo'))->compile());
    }

    public function testCompileMethodPrefersContentsPropertyOverView()
    {
        $this->file('_pages/foo.blade.php', 'blade');
        $this->assertSame('contents', (new InMemoryPage('foo', contents: 'contents', view: 'foo'))->compile());
    }

    public function testCompileMethodCanCompileAnonymousViewFiles()
    {
        $this->file('_pages/foo.blade.php', 'blade');
        $this->assertSame('blade', (new InMemoryPage('foo', view: '_pages/foo.blade.php'))->compile());
    }

    public function testCompileMethodCanCompileAnonymousViewFilesWithFrontMatter()
    {
        $this->file('_pages/foo.blade.php', 'blade {{ $foo }}');
        $this->assertSame('blade bar', (new InMemoryPage('foo', ['foo' => 'bar'], view: '_pages/foo.blade.php'))->compile());
    }

    public function testCanCreateInstanceMacros()
    {
        $page = InMemoryPage::make('foo');

        $page->macro('foo', function () {
            return 'bar';
        });

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame('bar', $page->foo());
    }

    public function testCanCreateInstanceMacrosUsingCallableObject()
    {
        $page = InMemoryPage::make('foo');

        $page->macro('foo', new class
        {
            public function __invoke(): string
            {
                return 'bar';
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame('bar', $page->foo());
    }

    public function testCallingMacroWithArguments()
    {
        $page = InMemoryPage::make('foo');

        $page->macro('foo', function (...$args) {
            return $args;
        });

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame(['bar'], $page->foo('bar'));
    }

    public function testCanUseMacrosToOverloadClassCompileMethod()
    {
        $page = InMemoryPage::make('foo');

        $page->macro('compile', function () {
            return 'bar';
        });

        $this->assertSame('bar', $page->compile());
    }

    public function testCallingUndefinedMacro()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Hyde\Pages\InMemoryPage::foo does not exist.');

        $page = InMemoryPage::make('foo');

        /** @noinspection PhpUndefinedMethodInspection */
        $page->foo();
    }

    public function testHasMacro()
    {
        $page = InMemoryPage::make('foo');

        $page->macro('foo', function () {
            return 'bar';
        });

        $this->assertTrue($page->hasMacro('foo'));
        $this->assertFalse($page->hasMacro('bar'));
    }

    public function testOutputPathUsesNormalHtmlPageSemantics()
    {
        $this->assertSame('foo.html', InMemoryPage::outputPath('foo'));
        $this->assertSame('robots.txt.html', InMemoryPage::outputPath('robots.txt'));
        $this->assertSame('data.json.html', InMemoryPage::outputPath('data.json'));
        $this->assertSame('sitemap.xml.html', InMemoryPage::outputPath('sitemap.xml'));
        $this->assertSame('docs/search.json.html', InMemoryPage::outputPath('docs/search.json'));
        $this->assertSame('foo.md.html', InMemoryPage::outputPath('foo.md'));
        $this->assertSame('foo.html.html', InMemoryPage::outputPath('foo.html'));
        $this->assertSame('docs/1.x.html', InMemoryPage::outputPath('docs/1.x'));
    }

    public function testFileUsesAnyOutputPathExactly()
    {
        $this->assertSame('robots.txt', InMemoryPage::file('robots.txt')->getOutputPath());
        $this->assertSame('site.webmanifest', InMemoryPage::file('site.webmanifest')->getOutputPath());
        $this->assertSame('sitemap.xsl', InMemoryPage::file('sitemap.xsl')->getOutputPath());
        $this->assertSame('downloads/data.csv', InMemoryPage::file('downloads/data.csv')->getOutputPath());
        $this->assertSame('feed', InMemoryPage::file('feed')->getOutputPath());
    }

    public function testGetRouteKeyForFile()
    {
        $this->assertSame('robots.txt', InMemoryPage::file('robots.txt')->getRouteKey());
        $this->assertSame('feed', InMemoryPage::file('feed')->getRouteKey());
    }

    public function testGetLinkForFile()
    {
        $this->assertSame('robots.txt', InMemoryPage::file('robots.txt')->getLink());
    }

    public function testGetLinkForFileIsNotAffectedByPrettyUrls()
    {
        config(['hyde.pretty_urls' => true]);

        $this->assertSame('robots.txt', InMemoryPage::file('robots.txt')->getLink());
    }

    public function testGetCanonicalUrlForFile()
    {
        config(['hyde.url' => 'https://example.com']);

        $this->assertSame('https://example.com/robots.txt', InMemoryPage::file('robots.txt')->getCanonicalUrl());
    }

    public function testCompiledContentsAreNotAffectedByExactOutputPath()
    {
        $this->assertSame('User-agent: *', InMemoryPage::file('robots.txt', contents: 'User-agent: *')->compile());
    }
}
