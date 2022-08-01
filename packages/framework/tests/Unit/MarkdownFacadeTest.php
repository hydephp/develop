<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Facades\Markdown;
use Hyde\Testing\TestCase;

/**
 * Class MarkdownConverterTest.
 *
 * @covers \Hyde\Framework\Facades\Markdown
 */
class MarkdownFacadeTest extends TestCase
{
    public function test_parse(): void
    {
        $markdown = '# Hello World!';

        $html = Markdown::parse($markdown);

        $this->assertIsString($html);
        $this->assertEquals("<h1>Hello World!</h1>\n", $html);
    }
}
