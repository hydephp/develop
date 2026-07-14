<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Pages\InMemoryPage;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use RuntimeException;
use TypeError;

/**
 * @see \Hyde\Framework\Testing\Unit\Pages\InMemoryPageUnitTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Pages\InMemoryPage::class)]
class InMemoryPageTest extends TestCase
{
    public function testCanConstructPageWithLiteralContents()
    {
        $page = new InMemoryPage('foo', contents: 'bar');

        $this->assertInstanceOf(InMemoryPage::class, $page);
        $this->assertSame('bar', $page->getContents());
    }

    public function testCanMakePageWithLiteralContents()
    {
        $page = InMemoryPage::make('foo', contents: 'bar');

        $this->assertInstanceOf(InMemoryPage::class, $page);
        $this->assertSame('bar', $page->getContents());
    }

    public function testGetContentsReturnsLiteralContents()
    {
        $this->assertSame('bar', (new InMemoryPage('foo', contents: 'bar'))->getContents());
    }

    public function testCompileReturnsLiteralContents()
    {
        $this->assertSame('bar', (new InMemoryPage('foo', contents: 'bar'))->compile());
    }

    public function testEmptyStringRemainsTheDefaultContents()
    {
        $page = new InMemoryPage('foo');

        $this->assertSame('', $page->getContents());
        $this->assertSame('', $page->compile());
    }

    public function testLiteralZeroIsTreatedAsConfiguredContents()
    {
        $page = new InMemoryPage('foo', contents: '0');

        $this->assertSame('0', $page->getContents());
        $this->assertSame('0', $page->compile());
    }

    public function testCanConstructPageWithRegularClosureContents()
    {
        $page = new InMemoryPage('foo', contents: function (): string {
            return 'bar';
        });

        $this->assertSame('bar', $page->getContents());
    }

    public function testCanMakePageWithClosureContents()
    {
        $page = InMemoryPage::make('foo', contents: fn (): string => 'bar');

        $this->assertSame('bar', $page->getContents());
    }

    public function testArrowFunctionClosureReturnsContents()
    {
        $this->assertSame('bar', (new InMemoryPage('foo', contents: fn (): string => 'bar'))->compile());
    }

    public function testRegularClosureReturnsContents()
    {
        $page = new InMemoryPage('foo', contents: function (): string {
            return 'bar';
        });

        $this->assertSame('bar', $page->compile());
    }

    public function testStaticContentClosureMayIgnoreCurrentPageArgument()
    {
        $page = new InMemoryPage('foo', contents: static fn (): string => 'bar');

        $this->assertSame('bar', $page->getContents());
        $this->assertSame('bar', $page->compile());
    }

    public function testContentClosureReceivesCurrentPageInstance()
    {
        $page = new InMemoryPage('example.txt', contents: function (InMemoryPage $page): string {
            return $page->getIdentifier();
        });

        $this->assertSame('example.txt', $page->getContents());
    }

    public function testContentClosureReceivesCurrentSubclassInstance()
    {
        $page = new InMemoryPageContentTestPage('example.txt', contents: function (InMemoryPageContentTestPage $page): string {
            return $page->contentPrefix().$page->getIdentifier();
        });

        $this->assertSame('subclass:example.txt', $page->compile());
    }

    public function testContentClosureUsesNormalPhpTypeEnforcementForItsPageArgument()
    {
        $page = new InMemoryPage('foo', contents: fn (\stdClass $dependency): string => 'bar');

        $this->expectException(TypeError::class);

        $page->getContents();
    }

    public function testFirstClassCallableClosurePreservesItsOriginalBinding()
    {
        $generator = new class
        {
            public function generate(): string
            {
                return 'generated';
            }
        };
        $page = new InMemoryPage('foo', contents: $generator->generate(...));

        $this->assertSame('generated', $page->compile());
    }

    public function testClosureCanReturnEmptyString()
    {
        $page = new InMemoryPage('foo', contents: fn (): string => '');

        $this->assertSame('', $page->getContents());
        $this->assertSame('', $page->compile());
    }

    public function testGetContentsPreservesStringReturnTypeEnforcementForClosures()
    {
        $this->expectException(TypeError::class);

        (new InMemoryPage('foo', contents: fn () => 123))->getContents();
    }

    public function testCompilePreservesStringReturnTypeEnforcementForClosures()
    {
        $this->expectException(TypeError::class);

        (new InMemoryPage('foo', contents: fn () => 123))->compile();
    }

    public function testExceptionsThrownByContentClosurePropagateUnchanged()
    {
        $exception = new RuntimeException('Generation failed');
        $page = new InMemoryPage('foo', contents: function () use ($exception): never {
            throw $exception;
        });

        $this->expectExceptionObject($exception);

        $page->compile();
    }

    public function testClosureIsLazyWhenPageIsConstructedAndRunsWhenContentsAreRequested()
    {
        $invocations = 0;
        $page = new InMemoryPage('foo', contents: function () use (&$invocations): string {
            $invocations++;

            return 'bar';
        });

        $this->assertSame(0, $invocations);
        $this->assertSame('bar', $page->getContents());
        $this->assertSame(1, $invocations);
    }

    public function testClosureIsLazyWhenPageIsMadeAndRunsWhenPageIsCompiled()
    {
        $invocations = 0;
        $page = InMemoryPage::make('foo', contents: function () use (&$invocations): string {
            $invocations++;

            return 'bar';
        });

        $this->assertSame(0, $invocations);
        $this->assertSame('bar', $page->compile());
        $this->assertSame(1, $invocations);
    }

    public function testOneCompilationInvokesClosureExactlyOnce()
    {
        $invocations = 0;
        $page = new InMemoryPage('foo', contents: function () use (&$invocations): string {
            $invocations++;

            return 'bar';
        });

        $page->compile();

        $this->assertSame(1, $invocations);
    }

    public function testClosureContentsAreInvokedAgainForEveryCompilation()
    {
        $invocations = 0;
        $page = new InMemoryPage('foo', contents: function () use (&$invocations): string {
            return (string) ++$invocations;
        });

        $this->assertSame('1', $page->compile());
        $this->assertSame('2', $page->compile());
        $this->assertSame(2, $invocations);
    }

    public function testClosureContentsAreInvokedAgainEveryTimeContentsAreRequested()
    {
        $invocations = 0;
        $page = new InMemoryPage('foo', contents: function () use (&$invocations): string {
            return (string) ++$invocations;
        });

        $this->assertSame('1', $page->getContents());
        $this->assertSame('2', $page->getContents());
        $this->assertSame(2, $invocations);
    }

    public function testLiteralContentsAndViewCannotBothBeConfigured()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('InMemoryPage cannot define both contents and a view.');

        new InMemoryPage('foo', contents: 'contents', view: 'foo');
    }

    public function testEmptyLiteralContentsAndViewCannotBothBeConfigured()
    {
        $this->expectException(InvalidArgumentException::class);

        new InMemoryPage('foo', contents: '', view: 'foo');
    }

    public function testClosureContentsAndViewCannotBothBeConfiguredWithoutInvokingClosure()
    {
        $invoked = false;

        try {
            new InMemoryPage('foo', contents: function () use (&$invoked): string {
                $invoked = true;

                return '';
            }, view: 'foo');

            $this->fail('Expected an exception when both contents and a view are configured.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('InMemoryPage cannot define both contents and a view.', $exception->getMessage());
        }

        $this->assertFalse($invoked);
    }

    public function testEmptyViewIsTreatedAsNoView()
    {
        $page = new InMemoryPage('foo', view: '');

        $this->assertSame('', $page->getBladeView());
        $this->assertSame('', $page->getContents());
        $this->assertSame('', $page->compile());
    }

    public function testEmptyViewIsTreatedAsNoViewWhenContentsAreSupplied()
    {
        $page = new InMemoryPage('foo', contents: 'bar', view: '');

        $this->assertSame('', $page->getBladeView());
        $this->assertSame('bar', $page->getContents());
        $this->assertSame('bar', $page->compile());
    }

    public function testMakeTreatsEmptyViewAsNoView()
    {
        $page = InMemoryPage::make('foo', view: '');

        $this->assertSame('', $page->getBladeView());
        $this->assertSame('', $page->compile());
    }

    public function testEmptyContentsWithoutViewIsValid()
    {
        $page = new InMemoryPage('foo', contents: '', view: null);

        $this->assertSame('', $page->getContents());
        $this->assertSame('', $page->getBladeView());
        $this->assertSame('', $page->compile());
    }

    public function testMakeRejectsContentsAndView()
    {
        $this->expectException(InvalidArgumentException::class);

        InMemoryPage::make('foo', contents: 'contents', view: 'foo');
    }

    public function testRegisteredBladeViewRenders()
    {
        $this->file('_pages/foo.blade.php', 'bar');

        $this->assertSame('bar', (new InMemoryPage('foo', view: 'foo'))->compile());
    }

    public function testCanMakePageWithRegisteredBladeView()
    {
        $this->file('_pages/foo.blade.php', 'bar');

        $this->assertSame('bar', InMemoryPage::make('foo', view: 'foo')->compile());
    }

    public function testPositionalViewCanUseNullContentsSentinel()
    {
        $this->file('_pages/foo.blade.php', 'bar');

        $this->assertSame('bar', (new InMemoryPage('foo', [], null, 'foo'))->compile());
    }

    public function testFrontMatterIsPassedToRegisteredBladeView()
    {
        $this->file('_pages/foo.blade.php', 'foo {{ $bar }}');

        $this->assertSame('foo baz', (new InMemoryPage('foo', ['bar' => 'baz'], view: 'foo'))->compile());
    }

    public function testArbitraryBladeFileRendersThroughAnonymousViewCompiler()
    {
        $this->file('_pages/foo.blade.php', 'blade');

        $this->assertSame('blade', (new InMemoryPage('foo', view: '_pages/foo.blade.php'))->compile());
    }

    public function testFrontMatterIsPassedToArbitraryBladeFile()
    {
        $this->file('_pages/foo.blade.php', 'blade {{ $foo }}');

        $this->assertSame('blade bar', (new InMemoryPage('foo', ['foo' => 'bar'], view: '_pages/foo.blade.php'))->compile());
    }

    public function testNeitherContentsNorViewCompilesToEmptyString()
    {
        $this->assertSame('', (new InMemoryPage('foo'))->compile());
    }

    public function testViewMethodReturnsConfiguredView()
    {
        $this->assertSame('bar', (new InMemoryPage('foo', view: 'bar'))->getBladeView());
    }

    public function testSubclassCanOverrideCompileMethod()
    {
        $page = new class extends InMemoryPage
        {
            public function compile(): string
            {
                return 'custom';
            }
        };

        $this->assertSame('custom', $page->compile());
    }

    public function testCompileDoesNotUseOverriddenGetContentsMethodWhenViewIsConfigured()
    {
        $this->file('_pages/foo.blade.php', 'view');

        $page = new class('foo', view: 'foo') extends InMemoryPage
        {
            public int $invocations = 0;

            public function getContents(): string
            {
                $this->invocations++;

                return 'custom';
            }
        };

        $this->assertSame('view', $page->compile());
        $this->assertSame(0, $page->invocations);
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

    public function testStaticAndInstanceOutputPathsUseTheSameSemantics()
    {
        foreach (['foo', 'robots.txt', 'docs/search.json'] as $identifier) {
            $this->assertSame(
                InMemoryPage::outputPath($identifier),
                (new InMemoryPage($identifier))->getOutputPath()
            );
        }
    }
}

class InMemoryPageContentTestPage extends InMemoryPage
{
    protected string $contentPrefix = 'subclass:';

    public function contentPrefix(): string
    {
        return $this->contentPrefix;
    }
}
