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
    }

    public function testCanConstructWithAllArguments()
    {
        $model = new TerminalBlockViewModel('Output', 'Console');

        $this->assertSame('Output', $model->literal);
        $this->assertSame('Console', $model->title);
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
            '<span class="hyde-terminal-command"><span class="hyde-terminal-prompt" aria-hidden="true">$ </span>php hyde build</span>',
            (new TerminalBlockViewModel('$ php hyde build'))->contents
        );
    }

    public function testCommandPromptCanBeIndentedWithTabs()
    {
        $this->assertSame(
            "<span class=\"hyde-terminal-command\"><span class=\"hyde-terminal-prompt\" aria-hidden=\"true\">\$\t</span>php hyde build</span>",
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
            '<span class="hyde-terminal-command"><span class="hyde-terminal-prompt" aria-hidden="true">$ </span>php hyde build</span>'."\nDone!",
            (new TerminalBlockViewModel("\$ php hyde build\nDone!"))->contents
        );
    }

    public function testFormatterTagsAreConvertedToSpans()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info">Ready</span>',
            (new TerminalBlockViewModel('<info>Ready</info>'))->contents
        );
    }

    public function testFormattingIsAppliedWithinCommandLines()
    {
        $this->assertSame(
            '<span class="hyde-terminal-command"><span class="hyde-terminal-prompt" aria-hidden="true">$ </span>php hyde build <span class="hyde-terminal-comment">--force</span></span>',
            (new TerminalBlockViewModel('$ php hyde build <comment>--force</comment>'))->contents
        );
    }

    public function testFormatterTagsDoNotSpanMultipleLines()
    {
        $this->assertSame(
            '<span class="hyde-terminal-info">Ready</span>'."\n".'Done&lt;/info&gt;',
            (new TerminalBlockViewModel("<info>Ready\nDone</info>"))->contents
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

        $this->assertStringContainsString('<span class="hyde-terminal-prompt" aria-hidden="true">$ </span>php hyde build', $html);
    }
}
