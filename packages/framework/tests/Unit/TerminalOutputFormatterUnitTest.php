<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Extensions\TerminalOutputFormatter;
use Hyde\Testing\UnitTestCase;

/**
 * @see \Hyde\Framework\Testing\Feature\TerminalCodeBlocksTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalOutputFormatter::class)]
class TerminalOutputFormatterUnitTest extends UnitTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('styleProvider')]
    public function testNamedStylesAreConvertedToSpans(string $text, string $expected)
    {
        $this->assertSame($expected, $this->format($text));
    }

    public static function styleProvider(): array
    {
        return [
            'info' => ['<info>Ready</info>', '<span class="hyde-terminal-info text-[#C3E88D]">Ready</span>'],
            'comment' => ['<comment>Wait</comment>', '<span class="hyde-terminal-comment text-[#FFCB6B]">Wait</span>'],
            'question' => ['<question>Continue?</question>', '<span class="hyde-terminal-question text-[#89DDFF]">Continue?</span>'],
            'error' => ['<error>Failed</error>', '<span class="hyde-terminal-error font-semibold text-[#F07178]">Failed</span>'],
        ];
    }

    public function testTagsCanBeNested()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-comment text-[#FFCB6B]">soon</span></span>',
            $this->format('<info>Ready <comment>soon</comment></info>')
        );
    }

    public function testUnclosedTagsAreClosedAtTheEnd()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-comment text-[#FFCB6B]">soon</span></span>',
            $this->format('<info>Ready <comment>soon')
        );
    }

    public function testShorthandClosingTagClosesTheMostRecentTag()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-comment text-[#FFCB6B]">soon</span> now</span>',
            $this->format('<info>Ready <comment>soon</> now</>')
        );
    }

    public function testShorthandClosingTagIsEscapedWhenNothingIsOpen()
    {
        $this->assertSame('Ready&lt;/&gt;', $this->format('Ready</>'));
    }

    public function testMismatchedTagsAreEscaped()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready&lt;/comment&gt;</span>',
            $this->format('<info>Ready</comment>')
        );
    }

    public function testUnopenedTagsAreEscaped()
    {
        $this->assertSame('Ready&lt;/info&gt;', $this->format('Ready</info>'));
    }

    public function testUnknownTagsAreEscaped()
    {
        $this->assertSame('&lt;unknown&gt;text&lt;/unknown&gt;', $this->format('<unknown>text</unknown>'));
    }

    public function testTextWithoutTagsIsEscaped()
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $this->format('<script>alert(1)</script>'));
    }

    protected function format(string $text): string
    {
        return (new TerminalOutputFormatter())->format($text);
    }
}
