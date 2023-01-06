<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

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

    public function testCompileMethodPrefersContentsPropertyOverView()
    {
        $this->file('_pages/foo.blade.php', 'blade');
        $this->assertSame('contents', (new VirtualPage('foo', contents: 'contents', view: 'foo'))->compile());
    }
}
