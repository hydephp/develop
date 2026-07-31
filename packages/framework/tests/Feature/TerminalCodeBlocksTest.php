<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\MarkdownService;
use Hyde\Framework\Views\Components\TerminalBlockComponent;
use Hyde\Markdown\Extensions\TerminalExtension;
use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use Hyde\Markdown\Extensions\Processing\TerminalBlockRenderer;
use Hyde\Markdown\Extensions\Processing\TransformTerminalBlocks;
use Hyde\Markdown\Models\Markdown;
use Hyde\Testing\TestCase;
use Illuminate\Support\HtmlString;
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
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalBlockComponent::class)]
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
            '<span class="hyde-terminal-command text-[#C3E88D]"><span class="hyde-terminal-prompt select-none" aria-hidden="true">$ </span>php hyde build</span>',
            $html,
        );
        $this->assertStringContainsString("\n\$VARIABLE\n", $html);
    }

    public function testXmlModifierRendersSymfonyFormatterTags(): void
    {
        $html = Markdown::render(
            "```terminal xml\n<info>Ready</info> <comment>Wait</comment> <question>Continue?</question> <error>Failed</error>\n```"
        );

        $this->assertStringContainsString('<span class="hyde-terminal-info text-[#C3E88D]">Ready</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-comment text-[#FFCB6B]">Wait</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-question text-[#89DDFF]">Continue?</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-error font-semibold text-[#F07178]">Failed</span>', $html);
    }

    public function testXmlFormattingSupportsNestedTags(): void
    {
        $html = Markdown::render("```terminal xml\n<info>Ready <comment>soon</comment></info>\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-comment text-[#FFCB6B]">soon</span></span>',
            $html,
        );
    }

    public function testMismatchedTagsAreEscaped(): void
    {
        $html = Markdown::render("```terminal xml\n<info>Ready</comment>\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready&lt;/comment&gt;</span>',
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
        $expected = ['<span>Build output</span>', '<span class="hyde-terminal-info text-[#C3E88D]">Hyde was installed successfully.</span>'];

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

    public function testTitlesAreOnlyParsedForTerminalBlocks(): void
    {
        $html = Markdown::render("```php title=Build\necho 'Hello World!';\n```");

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

    public function testParsedBlocksCarryTheComponentTheyWereParsedInto(): void
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
        $this->assertInstanceOf(TerminalBlockComponent::class, $node->component);

        $this->assertSame('$ php hyde build', $node->component->literal);
        $this->assertSame('Build output', $node->component->title);
        $this->assertTrue($node->component->usesSymfonyFormatting);
    }

    public function testComponentCanBeConstructedAndRenderedWithoutMarkdown(): void
    {
        $html = (new TerminalBlockComponent('$ php hyde build', 'Building the site'))->toHtml();

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<span>Building the site</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-prompt select-none" aria-hidden="true">$ </span>php hyde build', $html);
    }

    public function testComponentRendersTheSameHtmlAsTheEquivalentMarkdownBlock(): void
    {
        // The literal CommonMark hands the component keeps the trailing newline from the fence
        $component = new TerminalBlockComponent("<info>Hyde was installed successfully.</info>\n", 'Build output', true);

        $this->assertSame(
            trim(Markdown::render("```terminal xml title=\"Build output\"\n<info>Hyde was installed successfully.</info>\n```")),
            trim($component->toHtml())
        );
    }

    public function testComponentPropertiesMakeUpTheViewData(): void
    {
        $component = new TerminalBlockComponent('$ php hyde build', 'Build output', true);

        $data = $component->data();

        $this->assertSame('$ php hyde build', $data['literal']);
        $this->assertSame('Build output', $data['title']);
        $this->assertTrue($data['usesSymfonyFormatting']);
        $this->assertSame($component->contents, $data['contents']);
    }

    public function testComponentContentsAreAnHtmlStringOfFinishedMarkup(): void
    {
        $component = new TerminalBlockComponent('<info>Ready</info> <b>Bold</b>', usesSymfonyFormatting: true);

        $this->assertInstanceOf(HtmlString::class, $component->contents);

        $this->assertSame(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready</span> &lt;b&gt;Bold&lt;/b&gt;',
            $component->contents->toHtml()
        );
    }

    public function testComponentTitleDefaultsToNullSoTheViewCanDecideTheFallback(): void
    {
        $this->assertNull((new TerminalBlockComponent('$ php hyde build'))->title);

        $this->assertStringContainsString('<span>Terminal</span>', (new TerminalBlockComponent('$ php hyde build'))->toHtml());
    }

    public function testComponentAttributesAreMergedIntoTheTerminalWindow(): void
    {
        $html = (new TerminalBlockComponent('$ php hyde build'))->withAttributes(['id' => 'build-log', 'class' => 'shadow-lg'])->toHtml();

        $this->assertStringContainsString('id="build-log"', $html);
        $this->assertStringContainsString('shadow-lg', $html);
        $this->assertStringContainsString('hyde-terminal', $html);
    }
}
