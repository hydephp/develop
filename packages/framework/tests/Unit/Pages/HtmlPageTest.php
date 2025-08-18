<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Pages\HtmlPage;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Pages\HtmlPage::class)]
class HtmlPageTest extends TestCase
{
    public function testHtmlPageCanBeCompiled()
    {
        $this->file('_pages/foo.html', 'bar');

        $page = new HtmlPage('foo');

        $this->assertSame('bar', $page->compile());
    }

    public function testCompileMethodUsesContents()
    {
        $this->file('_pages/foo.html', 'bar');

        $page = new HtmlPage('foo');

        $this->assertSame($page->contents(), $page->compile());
    }
}
