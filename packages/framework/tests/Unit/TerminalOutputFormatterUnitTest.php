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

    #[\PHPUnit\Framework\Attributes\DataProvider('colorProvider')]
    public function testColorsAreConvertedToSpans(string $color, string $foreground, string $background)
    {
        $this->assertSame("<span class=\"hyde-terminal-fg-$color $foreground\">Text</span>", $this->format("<fg=$color>Text</>"));
        $this->assertSame("<span class=\"hyde-terminal-bg-$color $background\">Text</span>", $this->format("<bg=$color>Text</>"));
    }

    public static function colorProvider(): array
    {
        return [
            ['black', 'text-[#292D3E]', 'bg-[#292D3E]'],
            ['red', 'text-[#F07178]', 'bg-[#F07178]'],
            ['green', 'text-[#C3E88D]', 'bg-[#C3E88D]'],
            ['yellow', 'text-[#FFCB6B]', 'bg-[#FFCB6B]'],
            ['blue', 'text-[#82AAFF]', 'bg-[#82AAFF]'],
            ['magenta', 'text-[#C792EA]', 'bg-[#C792EA]'],
            ['cyan', 'text-[#89DDFF]', 'bg-[#89DDFF]'],
            ['white', 'text-[#D0D0D0]', 'bg-[#D0D0D0]'],
            ['gray', 'text-[#676E95]', 'bg-[#676E95]'],
            ['bright-red', 'text-[#FF8B92]', 'bg-[#FF8B92]'],
            ['bright-green', 'text-[#DDFFA7]', 'bg-[#DDFFA7]'],
            ['bright-yellow', 'text-[#FFE585]', 'bg-[#FFE585]'],
            ['bright-blue', 'text-[#9CC4FF]', 'bg-[#9CC4FF]'],
            ['bright-magenta', 'text-[#E1ACFF]', 'bg-[#E1ACFF]'],
            ['bright-cyan', 'text-[#A3F7FF]', 'bg-[#A3F7FF]'],
            ['bright-white', 'text-[#FFFFFF]', 'bg-[#FFFFFF]'],
        ];
    }

    public function testForegroundAndBackgroundCanBeCombined()
    {
        $this->assertSame(
            '<span class="hyde-terminal-fg-white text-[#D0D0D0] hyde-terminal-bg-green bg-[#C3E88D]"> PASS </span>',
            $this->format('<fg=white;bg=green> PASS </>')
        );
    }

    public function testAttributeNamesAndValuesAreCaseInsensitive()
    {
        $this->assertSame(
            '<span class="hyde-terminal-fg-gray text-[#676E95]">Text</span>',
            $this->format('<FG=Gray>Text</>')
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('optionProvider')]
    public function testOptionsAreConvertedToSpans(string $option, string $classes)
    {
        $this->assertSame("<span class=\"hyde-terminal-$option $classes\">Text</span>", $this->format("<options=$option>Text</>"));
    }

    public static function optionProvider(): array
    {
        return [
            ['bold', 'font-semibold'],
            ['underscore', 'underline'],
            ['strikethrough', 'line-through'],
        ];
    }

    public function testOptionsCanBeCombined()
    {
        $this->assertSame(
            '<span class="hyde-terminal-bold font-semibold hyde-terminal-strikethrough line-through">Text</span>',
            $this->format('<options=bold,strikethrough>Text</>')
        );
    }

    public function testOptionsCanBeCombinedWithColors()
    {
        $this->assertSame(
            '<span class="hyde-terminal-fg-gray text-[#676E95] hyde-terminal-bg-yellow bg-[#FFCB6B] hyde-terminal-strikethrough line-through">Text</span>',
            $this->format('<fg=gray;bg=yellow;options=strikethrough>Text</>')
        );
    }

    public function testUnknownOptionsAreEscaped()
    {
        $this->assertSame('&lt;options=sparkle&gt;Text&lt;/&gt;', $this->format('<options=sparkle>Text</>'));
    }

    public function testUnknownColorsAreEscaped()
    {
        $this->assertSame('&lt;fg=puce&gt;Text&lt;/&gt;', $this->format('<fg=puce>Text</>'));
    }

    public function testUnknownAttributesAreEscaped()
    {
        $this->assertSame('&lt;color=red&gt;Text&lt;/&gt;', $this->format('<color=red>Text</>'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('malformedTagProvider')]
    public function testTagsThatAreNotEntirelyAttributePairsAreEscaped(string $tag)
    {
        $this->assertSame("&lt;$tag&gt;Text&lt;/&gt;", $this->format("<$tag>Text</>"));
    }

    public static function malformedTagProvider(): array
    {
        return [
            'leading word' => ['x;fg=green'],
            'trailing word' => ['fg=green;x'],
            'trailing separator' => ['fg=green;'],
            'separated by spaces' => ['fg=green bg=red'],
        ];
    }

    public function testColorsCanBeNestedWithinNamedStyles()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready in <span class="hyde-terminal-fg-gray text-[#676E95]">0.4s</span></span>',
            $this->format('<info>Ready in <fg=gray>0.4s</></info>')
        );
    }

    public function testNestedTagsComposeWithTheOnesTheyAreNestedIn()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-bold font-semibold">now</span></span>',
            $this->format('<info>Ready <options=bold>now</></info>')
        );
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
