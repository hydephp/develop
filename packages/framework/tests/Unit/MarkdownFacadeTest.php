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
        $html = Markdown::render('# Hello World!');

        $this->assertIsString($html);
        $this->assertSame("<h1>Hello World!</h1>\n", $html);
    }
}
