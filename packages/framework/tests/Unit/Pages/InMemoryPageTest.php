<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Pages\InMemoryPage;
use Hyde\Testing\TestCase;
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
        $this->file('_pages/foo.blade.php', '@php(throw new \RuntimeException("View should not render"))');

        $page = new InMemoryPage('foo', contents: '0', view: 'foo');

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

    public function testStaticClosureRunsUnbound()
    {
        $page = new InMemoryPage('foo', contents: static fn (): string => 'bar');

        $this->assertSame('bar', $page->getContents());
        $this->assertSame('bar', $page->compile());
    }

    public function testNonStaticClosureCanUseThis()
    {
        $page = new InMemoryPage('example.txt', contents: function (): string {
            return $this->getIdentifier();
        });

        $this->assertSame('example.txt', $page->getContents());
    }

    public function testBoundClosureCanAccessPageFrontMatter()
    {
        $page = new InMemoryPage('example.txt', ['title' => 'Example'], function (): string {
            return $this->matter->get('title');
        });

        $this->assertSame('Example', $page->compile());
    }

    public function testClosureUsesSubclassAsBindingScope()
    {
        $page = new class('foo', contents: function (): string {
            return $this->subclassContents;
        }) extends InMemoryPage
        {
            protected string $subclassContents = 'subclass';
        };

        $this->assertSame('subclass', $page->getContents());
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

    public function testClosureContentsAreResolvedAgainForEveryCompilation()
    {
        $invocations = 0;
        $page = new InMemoryPage('foo', contents: function () use (&$invocations): string {
            return (string) ++$invocations;
        });

        $this->assertSame('1', $page->compile());
        $this->assertSame('2', $page->compile());
        $this->assertSame(2, $invocations);
    }

    public function testClosureContentsAreResolvedAgainEveryTimeContentsAreRequested()
    {
        $invocations = 0;
        $page = new InMemoryPage('foo', contents: function () use (&$invocations): string {
            return (string) ++$invocations;
        });

        $this->assertSame('1', $page->getContents());
        $this->assertSame('2', $page->getContents());
        $this->assertSame(2, $invocations);
    }

    public function testNonEmptyLiteralContentsTakePrecedenceOverNamedView()
    {
        $this->file('_pages/foo.blade.php', '@php(throw new \RuntimeException("View should not render"))');

        $this->assertSame('contents', (new InMemoryPage('foo', contents: 'contents', view: 'foo'))->compile());
    }

    public function testClosureContentsTakePrecedenceOverNamedView()
    {
        $this->file('_pages/foo.blade.php', '@php(throw new \RuntimeException("View should not render"))');

        $page = new InMemoryPage('foo', contents: fn (): string => 'contents', view: 'foo');

        $this->assertSame('contents', $page->compile());
    }

    public function testClosureReturningEmptyStringTakesPrecedenceOverNamedView()
    {
        $this->file('_pages/foo.blade.php', '@php(throw new \RuntimeException("View should not render"))');

        $page = new InMemoryPage('foo', contents: fn (): string => '', view: 'foo');

        $this->assertSame('', $page->compile());
    }

    public function testEmptyLiteralContentsAllowNamedViewToRender()
    {
        $this->file('_pages/foo.blade.php', 'bar');

        $this->assertSame('bar', (new InMemoryPage('foo', contents: '', view: 'foo'))->compile());
    }

    public function testRegisteredBladeViewRenders()
    {
        $this->file('_pages/foo.blade.php', 'bar');

        $this->assertSame('bar', (new InMemoryPage('foo', view: 'foo'))->compile());
    }

    public function testFrontMatterIsPassedToRegisteredBladeView()
    {
        $this->file('_pages/foo.blade.php', 'foo {{ $bar }}');

        $this->assertSame('foo baz', (new InMemoryPage('foo', ['bar' => 'baz'], view: 'foo'))->compile());
    }

    public function testEmptyLiteralContentsAllowArbitraryBladeFileToRender()
    {
        $this->file('_pages/foo.blade.php', 'blade');

        $page = new InMemoryPage('foo', contents: '', view: '_pages/foo.blade.php');

        $this->assertSame('blade', $page->compile());
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

    public function testMacroMethodWasRemoved()
    {
        $this->assertFalse(method_exists(InMemoryPage::class, 'macro'));
    }

    public function testHasMacroMethodWasRemoved()
    {
        $this->assertFalse(method_exists(InMemoryPage::class, 'hasMacro'));
    }

    public function testCustomMacroCallHandlerWasRemoved()
    {
        $this->assertFalse(method_exists(InMemoryPage::class, '__call'));
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
}
