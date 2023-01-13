<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use BadMethodCallException;
use Hyde\Pages\VirtualPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\VirtualPage
 *
 * @see \Hyde\Framework\Testing\Unit\Pages\VirtualPageUnitTest
 */
class VirtualPageTest extends TestCase
{
    public function testConstructWithContentsString()
    {
        $this->assertInstanceOf(VirtualPage::class, new VirtualPage('foo', contents: 'bar'));
    }

    public function testMakeWithContentsString()
    {
        $this->assertInstanceOf(VirtualPage::class, VirtualPage::make('foo', contents: 'bar'));
        $this->assertEquals(VirtualPage::make('foo', contents: 'bar'), new VirtualPage('foo', contents: 'bar'));
    }

    public function testContentsMethod()
    {
        $this->assertSame('bar', (new VirtualPage('foo', contents: 'bar'))->getContents());
    }

    public function testViewMethod()
    {
        $this->assertSame('bar', (new VirtualPage('foo', view: 'bar'))->getBladeView());
    }

    public function testCompileMethodUsesContentsProperty()
    {
        $this->assertSame('bar', (new VirtualPage('foo', contents: 'bar'))->compile());
    }

    public function testCompileMethodUsesViewProperty()
    {
        $this->file('_pages/foo.blade.php', 'bar');
        $this->assertSame('bar', (new VirtualPage('foo', view: 'foo'))->compile());
    }

    public function testCompileMethodUsingViewCompileAndFrontMatter()
    {
        $this->file('_pages/foo.blade.php', 'foo {{ $bar }}');
        $this->assertSame('foo baz', (new VirtualPage('foo', ['bar' => 'baz'], view: 'foo'))->compile());
    }

    public function testCompileMethodPrefersContentsPropertyOverView()
    {
        $this->file('_pages/foo.blade.php', 'blade');
        $this->assertSame('contents', (new VirtualPage('foo', contents: 'contents', view: 'foo'))->compile());
    }

    public function testCompileMethodCanCompileAnonymousViewFiles()
    {
        $this->file('_pages/foo.blade.php', 'blade');
        $this->assertSame('blade', (new VirtualPage('foo', view: '_pages/foo.blade.php'))->compile());
    }

    public function testCompileMethodCanCompileAnonymousViewFilesWithFrontMatter()
    {
        $this->file('_pages/foo.blade.php', 'blade {{ $foo }}');
        $this->assertSame('blade bar', (new VirtualPage('foo', ['foo' => 'bar'], view: '_pages/foo.blade.php'))->compile());
    }

    public function testCanCreateInstanceMacros()
    {
        $page = VirtualPage::make('foo');

        $page->macro('foo', function () {
            return 'bar';
        });

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame('bar', $page->foo());
    }

    public function testCanCreateInstanceMacrosUsingCallableObject()
    {
        $page = VirtualPage::make('foo');

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
        $page = VirtualPage::make('foo');

        $page->macro('foo', function (...$args) {
            return $args;
        });

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame(['bar'], $page->foo('bar'));
    }

    public function testCanUseMacrosToOverloadClassCompileMethod()
    {
        $page = VirtualPage::make('foo');

        $page->macro('compile', function () {
            return 'bar';
        });

        $this->assertSame('bar', $page->compile());
    }

    public function testCallingUndefinedMacro()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Hyde\Pages\VirtualPage::foo does not exist.');

        $page = VirtualPage::make('foo');

        /** @noinspection PhpUndefinedMethodInspection */
        $page->foo();
    }
}
