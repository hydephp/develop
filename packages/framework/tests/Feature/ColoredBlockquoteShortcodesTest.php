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
    public function test_resolve_method()
    {
        $this->assertEquals('<blockquote class="color"><p>foo</p></blockquote>',
            ColoredBlockquotes::resolve('>color foo'));
    }

    public function test_can_use_markdown_within_blockquote()
    {
        $this->assertEquals('<blockquote class="color"><p>foo <strong>bar</strong></p></blockquote>',
            ColoredBlockquotes::resolve('>color foo **bar**')
        );
    }
}
