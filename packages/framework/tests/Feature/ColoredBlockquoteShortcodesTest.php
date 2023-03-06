<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Markdown\Processing\ColoredBlockquotes;
use Hyde\Testing\UnitTestCase;

/**
 * Class ColoredBlockquoteShortcodesTest.
 *
 * @covers \Hyde\Markdown\Processing\ColoredBlockquotes
 */
class ColoredBlockquoteShortcodesTest extends UnitTestCase
{
    public function testGetMethod()
    {
        $this->assertCount(4, ColoredBlockquotes::get());
        $this->assertContainsOnlyInstancesOf(ColoredBlockquotes::class,
            ColoredBlockquotes::get()
        );
    }

    public function testResolveMethod()
    {
        $this->assertSame(
            '<blockquote class="color"><p>foo</p></blockquote>',
            ColoredBlockquotes::resolve('>color foo')
        );
    }

    public function testCanUseMarkdownWithinBlockquote()
    {
        $this->assertSame(
            '<blockquote class="color"><p>foo <strong>bar</strong></p></blockquote>',
            ColoredBlockquotes::resolve('>color foo **bar**')
        );
    }
}
