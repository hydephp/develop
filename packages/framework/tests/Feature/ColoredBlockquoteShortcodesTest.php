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
    public function testSignature()
    {
        $this->assertSame('>', ColoredBlockquotes::signature());
    }

    public function testSignatures()
    {
        $this->assertSame(
            ['>danger', '>info', '>success', '>warning'],
            ColoredBlockquotes::getSignatures()
        );
    }

    public function testResolveMethod()
    {
        $this->assertSame(
            '<blockquote class="info"><p>foo</p></blockquote>',
            ColoredBlockquotes::resolve('>info foo')
        );
    }

    public function testCanUseMarkdownWithinBlockquote()
    {
        $this->assertSame(
            '<blockquote class="info"><p>foo <strong>bar</strong></p></blockquote>',
            ColoredBlockquotes::resolve('>info foo **bar**')
        );
    }

    public function testWithUnrelatedClass()
    {
        $this->assertSame(
            '>foo foo',
            ColoredBlockquotes::resolve('>foo foo')
        );
    }
}
