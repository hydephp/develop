<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Markdown\Processing\ColoredBlockquotes;
use Hyde\Testing\TestCase;

/**
 * Class ColoredBlockquoteShortcodesTest.
 *
 * @covers \Hyde\Markdown\Processing\ColoredBlockquotes
 */
class ColoredBlockquoteShortcodesTest extends TestCase
{
    public function test_resolve_method()
    {
        $this->assertEquals('<blockquote class="color"><p>foo</p></blockquote>',
            ColoredBlockquotes::resolve('>color foo'));
    }

    public function test_get_method()
    {
        $this->assertCount(4, ColoredBlockquotes::get());
        $this->assertContainsOnlyInstancesOf(ColoredBlockquotes::class,
            ColoredBlockquotes::get());
    }

    public function test_can_use_markdown_within_blockquote()
    {
        $this->assertEquals('<blockquote class="color"><p>foo <strong>bar</strong></p></blockquote>',
            ColoredBlockquotes::resolve('>color foo **bar**')
        );
    }
}
