<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Processing\BladeBlocks\BladeBlockExtractor;
use Hyde\Markdown\Processing\BladeBlocks\BladeComponentBlock;
use Hyde\Markdown\Processing\BladeBlocks\BladeRenderBlock;
use Hyde\Testing\UnitTestCase;
use InvalidArgumentException;

use function array_key_first;
use function array_unique;
use function array_values;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\BladeBlocks\BladeBlockExtractor::class)]
class BladeBlockExtractorTest extends UnitTestCase
{
    public function testReturnsEmptyBlocksAndUnchangedMarkdownWhenNoFences()
    {
        [$blocks, $markdown] = (new BladeBlockExtractor())->handle("# Title\n\nBody");

        $this->assertSame([], $blocks);
        $this->assertSame("# Title\n\nBody", $markdown);
    }

    public function testLeavesOrdinaryCodeBlocksUntouched()
    {
        $markdown = "```php\n<h1>Hello</h1>\n```";

        [$blocks, $result] = (new BladeBlockExtractor())->handle($markdown);

        $this->assertSame([], $blocks);
        $this->assertSame($markdown, $result);
    }

    public function testLeavesBareBladeBlockUntouched()
    {
        $markdown = "```blade\n{{ \"Not executed\" }}\n```";

        [$blocks, $result] = (new BladeBlockExtractor())->handle($markdown);

        $this->assertSame([], $blocks);
        $this->assertSame($markdown, $result);
    }

    public function testExtractsRenderBlock()
    {
        [$blocks] = (new BladeBlockExtractor())->handle("```blade render\n{{ \"Hi\" }}\n```");

        $this->assertCount(1, $blocks);
        $this->assertInstanceOf(BladeRenderBlock::class, array_values($blocks)[0]);
    }

    public function testExtractsComponentBlock()
    {
        [$blocks] = (new BladeBlockExtractor())->handle("```blade component(x)\ncontent\n```");

        $this->assertCount(1, $blocks);
        $this->assertInstanceOf(BladeComponentBlock::class, array_values($blocks)[0]);
    }

    public function testReplacesExtractedBlockWithSignatureComment()
    {
        [, $markdown] = (new BladeBlockExtractor())->handle("```blade render\n{{ \"Hi\" }}\n```");

        $this->assertMatchesRegularExpression('/^<!-- HYDE\[BladeBlock\][0-9a-f]{64} -->$/m', $markdown);
        $this->assertStringNotContainsString('{{ "Hi" }}', $markdown);
    }

    public function testEqualBlocksProduceDistinctSignatures()
    {
        [$blocks] = (new BladeBlockExtractor())->handle(
            "```blade render\n{{ \"X\" }}\n```\n\n```blade render\n{{ \"X\" }}\n```"
        );

        $this->assertCount(2, $blocks);
        $this->assertCount(2, array_unique(array_keys($blocks)));
    }

    public function testCarriageReturnLineEndingsAreNormalized()
    {
        $markdown = "Foo\r\n```blade render\r\n{{ \"Hi\" }}\r\n```\r\nBar";

        [$blocks] = (new BladeBlockExtractor())->handle($markdown);

        $this->assertCount(1, $blocks);
    }

    public function testTildeFenceIsSupported()
    {
        [$blocks] = (new BladeBlockExtractor())->handle("~~~blade render\n{{ \"Hi\" }}\n~~~");

        $this->assertCount(1, $blocks);
        $this->assertInstanceOf(BladeRenderBlock::class, array_values($blocks)[0]);
    }

    public function testBacktickInInfoStringIsIgnored()
    {
        $markdown = "```foo`bar\ncontent\n```";

        [$blocks, $result] = (new BladeBlockExtractor())->handle($markdown);

        $this->assertSame([], $blocks);
        $this->assertSame($markdown, $result);
    }

    public function testFourBacktickFenceDoesNotExtractInnerTripleFence()
    {
        $markdown = "````markdown\n```blade render\n{{ \"Hi\" }}\n```\n````";

        [$blocks, $result] = (new BladeBlockExtractor())->handle($markdown);

        $this->assertSame([], $blocks);
        $this->assertSame($markdown, $result);
    }

    public function testLongerClosingFenceRequiredToClose()
    {
        // The shorter ``` run inside the body must not close the ```` fence.
        $markdown = "````blade render\n{{ \"Hi\" }}\n```\nmore\n````";

        [$blocks, $result] = (new BladeBlockExtractor())->handle($markdown);

        $this->assertCount(1, $blocks);
        $this->assertInstanceOf(BladeRenderBlock::class, array_values($blocks)[0]);
        $this->assertStringNotContainsString('more', $result);
    }

    public function testClosingFenceMustMatchOpeningCharacter()
    {
        [$blocks, $result] = (new BladeBlockExtractor())->handle("```blade render\n{{ \"Hi\" }}\n~~~");

        $this->assertCount(1, $blocks);
        $this->assertStringNotContainsString('~~~', $result);
    }

    public function testUnterminatedFenceExtractsToEndOfInput()
    {
        [$blocks, $result] = (new BladeBlockExtractor())->handle("```blade render\n{{ \"Hi\" }}");

        $this->assertCount(1, $blocks);
        $this->assertSame(array_key_first($blocks), $result);
    }

    public function testThrowsOnUnknownDirective()
    {
        $this->expectException(InvalidArgumentException::class);

        (new BladeBlockExtractor())->handle("```blade foo\ncontent\n```");
    }

    public function testThrowsOnComponentWithoutName()
    {
        $this->expectException(InvalidArgumentException::class);

        (new BladeBlockExtractor())->handle("```blade component\ncontent\n```");
    }

    public function testThrowsOnComponentWithEmptyParentheses()
    {
        $this->expectException(InvalidArgumentException::class);

        (new BladeBlockExtractor())->handle("```blade component()\ncontent\n```");
    }
}
