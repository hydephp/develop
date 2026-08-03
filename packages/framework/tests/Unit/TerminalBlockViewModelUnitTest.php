<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Extensions\TerminalBlockViewModel;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\UsesRealBladeInUnitTests;

/**
 * @see \Hyde\Framework\Testing\Feature\TerminalCodeBlocksTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Extensions\TerminalBlockViewModel::class)]
class TerminalBlockViewModelUnitTest extends UnitTestCase
{
    use UsesRealBladeInUnitTests;

    protected static bool $needsKernel = true;

    protected function setUp(): void
    {
        $this->createRealBladeCompilerEnvironment();
    }

    public function testCanConstructWithOnlyLiteral()
    {
        $model = new TerminalBlockViewModel('Output');

        $this->assertSame('Output', $model->literal);
        $this->assertNull($model->title);
        $this->assertFalse($model->usesFormatting);
    }

    public function testCanConstructWithAllArguments()
    {
        $model = new TerminalBlockViewModel('Output', 'Console', true);

        $this->assertSame('Output', $model->literal);
        $this->assertSame('Console', $model->title);
        $this->assertTrue($model->usesFormatting);
    }

    public function testContentsAreFormattedOnConstruction()
    {
        $this->assertSame('Output', (new TerminalBlockViewModel('Output'))->contents);
    }

    public function testEmptyLiteralMakesEmptyContents()
    {
        $this->assertSame('', (new TerminalBlockViewModel(''))->contents);
    }

    public function testContentsAreEscaped()
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', (new TerminalBlockViewModel('<script>alert(1)</script>'))->contents);
    }

    public function testLineBreaksArePreserved()
    {
        $this->assertSame("Foo\nBar\n\nBaz", (new TerminalBlockViewModel("Foo\nBar\n\nBaz"))->contents);
    }

    public function testCommandPromptIsWrappedInSpans()
    {
        $this->assertSame(
            '<span class="hyde-terminal-command text-[#C3E88D]"><span class="hyde-terminal-prompt select-none" aria-hidden="true">$ </span>php hyde build</span>',
            (new TerminalBlockViewModel('$ php hyde build'))->contents
        );
    }

    public function testCommandPromptCanBeIndentedWithTabs()
    {
        $this->assertSame(
            "<span class=\"hyde-terminal-command text-[#C3E88D]\"><span class=\"hyde-terminal-prompt select-none\" aria-hidden=\"true\">\$\t</span>php hyde build</span>",
            (new TerminalBlockViewModel("\$\tphp hyde build"))->contents
        );
    }

    public function testDollarSignWithoutTrailingWhitespaceIsNotACommand()
    {
        $this->assertSame('$VARIABLE', (new TerminalBlockViewModel('$VARIABLE'))->contents);
    }

    public function testCommandArgumentsAreEscaped()
    {
        $this->assertStringContainsString('php hyde build &amp;&amp; echo &quot;done&quot;',
            (new TerminalBlockViewModel('$ php hyde build && echo "done"'))->contents
        );
    }

    public function testOnlyCommandLinesAreWrapped()
    {
        $this->assertSame(
            '<span class="hyde-terminal-command text-[#C3E88D]"><span class="hyde-terminal-prompt select-none" aria-hidden="true">$ </span>php hyde build</span>'."\nDone!",
            (new TerminalBlockViewModel("\$ php hyde build\nDone!"))->contents
        );
    }

    public function testFormatterTagsAreEscapedWithoutFormatting()
    {
        $this->assertSame('&lt;info&gt;Ready&lt;/info&gt;', (new TerminalBlockViewModel('<info>Ready</info>'))->contents);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatterTagProvider')]
    public function testSymfonyFormatterTagsAreConvertedToSpans(string $literal, string $expected)
    {
        $this->assertSame($expected, (new TerminalBlockViewModel($literal, null, true))->contents);
    }

    public static function formatterTagProvider(): array
    {
        return [
            'info' => ['<info>Ready</info>', '<span class="hyde-terminal-info text-[#C3E88D]">Ready</span>'],
            'comment' => ['<comment>Wait</comment>', '<span class="hyde-terminal-comment text-[#FFCB6B]">Wait</span>'],
            'question' => ['<question>Continue?</question>', '<span class="hyde-terminal-question text-[#89DDFF]">Continue?</span>'],
            'error' => ['<error>Failed</error>', '<span class="hyde-terminal-error font-semibold text-[#F07178]">Failed</span>'],
        ];
    }

    public function testSymfonyFormatterTagsCanBeNested()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-comment text-[#FFCB6B]">soon</span></span>',
            (new TerminalBlockViewModel('<info>Ready <comment>soon</comment></info>', null, true))->contents
        );
    }

    public function testUnclosedSymfonyFormatterTagsAreClosedAtTheEndOfTheLine()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-comment text-[#FFCB6B]">soon</span></span>',
            (new TerminalBlockViewModel('<info>Ready <comment>soon', null, true))->contents
        );
    }

    public function testMismatchedSymfonyFormatterTagsAreEscaped()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready&lt;/comment&gt;</span>',
            (new TerminalBlockViewModel('<info>Ready</comment>', null, true))->contents
        );
    }

    public function testUnopenedSymfonyFormatterTagsAreEscaped()
    {
        $this->assertSame('Ready&lt;/info&gt;', (new TerminalBlockViewModel('Ready</info>', null, true))->contents);
    }

    public function testUnknownTagsAreEscapedWithFormatting()
    {
        $this->assertSame('&lt;unknown&gt;text&lt;/unknown&gt;', (new TerminalBlockViewModel('<unknown>text</unknown>', null, true))->contents);
    }

    public function testFormattingIsAppliedWithinCommandLines()
    {
        $this->assertSame(
            '<span class="hyde-terminal-command text-[#C3E88D]"><span class="hyde-terminal-prompt select-none" aria-hidden="true">$ </span>php hyde build <span class="hyde-terminal-comment text-[#FFCB6B]">--force</span></span>',
            (new TerminalBlockViewModel('$ php hyde build <comment>--force</comment>', null, true))->contents
        );
    }

    public function testSymfonyFormatterTagsDoNotSpanMultipleLines()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready</span>'."\n".'Done&lt;/info&gt;',
            (new TerminalBlockViewModel("<info>Ready\nDone</info>", null, true))->contents
        );
    }

    public function testRenderReturnsTerminalComponent()
    {
        $html = (new TerminalBlockViewModel('Output'))->render();

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<pre class="hyde-terminal-body ', $html);
        $this->assertStringContainsString('Output', $html);
    }

    public function testRenderUsesDefaultTitleWhenNoneIsSet()
    {
        $this->assertStringContainsString('<span>Terminal</span>', (new TerminalBlockViewModel('Output'))->render());
    }

    public function testRenderUsesGivenTitle()
    {
        $this->assertStringContainsString('<span>Console</span>', (new TerminalBlockViewModel('Output', 'Console'))->render());
    }

    public function testRenderEscapesTitle()
    {
        $html = (new TerminalBlockViewModel('Output', '<script>alert(1)</script>'))->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testRenderDoesNotEscapeFormattedContents()
    {
        $html = (new TerminalBlockViewModel('$ php hyde build'))->render();

        $this->assertStringContainsString('<span class="hyde-terminal-prompt select-none" aria-hidden="true">$ </span>php hyde build', $html);
    }
}
