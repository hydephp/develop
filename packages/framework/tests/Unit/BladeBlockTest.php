<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Processing\BladeBlocks\BladeBlock;
use Hyde\Markdown\Processing\BladeBlocks\BladeComponentBlock;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\BladeBlocks\BladeBlock::class)]
class BladeBlockTest extends UnitTestCase
{
    public function testSignatureIsAnHtmlComment()
    {
        $block = new StubBladeBlock('content');

        $this->assertMatchesRegularExpression('/^<!-- HYDE\[BladeBlock\][0-9a-f]{64} -->$/', $block->signature);
    }

    public function testCompileWrapsRenderOutputInBladeBlockDiv()
    {
        $block = new StubBladeBlock('content');

        $this->assertSame('<div class="blade-block not-prose">STUB</div>', $block->compile());
    }

    public function testIdenticalContentProducesDistinctSignatures()
    {
        $first = new StubBladeBlock('same content');
        $second = new StubBladeBlock('same content');

        $this->assertNotSame($first->signature, $second->signature);
    }

    public function testDifferentContentProducesDistinctSignatures()
    {
        $first = new StubBladeBlock('content A');
        $second = new StubBladeBlock('content B');

        $this->assertNotSame($first->signature, $second->signature);
    }

    public function testComponentBlockAddsNameToHashableContent()
    {
        $first = new BladeComponentBlock('foo: bar', 'component-one');
        $second = new BladeComponentBlock('foo: bar', 'component-two');

        $this->assertNotSame($first->signature, $second->signature);
    }
}

class StubBladeBlock extends BladeBlock
{
    protected function render(): string
    {
        return 'STUB';
    }
}
