<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Modules\Markdown\Shortcodes\ColoredBlockquotes;
use Hyde\Testing\TestCase;

/**
 * Class ColoredBlockquoteShortcodesTest.
 *
 * @covers \Hyde\Framework\Modules\Markdown\Shortcodes\ColoredBlockquotes
 */
class ColoredBlockquoteShortcodesTest extends TestCase
{
    public function test_resolve_method()
    {
        $this->assertEquals('<blockquote class="color">foo</blockquote>',
            ColoredBlockquotes::resolve('>color foo'));
    }

    public function test_get_method()
    {
        $this->assertCount(4, ColoredBlockquotes::get());
        $this->assertContainsOnlyInstancesOf(ColoredBlockquotes::class,
            ColoredBlockquotes::get());
    }
}
