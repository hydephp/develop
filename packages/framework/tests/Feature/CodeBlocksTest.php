<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use ArrayObject;
use GuzzleHttp\Promise\PromiseInterface;
use Hyde\Framework\Services\MarkdownService;
use Hyde\Markdown\Extensions\CodeBlockViewModel;
use Hyde\Markdown\Extensions\Nodes\CodeBlock;
use Hyde\Markdown\Extensions\Processing\CodeBlockRenderer;
use Hyde\Markdown\Extensions\Processing\PrepareCodeBlocks;
use Hyde\Markdown\Extensions\Processing\WrapCodeBlocks;
use Hyde\Markdown\Models\Markdown;
use Hyde\Testing\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Event\DocumentPreRenderEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Torchlight\Commonmark\BaseExtension;

use function array_map;
use function sprintf;
use function trim;
use function md5;

use const PHP_INT_MAX;

#[\PHPUnit\Framework\Attributes\CoversClass(CodeBlockViewModel::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(CodeBlockRenderer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(PrepareCodeBlocks::class)]
class CodeBlocksTest extends TestCase
{
    /** @var array<int, array<string, mixed>> The payloads sent to the Torchlight API. */
    protected array $torchlightRequests = [];

    public function testFencedCodeBlocksAreRenderedThroughTheCodeBlockView(): void
    {
        $html = Markdown::render("```php\necho 'Hello World!';\n```");

        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
        $this->assertStringContainsString('<pre><code class="language-php">', $html);
    }

    public function testCodeBlockWithoutLanguageIsRenderedThroughTheView(): void
    {
        $html = Markdown::render("```\nHello World!\n```");

        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
        $this->assertStringContainsString('<pre><code>Hello World!', $html);
    }

    public function testCodeBlockWithoutLabelRendersNoLabel(): void
    {
        $this->assertStringNotContainsString('hyde-code-block-label', Markdown::render("```php\necho 'Hi';\n```"));
    }

    public function testFilepathCommentIsNoLongerRecognized(): void
    {
        $html = Markdown::render("```php\n// filepath: app/Model.php\n\necho 'Hi';\n```");

        // The comment is left in the code exactly as written, blank line and all
        $this->assertStringNotContainsString('hyde-code-block-label', $html);
        $this->assertStringContainsString("<pre><code class=\"language-php\">// filepath: app/Model.php\n\necho 'Hi';", $html);
    }

    public function testIndentedCodeBlocksAreNotAffected(): void
    {
        $html = Markdown::render("    echo 'Hello World!';");

        $this->assertStringNotContainsString('hyde-code-block', $html);
    }

    public function testTerminalBlocksAreNotRenderedAsCodeBlocks(): void
    {
        $html = Markdown::render("```terminal\n\$ php hyde build\n```");

        $this->assertStringNotContainsString('hyde-code-block', $html);
        $this->assertStringContainsString('hyde-terminal', $html);
    }

    public function testTitleModifierBecomesALabel(): void
    {
        $html = Markdown::render("```php title=\"app/Model.php\"\necho 'Hi';\n```");

        $this->assertStringContainsString('<figure class="hyde-code-block ', $html);
        $this->assertStringContainsString('<figcaption class="hyde-code-block-label ', $html);
        $this->assertStringContainsString('>app/Model.php</figcaption>', $html);
    }

    public function testLegacyBadgeLabelStyleCanBeConfigured(): void
    {
        config(['markdown.code_block_label_style' => 'badge']);

        $html = Markdown::render("```php title=\"app/Model.php\"\necho 'Hi';\n```");

        $this->assertStringContainsString('<figure class="hyde-code-block ', $html);
        $this->assertStringContainsString('<figcaption class="hyde-code-block-label ', $html);
        $this->assertStringContainsString('>app/Model.php</figcaption>', $html);
    }

    public function testUnsupportedLabelStyleThrows(): void
    {
        config(['markdown.code_block_label_style' => 'unsupported']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid code block label style [unsupported]. Supported styles are [header] and [badge].');

        Markdown::render("```php title=\"app/Model.php\"\necho 'Hi';\n```");
    }

    public function testTitleModifierAcceptsSingleQuotes(): void
    {
        $html = Markdown::render("```php title='app/Model.php'\necho 'Hi';\n```");

        $this->assertStringContainsString('>app/Model.php</figcaption>', $html);
    }

    public function testTitleModifierMayContainWhitespace(): void
    {
        $html = Markdown::render("```php title=\"My File.php\"\necho 'Hi';\n```");

        $this->assertStringContainsString('>My File.php</figcaption>', $html);
    }

    public function testTitleModifierDoesNotAffectTheLanguageClass(): void
    {
        $html = Markdown::render("```php title=\"app/Model.php\"\necho 'Hi';\n```");

        $this->assertStringContainsString('<pre><code class="language-php">', $html);
    }

    public function testTitleModifierCanBeSetOnAFenceThatDeclaresNoLanguage(): void
    {
        $html = Markdown::render("``` title=\"app/Model.php\"\necho 'Hi';\n```");

        $this->assertStringContainsString('>app/Model.php</figcaption>', $html);
        $this->assertStringContainsString("<pre><code class=\"language-plaintext\">echo 'Hi';", $html);
    }

    public function testFenceThatDeclaresNoLanguageKeepsItsOtherModifiersOutOfTheLanguageSlot(): void
    {
        $this->assertSame('plaintext theme:github-dark', $this->fenceInfoSeenByHighlighter('title="app/Model.php" theme:github-dark'));
        $this->assertSame('plaintext option="value"', $this->fenceInfoSeenByHighlighter('title="app/Model.php" option="value"'));

        // Not even a modifier that happens to name a language, which belongs before the title
        $this->assertSame('plaintext php', $this->fenceInfoSeenByHighlighter('title="app/Model.php" php'));
    }

    public function testFenceWithoutATitleIsLeftWithoutAFallbackLanguage(): void
    {
        $this->assertSame('', $this->fenceInfoSeenByHighlighter(''));
        $this->assertSame('option="value"', $this->fenceInfoSeenByHighlighter('option="value"'));
    }

    public function testFallbackLanguageIsRenderedAsTheLanguage(): void
    {
        $html = Markdown::render("``` title=\"app/Model.php\" theme:github-dark\necho 'Hi';\n```");

        $this->assertStringContainsString('>app/Model.php</figcaption>', $html);
        $this->assertStringContainsString('<pre><code class="language-plaintext">', $html);
        $this->assertStringNotContainsString('theme:github-dark', $html);
    }

    public function testHighlighterIsNotGivenAModifierAsTheLanguage(): void
    {
        $this->renderWithTorchlight("``` title=\"app/Model.php\" theme:github-dark\necho 'Hi';\n```");

        $this->assertSame('plaintext', $this->torchlightRequests[0]['blocks'][0]['language']);
        $this->assertSame('github-dark', $this->torchlightRequests[0]['blocks'][0]['theme']);
    }

    public function testTitleModifierIsTheOnlyModifierTakenOutOfTheInfoString(): void
    {
        $this->assertSame('php theme:github-dark', $this->fenceInfoSeenByHighlighter('php theme:github-dark title="app/Model.php"'));
    }

    public function testTakingTheTitleOutLeavesTheOtherModifiersByteForByte(): void
    {
        $this->assertSame('php label="a  b" option="c   d"',
            $this->fenceInfoSeenByHighlighter('php label="a  b" title="app/Model.php" option="c   d"')
        );
    }

    public function testATitleWrittenInsideAnotherModifiersValueIsNotOurs(): void
    {
        $this->assertSame('php meta=\'a title="not-hyde" b\'',
            $this->fenceInfoSeenByHighlighter('php meta=\'a title="not-hyde" b\' title="file.php"')
        );

        $this->assertSame('php meta="a title=\'not-hyde\' b"',
            $this->fenceInfoSeenByHighlighter('php meta="a title=\'not-hyde\' b" title="file.php"')
        );
    }

    public function testAnInfoStringWithoutATitleIsLeftExactlyAsItWas(): void
    {
        $this->assertSame('php  theme:dark   option="a  b"', $this->fenceInfoSeenByHighlighter('php  theme:dark   option="a  b"'));
    }

    public function testTheLastOfSeveralTitleModifiersWins(): void
    {
        $html = Markdown::render("```php title=\"first.php\" title=\"second.php\"\necho 'Hi';\n```");

        $this->assertStringContainsString('>second.php</figcaption>', $html);
        $this->assertStringNotContainsString('first.php', $html);
        $this->assertSame('php', $this->fenceInfoSeenByHighlighter('php title="first.php" title="second.php"'));
    }

    public function testEmptyTitleDoesNotRenderALabel(): void
    {
        $html = Markdown::render("```php title=\"\"\necho 'Hi';\n```");

        $this->assertStringNotContainsString('hyde-code-block-label', $html);
        $this->assertStringContainsString('<pre><code class="language-php">', $html);
    }

    public function testMalformedTitleModifierThrowsWithoutALanguageToo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid code block title [title=Foo]. Expected syntax like title="My title".');

        Markdown::render("``` title=Foo\necho 'Hi';\n```");
    }

    public function testMalformedTitleModifierThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid code block title [title=Foo]. Expected syntax like title="My title".');

        Markdown::render("```php title=Foo\necho 'Hi';\n```");
    }

    public function testUnknownModifiersAreIgnored(): void
    {
        $html = Markdown::render("```php someFutureModifier\necho 'Hi';\n```");

        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
    }

    public function testLabelIsNotEscapedWhenHtmlIsAllowed(): void
    {
        $html = Markdown::render("```php title='<a href=\"#\">Link</a>'\necho 'Hi';\n```");

        $this->assertStringContainsString('<a href="#">Link</a></figcaption>', $html);
    }

    public function testLabelIsEscapedWhenHtmlIsDisabled(): void
    {
        config(['markdown.allow_html' => false]);

        $html = Markdown::render("```php title='<a href=\"#\">Link</a>'\necho 'Hi';\n```");

        $this->assertStringContainsString(e('<a href="#">Link</a>').'</figcaption>', $html);
    }

    public function testTorchlightStillRendersTheHighlightedMarkup(): void
    {
        $html = $this->renderWithTorchlight("```php\necho 'Hello World!';\n```");

        // Torchlight's markup, not CommonMark's, and it sits inside the code block view
        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
        $this->assertStringContainsString("<pre><code class='torchlight'", $html);
        $this->assertStringContainsString('HIGHLIGHTED(echo \'Hello World!\';)', $html);
        $this->assertStringNotContainsString('<pre><code class="language-php">', $html);
    }

    public function testTorchlightHighlightedBlocksStillGetTheirLabel(): void
    {
        $html = $this->renderWithTorchlight("```php title=\"hello-world.php\"\necho 'Hello World!';\n```");

        $this->assertStringContainsString('>hello-world.php</figcaption>', $html);
    }

    public function testTerminalBlocksAreStillNotSubmittedToTorchlight(): void
    {
        $this->renderWithTorchlight("```terminal\n\$ php hyde build\n```");

        $this->assertSame([], BaseExtension::$torchlightBlocks);
    }

    public function testAThirdPartyHighlighterRendersTheMarkupInsideTheView(): void
    {
        config(['markdown.extensions' => [FakeHighlighterExtension::class]]);

        $html = Markdown::render("```php\necho 'Hi';\n```");

        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
        $this->assertStringContainsString("<pre><code class=\"fake\">echo 'Hi';</code></pre>", $html);
        $this->assertStringNotContainsString('<pre><code class="language-php">', $html);
    }

    public function testAHighlighterRendersInsideTheViewRegardlessOfItsPriority(): void
    {
        config(['markdown.extensions' => [OverridingHighlighterExtension::class]]);

        $html = Markdown::render("```php\necho 'Hi';\n```");

        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
        $this->assertStringContainsString('<pre><code class="overriding">', $html);
    }

    public function testAHighlighterKeepingStateBetweenItsListenerAndRendererStillWorks(): void
    {
        config(['markdown.extensions' => [StatefulHighlighterExtension::class]]);

        $html = Markdown::render("```php\necho 'Hi';\n```");

        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
        $this->assertStringContainsString("<pre><code>HIGHLIGHTED(echo 'Hi';)</code></pre>", $html);
    }

    public function testAHighlighterThatDeclinesABlockIsOnlyAskedOnce(): void
    {
        config(['markdown.extensions' => [CountingHighlighterExtension::class]]);

        CountingHighlighterRenderer::$calls = 0;

        Markdown::render("```php\necho 'Hi';\n```");

        $this->assertSame(1, CountingHighlighterRenderer::$calls);
    }

    public function testAHighlighterListenerSeesThePreparedCodeHoweverEarlyItRegisters(): void
    {
        config(['markdown.extensions' => [ListeningHighlighterExtension::class]]);

        ListeningHighlighterExtension::$priority = PHP_INT_MAX;
        ListeningHighlighterExtension::$collected = [];

        $html = Markdown::render("```php title=\"app/Model.php\"\necho 'Hi';\n```");

        $this->assertSame([['php', "echo 'Hi';"]], ListeningHighlighterExtension::$collected);
        $this->assertStringContainsString('>app/Model.php</figcaption>', $html);
    }

    public function testTerminalFencesKeepTheirOwnTitleModifier(): void
    {
        config(['markdown.extensions' => [ListeningHighlighterExtension::class]]);

        ListeningHighlighterExtension::$priority = PHP_INT_MAX;

        $html = Markdown::render("```terminal title=\"Build output\"\n\$ php hyde build\n```");

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<span>Build output</span>', $html);
    }

    public function testWrappingACodeBlockTwiceLeavesOneWrapper(): void
    {
        $document = new Document();
        $document->appendChild($fence = new FencedCode(3, '`', 0));

        (new WrapCodeBlocks())($event = new DocumentPreRenderEvent($document, 'html'));
        (new WrapCodeBlocks())($event);

        $this->assertInstanceOf(CodeBlock::class, $fence->parent());
        $this->assertSame($document, $fence->parent()->parent());
    }

    public function testAHighlighterReplacingTheNodeItselfStillOwnsTheBlock(): void
    {
        config(['markdown.extensions' => [NodeReplacingHighlighterExtension::class]]);

        $html = Markdown::render("```php\necho 'Hi';\n```");

        $this->assertStringContainsString('<pre class="replaced">', $html);
        $this->assertStringNotContainsString('hyde-code-block', $html);
    }

    public function testAPublishedCopyOfTheOldFilepathLabelViewIsNotUsed(): void
    {
        $this->directory('resources/views/vendor/hyde/components');
        $this->file('resources/views/vendor/hyde/components/filepath-label.blade.php', '<small>Stale {{ $path ?? \'\' }}</small>');

        $html = Markdown::render("```php title=\"hello-world.php\"\necho 'Hi';\n```");

        $this->assertStringNotContainsString('Stale', $html);
        $this->assertStringContainsString('class="hyde-code-block-label ', $html);
    }

    public function testViewModelRendersTheCodeBlockView(): void
    {
        $model = new CodeBlockViewModel('<pre><code>Hello World!</code></pre>', 'php', 'foo.php');

        $this->assertStringContainsString('<figure class="hyde-code-block ', $model->render());
        $this->assertStringContainsString('>foo.php</figcaption>', $model->render());
        $this->assertStringContainsString('<pre><code>Hello World!</code></pre>', $model->render());
    }

    /** Render Markdown with Torchlight enabled, against a faked API that echoes each block back highlighted. */
    protected function renderWithTorchlight(string $markdown): string
    {
        config(['torchlight.token' => 'fake-token']);

        BaseExtension::$torchlightBlocks = [];
        $this->torchlightRequests = [];

        Http::fake(function (Request $request): PromiseInterface {
            $this->torchlightRequests[] = $request->data();

            return Http::response(['blocks' => array_map(fn (array $block): array => [
                'id' => $block['id'],
                'classes' => 'torchlight',
                'styles' => 'background-color: #22272e;',
                'highlighted' => sprintf('<!-- Syntax highlighted by torchlight.dev --><div class="line">HIGHLIGHTED(%s)</div>', $block['code']),
            ], $request->data()['blocks'])]);
        });

        return (new MarkdownService($markdown))->addFeature('torchlight')->parse();
    }

    /** Render a fence with the given info string, returning it as the highlighter downstream is given it. */
    protected function fenceInfoSeenByHighlighter(string $info): string
    {
        config(['markdown.extensions' => [ListeningHighlighterExtension::class]]);

        ListeningHighlighterExtension::$priority = 0;
        ListeningHighlighterExtension::$collected = [];

        Markdown::render("```$info\necho 1;\n```");

        return ListeningHighlighterExtension::$collected[0][0];
    }
}

/** Stands in for any third-party highlighter registering a fenced code renderer. */
class FakeHighlighterExtension implements ExtensionInterface
{
    protected const PRIORITY = 10;
    protected const CLASS_NAME = 'fake';

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addRenderer(FencedCode::class, new FakeHighlighterRenderer(static::CLASS_NAME), static::PRIORITY);
    }
}

/** The same, but registering above the priority Hyde used to depend on winning at. */
class OverridingHighlighterExtension extends FakeHighlighterExtension
{
    protected const PRIORITY = 100;
    protected const CLASS_NAME = 'overriding';
}

class FakeHighlighterRenderer implements NodeRendererInterface
{
    public function __construct(protected string $className)
    {
        //
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        FencedCode::assertInstanceOf($node);

        return sprintf('<pre><code class="%s">%s</code></pre>', $this->className, trim($node->getLiteral()));
    }
}

/** A highlighter built the usual way, with a listener collecting the blocks its renderer looks up. */
class StatefulHighlighterExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $blocks = new ArrayObject();

        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event) use ($blocks): void {
            foreach ($event->getDocument()->iterator() as $node) {
                if ($node instanceof FencedCode) {
                    $blocks[md5($node->getLiteral())] = sprintf('HIGHLIGHTED(%s)', trim($node->getLiteral()));
                }
            }
        });

        $environment->addRenderer(FencedCode::class, new StatefulHighlighterRenderer($blocks), 10);
    }
}

class StatefulHighlighterRenderer implements NodeRendererInterface
{
    public function __construct(protected ArrayObject $blocks)
    {
        //
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        FencedCode::assertInstanceOf($node);

        return sprintf('<pre><code>%s</code></pre>', $this->blocks[md5($node->getLiteral())] ?? 'STATE MISS');
    }
}

/** Declines every block, so the renderer below it answers instead. */
class CountingHighlighterExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addRenderer(FencedCode::class, new CountingHighlighterRenderer(), 10);
    }
}

class CountingHighlighterRenderer implements NodeRendererInterface
{
    public static int $calls = 0;

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): ?string
    {
        static::$calls++;

        return null;
    }
}

/** Collects the fences it is given, the way a highlighter gathers blocks to send off. */
class ListeningHighlighterExtension implements ExtensionInterface
{
    public static int $priority = 0;

    /** @var array<int, array{0: string, 1: string}> */
    public static array $collected = [];

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event): void {
            foreach ($event->getDocument()->iterator() as $node) {
                if ($node instanceof FencedCode) {
                    static::$collected[] = [$node->getInfo(), trim($node->getLiteral())];
                }
            }
        }, static::$priority);
    }
}

/** Replaces the node rather than rendering it, which is how a block is taken over completely. */
class NodeReplacingHighlighterExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event): void {
            foreach ($event->getDocument()->iterator() as $node) {
                if ($node instanceof FencedCode) {
                    $replacement = new HtmlBlock(HtmlBlock::TYPE_6_BLOCK_ELEMENT);
                    $replacement->setLiteral(sprintf('<pre class="replaced">%s</pre>', trim($node->getLiteral())));

                    $node->replaceWith($replacement);
                }
            }
        });
    }
}
