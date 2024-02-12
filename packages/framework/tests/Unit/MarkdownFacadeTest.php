<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Models\Markdown;
use PHPUnit\Framework\TestCase;

/**
 * Class MarkdownConverterTest.
 *
 * @covers \Hyde\Markdown\Models\Markdown
 */
class MarkdownFacadeTest extends TestCase
{
    public function testRender(): void
    {
        $markdown = '# Hello World!';

        $html = Markdown::render($markdown);

        $this->assertIsString($html);
        $this->assertEquals("<h1>Hello World!</h1>\n", $html);
    }
}
