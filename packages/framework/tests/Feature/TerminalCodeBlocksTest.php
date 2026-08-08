<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\MarkdownService;
use Hyde\Markdown\Extensions\TerminalBlockViewModel;
use Hyde\Markdown\Extensions\TerminalExtension;
use Hyde\Markdown\Extensions\TerminalOutputFormatter;
use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use Hyde\Markdown\Extensions\Processing\TerminalBlockRenderer;
use Hyde\Markdown\Extensions\Processing\TransformTerminalBlocks;
use Hyde\Markdown\Models\Markdown;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use Mockery;
use Torchlight\Commonmark\BaseExtension;

#[\PHPUnit\Framework\Attributes\CoversClass(TerminalExtension::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalBlock::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalBlockViewModel::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalOutputFormatter::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalBlockRenderer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TransformTerminalBlocks::class)]
class TerminalCodeBlocksTest extends TestCase
{
    public function testRendererRejectsIncompatibleNodes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TerminalBlockRenderer())->render(
            Mockery::mock(Node::class),
            Mockery::mock(ChildNodeRendererInterface::class),
        );
    }

    public function testTerminalFenceRendersAsTerminal(): void
    {
        $html = Markdown::render("```terminal\n\$ php hyde publish\n\nPublished!\n```");

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<figcaption class="hyde-terminal-header ', $html);
        $this->assertStringContainsString('<pre class="hyde-terminal-body ', $html);
        $this->assertStringContainsString('Published!', $html);
    }

    public function testCommandPromptIsStyledAndExcludedFromSelection(): void
    {
        $html = Markdown::render("```terminal\n\$ php hyde build\n\$VARIABLE\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-command"><span class="hyde-terminal-prompt" aria-hidden="true">$ </span>php hyde build</span>',
            $html,
        );
        $this->assertStringContainsString("\n\$VARIABLE\n", $html);
    }

    public function testXmlModifierRendersFormatterTags(): void
    {
        $html = Markdown::render(
            "```terminal xml\n<info>Ready</info> <comment>Wait</comment> <question>Continue?</question> <error>Failed</error>\n```"
        );

        $this->assertStringContainsString('<span class="hyde-terminal-info">Ready</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-comment">Wait</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-question">Continue?</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-error">Failed</span>', $html);
    }

    public function testXmlModifierRendersColorAndOptionTags(): void
    {
        $html = Markdown::render(
            "```terminal xml\n<fg=gray>Skipped</> <options=strikethrough>Removed</> <fg=white;bg=red;options=bold> ERROR </>\n```"
        );

        $this->assertStringContainsString('<span class="hyde-terminal-fg-gray">Skipped</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-strikethrough">Removed</span>', $html);
        $this->assertStringContainsString(
            '<span class="hyde-terminal-fg-white hyde-terminal-bg-red hyde-terminal-bold"> ERROR </span>',
            $html,
        );
    }

    public function testXmlFormattingRendersPastedConsoleOutput(): void
    {
        $html = Markdown::render(<<<'MARKDOWN'
        ```terminal xml title="Build output"
        $ php hyde build
        <info>Building your static site!</info>
        <fg=gray>Created 12 files in 0.4 seconds</>
        <comment>Note: <options=underscore>--pretty-urls</> is now the default</comment>
        ```
        MARKDOWN);

        $this->assertStringContainsString('<span>Build output</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-prompt" aria-hidden="true">$ </span>php hyde build', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-info">Building your static site!</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-fg-gray">Created 12 files in 0.4 seconds</span>', $html);
        $this->assertStringContainsString(
            '<span class="hyde-terminal-comment">Note: <span class="hyde-terminal-underscore">--pretty-urls</span> is now the default</span>',
            $html,
        );
    }

    public function testXmlFormattingDoesNotCarryOverToTheNextLine(): void
    {
        $html = Markdown::render("```terminal xml\n<fg=gray>Working\nDone</>\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-fg-gray">Working</span>'."\n".'Done&lt;/&gt;',
            $html,
        );
    }

    public function testXmlFormattingSupportsNestedTags(): void
    {
        $html = Markdown::render("```terminal xml\n<info>Ready <comment>soon</comment></info>\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-info">Ready <span class="hyde-terminal-comment">soon</span></span>',
            $html,
        );
    }

    public function testMismatchedTagsAreEscaped(): void
    {
        $html = Markdown::render("```terminal xml\n<info>Ready</comment>\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-info">Ready&lt;/comment&gt;</span>',
            $html,
        );
    }

    public function testTerminalContentsAreAlwaysEscaped(): void
    {
        $html = Markdown::render("```terminal xml\n<script>alert(1)</script> <unknown>text</unknown>\n```");

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<unknown>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;unknown&gt;text&lt;/unknown&gt;', $html);
    }

    public function testFormatterTagsRemainLiteralWithoutXmlModifier(): void
    {
        $html = Markdown::render("```terminal\n<info>Ready</info>\n```");

        $this->assertStringContainsString('&lt;info&gt;Ready&lt;/info&gt;', $html);
        $this->assertStringNotContainsString('hyde-terminal-info', $html);
    }

    public function testUnknownModifiersAreIgnored(): void
    {
        $html = Markdown::render("```terminal future\nOutput\n```");

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('Output', $html);
    }

    public function testUnknownAttributesAreIgnored(): void
    {
        $html = Markdown::render("```terminal future=\"maybe\"\nOutput\n```");

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<span>Terminal</span>', $html);
        $this->assertStringContainsString('Output', $html);
    }

    public function testTerminalWindowUsesTheDefaultTitleWhenNoneIsSet(): void
    {
        $html = Markdown::render("```terminal\n\$ php hyde build\n```");

        $this->assertStringContainsString('<span>Terminal</span>', $html);
    }

    public function testTitleModifierSetsTheTerminalWindowTitle(): void
    {
        $html = Markdown::render("```terminal title=\"Installing Hyde\"\n\$ composer require hyde/framework\n```");

        $this->assertStringContainsString('<span>Installing Hyde</span>', $html);
        $this->assertStringNotContainsString('<span>Terminal</span>', $html);
        $this->assertStringNotContainsString('title=', $html);
    }

    public function testTitleModifierAcceptsSingleQuotes(): void
    {
        $html = Markdown::render("```terminal title='Build output'\nDone!\n```");

        $this->assertStringContainsString('<span>Build output</span>', $html);
    }

    public function testTitleMayContainTheOtherQuoteCharacter(): void
    {
        $this->assertStringContainsString('<span>It&#039;s building</span>',
            Markdown::render("```terminal title=\"It's building\"\nDone!\n```")
        );

        $this->assertStringContainsString('<span>The &quot;build&quot; command</span>',
            Markdown::render("```terminal title='The \"build\" command'\nDone!\n```")
        );
    }

    public function testTitleCasingIsPreserved(): void
    {
        $html = Markdown::render("```terminal TITLE=\"Build Output\"\nDone!\n```");

        $this->assertStringContainsString('<span>Build Output</span>', $html);
    }

    public function testTitleIsEscaped(): void
    {
        $html = Markdown::render("```terminal title=\"<script>alert(1)</script>\"\nDone!\n```");

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testAnEmptyTitleRendersAnEmptyLabel(): void
    {
        $html = Markdown::render("```terminal title=\"\"\nDone!\n```");

        $this->assertStringContainsString('<span></span>', $html);
        $this->assertStringNotContainsString('<span>Terminal</span>', $html);
    }

    public function testModifiersAreOrderIndependent(): void
    {
        $expected = ['<span>Build output</span>', '<span class="hyde-terminal-info">Hyde was installed successfully.</span>'];

        foreach (['xml title="Build output"', 'title="Build output" xml'] as $modifiers) {
            $html = Markdown::render("```terminal $modifiers\n<info>Hyde was installed successfully.</info>\n```");

            foreach ($expected as $needle) {
                $this->assertStringContainsString($needle, $html, "The modifiers [$modifiers] did not render as expected.");
            }
        }
    }

    public function testMalformedTitlesAreRejected(): void
    {
        $malformed = [
            'title=Build',
            'title="Build output',
            'title=\'Build output',
            'title="Build\'',
            'title=',
            'title',
            'title = "Build"',
            'title="Build"xml',
            'xml title=Build',
        ];

        foreach ($malformed as $modifier) {
            try {
                Markdown::render("```terminal $modifier\nDone!\n```");

                $this->fail("The malformed title [$modifier] was not rejected.");
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('Invalid terminal block title', $exception->getMessage());
                $this->assertStringContainsString('Expected syntax like title="My title".', $exception->getMessage());
            }
        }
    }

    public function testModifiersAreNotFoundInsideAnotherToken(): void
    {
        // Modifiers are whitespace separated, so this is one unknown token, not two modifiers
        $html = Markdown::render("```terminal xmlfuture=\"yes\"\n<info>Ready</info>\n```");

        $this->assertStringContainsString('&lt;info&gt;Ready&lt;/info&gt;', $html);
        $this->assertStringNotContainsString('hyde-terminal-info', $html);
    }

    public function testTitlesOnOtherLanguagesDoNotMakeTheBlockATerminal(): void
    {
        $html = Markdown::render("```php title=\"Build\"\necho 'Hello World!';\n```");

        $this->assertStringNotContainsString('hyde-terminal', $html);
        $this->assertStringContainsString('<pre><code class="language-php', $html);
    }

    public function testOrdinaryCodeBlocksAreUnaffected(): void
    {
        $html = Markdown::render("```php\n<h1>Hello</h1>\n```");

        $this->assertStringNotContainsString('hyde-terminal', $html);
        $this->assertStringContainsString('<pre><code class="language-php">&lt;h1&gt;Hello&lt;/h1&gt;', $html);
    }

    public function testTerminalExtensionIsAlwaysEnabled(): void
    {
        $service = new MarkdownService('Text');
        $service->parse();

        $this->assertContains(TerminalExtension::class, $service->getExtensions());
    }

    public function testTerminalBlocksAreNotSubmittedToTorchlight(): void
    {
        BaseExtension::$torchlightBlocks = [];

        $service = new MarkdownService("```terminal\n\$ php hyde build\n```");
        $html = $service->addFeature('torchlight')->parse();

        $this->assertSame([], BaseExtension::$torchlightBlocks);
        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
    }

    public function testParsedBlocksCarryTheViewModelTheyWereParsedInto(): void
    {
        $document = new Document();

        $fence = new FencedCode(3, '`', 0);
        $fence->setInfo('terminal xml title="Build output"');
        $fence->setLiteral('$ php hyde build');

        $document->appendChild($fence);

        (new TransformTerminalBlocks())(new DocumentParsedEvent($document));

        /** @var TerminalBlock $node */
        $node = $document->firstChild();

        $this->assertInstanceOf(TerminalBlock::class, $node);
        $this->assertInstanceOf(TerminalBlockViewModel::class, $node->viewModel);

        $this->assertSame('$ php hyde build', $node->viewModel->literal);
        $this->assertSame('Build output', $node->viewModel->title);
        $this->assertTrue($node->viewModel->usesFormatting);
    }

    public function testViewModelRendersTheTerminalView(): void
    {
        $html = (new TerminalBlockViewModel('$ php hyde build', 'Build output'))->render();

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<span>Build output</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-prompt" aria-hidden="true">$ </span>php hyde build', $html);
    }

    public function testViewModelGivesTheViewTheSameDataAsBefore(): void
    {
        $viewModel = new TerminalBlockViewModel('$ php hyde build', 'Build output');

        $this->assertSame(['contents', 'title'], array_keys((fn (): array => $this->viewData())->call($viewModel)));
    }

    public function testViewModelContentsAreFinishedMarkup(): void
    {
        $viewModel = new TerminalBlockViewModel('<info>Ready</info> <b>Bold</b>', usesFormatting: true);

        $this->assertSame(
            '<span class="hyde-terminal-info">Ready</span> &lt;b&gt;Bold&lt;/b&gt;',
            $viewModel->contents
        );
    }
}
