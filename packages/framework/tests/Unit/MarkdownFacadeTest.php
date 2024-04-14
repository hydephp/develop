<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Markdown\Models\Markdown;

/**
 * @covers \Hyde\Markdown\Models\Markdown
 */
class MarkdownFacadeTest extends UnitTestCase
{
    public function testRender(): void
    {
        $markdown = '# Hello World!';

        $html = Markdown::render($markdown);

        $this->assertIsString($html);
        $this->assertEquals("<h1>Hello World!</h1>\n", $html);
    }
}
