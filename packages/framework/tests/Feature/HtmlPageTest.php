<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\HtmlPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\HtmlPage
 */
class HtmlPageTest extends TestCase
{
    public function testHtmlPageCanBeCompiled()
    {
        $this->file(HtmlPage::$sourceDirectory.'/foo.html', 'bar');

        $page = new HtmlPage('foo');

        $this->assertEquals('bar', $page->compile());
    }

    public function testCompileMethodUsesContents()
    {
        $this->file(HtmlPage::$sourceDirectory.'/foo.html', 'bar');

        $page = new HtmlPage('foo');

        $this->assertSame($page->contents(), $page->compile());
    }
}
