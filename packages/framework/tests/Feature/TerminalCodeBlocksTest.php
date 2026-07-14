<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\MarkdownService;
use Hyde\Markdown\Extensions\TerminalExtension;
use Hyde\Markdown\Models\Markdown;
use Hyde\Markdown\Nodes\TerminalBlock;
use Hyde\Markdown\Processing\TerminalBlockRenderer;
use Hyde\Markdown\Processing\TransformTerminalBlocks;
use Hyde\Testing\TestCase;
use Torchlight\Commonmark\BaseExtension;

#[\PHPUnit\Framework\Attributes\CoversClass(TerminalExtension::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalBlock::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TerminalBlockRenderer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TransformTerminalBlocks::class)]
class TerminalCodeBlocksTest extends TestCase
{
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
        $this->assertStringContainsString('<span class="hyde-terminal-question bg-[#89DDFF] text-black">Continue?</span>', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-error bg-[#F07178] text-white">Failed</span>', $html);
    }

    public function testXmlFormattingSupportsNestedTags(): void
    {
        $html = Markdown::render("```terminal xml\n<info>Ready <comment>soon</comment></info>\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-info text-[#C3E88D]">Ready <span class="hyde-terminal-comment text-[#FFCB6B]">soon</span></span>',
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
}
