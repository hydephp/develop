<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Processing\ColoredBlockquotes;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\UsesRealBladeInUnitTests;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\ColoredBlockquotes::class)]
class ColoredBlockquoteShortcodesTest extends UnitTestCase
{
    use UsesRealBladeInUnitTests;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        $this->mockRender();
        $this->createRealBladeCompilerEnvironment();
    }

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
        $this->assertSame(<<<'HTML'
            <blockquote class="border-blue-500">
                <p>foo</p>
            </blockquote>
            HTML, ColoredBlockquotes::resolve('>info foo')
        );
    }

    public function testCanUseMarkdownWithinBlockquote()
    {
        $this->assertSame(
            <<<'HTML'
            <blockquote class="border-blue-500">
                <p>foo <strong>bar</strong></p>
            </blockquote>
            HTML, ColoredBlockquotes::resolve('>info foo **bar**')
        );
    }

    public function testWithUnrelatedClass()
    {
        $this->assertSame(
            '>foo foo',
            ColoredBlockquotes::resolve('>foo foo')
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('blockquoteProvider')]
    public function testItResolvesAllShortcodes(string $input, string $expectedOutput)
    {
        $this->assertSame($expectedOutput, ColoredBlockquotes::resolve($input));
    }

    public static function blockquoteProvider(): \Iterator
    {
        yield [
            '>danger This is a danger blockquote',
            <<<'HTML'
                <blockquote class="border-red-600">
                    <p>This is a danger blockquote</p>
                </blockquote>
                HTML,
        ];
        yield [
            '>info This is an info blockquote',
            <<<'HTML'
                <blockquote class="border-blue-500">
                    <p>This is an info blockquote</p>
                </blockquote>
                HTML,
        ];
        yield [
            '>success This is a success blockquote',
            <<<'HTML'
                <blockquote class="border-green-500">
                    <p>This is a success blockquote</p>
                </blockquote>
                HTML,
        ];
        yield [
            '>warning This is a warning blockquote',
            <<<'HTML'
                <blockquote class="border-amber-500">
                    <p>This is a warning blockquote</p>
                </blockquote>
                HTML,
        ];
    }
}
