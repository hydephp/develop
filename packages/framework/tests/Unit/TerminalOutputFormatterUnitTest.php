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
            'info' => ['<info>Ready</info>', '<span class="hyde-terminal-info">Ready</span>'],
            'comment' => ['<comment>Wait</comment>', '<span class="hyde-terminal-comment">Wait</span>'],
            'question' => ['<question>Continue?</question>', '<span class="hyde-terminal-question">Continue?</span>'],
            'error' => ['<error>Failed</error>', '<span class="hyde-terminal-error">Failed</span>'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('colorProvider')]
    public function testColorsAreConvertedToSpans(string $color)
    {
        $this->assertSame("<span class=\"hyde-terminal-fg-$color\">Text</span>", $this->format("<fg=$color>Text</>"));
        $this->assertSame("<span class=\"hyde-terminal-bg-$color\">Text</span>", $this->format("<bg=$color>Text</>"));
    }

    public static function colorProvider(): array
    {
        return [
            ['black'], ['red'], ['green'], ['yellow'], ['blue'], ['magenta'], ['cyan'], ['white'], ['gray'],
            ['bright-red'], ['bright-green'], ['bright-yellow'], ['bright-blue'],
            ['bright-magenta'], ['bright-cyan'], ['bright-white'],
        ];
    }

    public function testForegroundAndBackgroundCanBeCombined()
    {
        $this->assertSame(
            '<span class="hyde-terminal-fg-white hyde-terminal-bg-green"> PASS </span>',
            $this->format('<fg=white;bg=green> PASS </>')
        );
    }

    public function testAttributeNamesAndValuesAreCaseInsensitive()
    {
        $this->assertSame(
            '<span class="hyde-terminal-fg-gray">Text</span>',
            $this->format('<FG=Gray>Text</>')
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('optionProvider')]
    public function testOptionsAreConvertedToSpans(string $option)
    {
        $this->assertSame("<span class=\"hyde-terminal-$option\">Text</span>", $this->format("<options=$option>Text</>"));
    }

    public static function optionProvider(): array
    {
        return [['bold'], ['underscore'], ['strikethrough']];
    }

    public function testOptionsCanBeCombined()
    {
        $this->assertSame(
            '<span class="hyde-terminal-bold hyde-terminal-strikethrough">Text</span>',
            $this->format('<options=bold,strikethrough>Text</>')
        );
    }

    public function testOptionsCanBeCombinedWithColors()
    {
        $this->assertSame(
            '<span class="hyde-terminal-fg-gray hyde-terminal-bg-yellow hyde-terminal-strikethrough">Text</span>',
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

    public function testTagsCanBeNested()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info">Ready <span class="hyde-terminal-comment">soon</span></span>',
            $this->format('<info>Ready <comment>soon</comment></info>')
        );
    }

    public function testNestedTagsComposeWithTheOnesTheyAreNestedIn()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info">Ready in <span class="hyde-terminal-fg-gray">0.4s</span></span>',
            $this->format('<info>Ready in <fg=gray>0.4s</></info>')
        );
    }

    public function testUnclosedTagsAreClosedAtTheEnd()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info">Ready <span class="hyde-terminal-comment">soon</span></span>',
            $this->format('<info>Ready <comment>soon')
        );
    }

    public function testShorthandClosingTagClosesTheMostRecentTag()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info">Ready <span class="hyde-terminal-comment">soon</span> now</span>',
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
            '<span class="hyde-terminal-info">Ready&lt;/comment&gt;</span>',
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
