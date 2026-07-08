<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use InvalidArgumentException;

use function config;
use function file_put_contents;
use function substr_count;
use function unlink;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\BladeBlockProcessor::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\BladeBlocks\BladeBlock::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\BladeBlocks\BladeRenderBlock::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\BladeBlocks\BladeComponentBlock::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Processing\BladeBlocks\BladeBlockExtractor::class)]
class BladeBlocksTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        file_put_contents(
            'resources/views/components/blade-block-fixture.blade.php',
            "data=[{{ \$attributes->get('foo') }}] slot=[{{ \$slot }}]"
        );

        file_put_contents(
            'resources/views/components/blade-block-props-fixture.blade.php',
            "value=[{{ \$foo ?? 'UNDEFINED' }}]"
        );
    }

    public static function tearDownAfterClass(): void
    {
        unlink('resources/views/components/blade-block-fixture.blade.php');
        unlink('resources/views/components/blade-block-props-fixture.blade.php');
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['markdown.enable_blade_blocks' => true]);
    }

    private function render(string $markdown): string
    {
        return Markdown::render($markdown);
    }

    // Feature toggle

    public function testFeatureIsDisabledByDefault()
    {
        config(['markdown.enable_blade_blocks' => false]);

        $html = $this->render("```blade render\n{{ \"Hello World!\" }}\n```");

        $this->assertStringNotContainsString('<div class="blade-block', $html);
        $this->assertStringContainsString('{{', $html);
    }

    public function testPlainMarkdownIsUnaffectedWhenFeatureEnabled()
    {
        $html = $this->render("# Title\n\nBody");

        $this->assertStringContainsString('<h1>Title</h1>', $html);
        $this->assertStringContainsString('<p>Body</p>', $html);
        $this->assertStringNotContainsString('blade-block', $html);
    }

    // Test `blade render`

    public function testBladeRenderBlockIsExecuted()
    {
        $html = $this->render("```blade render\n{{ \"Hello World!\" }}\n```");

        $this->assertStringContainsString('<div class="blade-block not-prose">Hello World!</div>', $html);
    }

    public function testBladeRenderBlockExecutesArbitraryBladeAndPhp()
    {
        $html = $this->render("```blade render\n@php(\$world = 'world')\n\n{{ \"Hello \$world\" }}\n```");

        $this->assertStringContainsString('<div class="blade-block not-prose">Hello world</div>', $html);
    }

    public function testPageVariableIsAvailableInBladeRenderBlock()
    {
        $page = MarkdownPage::make('blade-block-page', [], "```blade render\n{{ \$page->identifier ?? 'NO-PAGE' }}\n```");

        Hyde::shareViewData($page);

        $html = $page->compile();

        $this->assertStringContainsString('<div class="blade-block not-prose">blade-block-page</div>', $html);
    }

    // Bare `blade` and ordinary code blocks

    public function testBareBladeBlockIsNotExecuted()
    {
        $html = $this->render("```blade\n{{ \"This is not executed\" }}\n```");

        $this->assertStringNotContainsString('blade-block', $html);
        $this->assertStringContainsString('{{', $html);
    }

    public function testOrdinaryCodeBlocksAreUnaffected()
    {
        $html = $this->render("```php\n<h1>Hello</h1>\n```");

        $this->assertStringNotContainsString('blade-block', $html);
        $this->assertStringContainsString('&lt;h1&gt;Hello&lt;/h1&gt;', $html);
    }

    // Test `blade component(name)`

    public function testComponentBlockWithBareYamlData()
    {
        $html = $this->render("```blade component(blade-block-fixture)\nfoo: bar\n```");

        $this->assertStringContainsString('data=[bar]', $html);
        $this->assertStringContainsString('slot=[]', $html);
    }

    public function testComponentBlockWithFrontMatterAndMarkdownSlot()
    {
        $html = $this->render("```blade component(blade-block-fixture)\n---\nfoo: bar\n---\n\n# Heading\n\nSome **bold** text\n```");

        $this->assertStringContainsString('data=[bar]', $html);
        $this->assertStringContainsString('<h1>Heading</h1>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function testComponentWithNonMappingBodyYieldsNoData()
    {
        $html = $this->render("```blade component(blade-block-fixture)\njust some text\n```");

        $this->assertStringContainsString('data=[]', $html);
        $this->assertStringContainsString('slot=[]', $html);
    }

    public function testComponentDataIsAvailableAsViewVariables()
    {
        $html = $this->render("```blade component(blade-block-props-fixture)\nfoo: bar\n```");

        $this->assertStringContainsString('value=[bar]', $html);
    }

    // Error handling

    public function testUnknownDirectiveThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('```blade render```');

        $this->render("```blade foo\ncontent\n```");
    }

    public function testComponentWithoutParenthesesThrows()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->render("```blade component\ncontent\n```");
    }

    public function testComponentWithEmptyParenthesesThrows()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->render("```blade component()\ncontent\n```");
    }

    public function testComponentNameWithWhitespaceThrows()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->render("```blade component(foo bar)\nfoo: bar\n```");
    }

    // Fence parsing behavior (through the real pipeline)

    public function testFourBacktickFenceEscapesBladeBlock()
    {
        $html = $this->render("````markdown\n```blade render\n{{ \"Not executed\" }}\n```\n````");

        $this->assertStringNotContainsString('blade-block', $html);
        $this->assertStringContainsString('{{', $html);
    }

    public function testTildeFencedBladeBlockIsExecuted()
    {
        $html = $this->render("~~~blade render\n{{ \"Tilde\" }}\n~~~");

        $this->assertStringContainsString('<div class="blade-block not-prose">Tilde</div>', $html);
    }

    public function testBacktickInInfoStringIsNotTreatedAsFence()
    {
        $html = $this->render("```foo`bar\ncontent\n```");

        $this->assertStringNotContainsString('blade-block', $html);
    }

    public function testIndentedBladeRenderBlockIsExecuted()
    {
        $html = $this->render("  ```blade render\n  {{ \"Indented\" }}\n  ```");

        $this->assertStringContainsString('<div class="blade-block not-prose">Indented</div>', $html);
    }

    public function testUnterminatedBladeBlockIsStillExecuted()
    {
        $html = $this->render("```blade render\n{{ \"EOF\" }}");

        $this->assertStringContainsString('<div class="blade-block not-prose">EOF</div>', $html);
    }

    public function testUnterminatedOrdinaryFenceIsPreserved()
    {
        $html = $this->render("```php\n<h1>Hello</h1>");

        $this->assertStringNotContainsString('blade-block', $html);
        $this->assertStringContainsString('&lt;h1&gt;Hello&lt;/h1&gt;', $html);
    }

    // Independence, reentrancy, integration

    public function testEqualBladeBlocksAreCompiledIndependently()
    {
        $html = $this->render("```blade render\n{{ \"X\" }}\n```\n\n```blade render\n{{ \"X\" }}\n```");

        $this->assertSame(2, substr_count($html, '<div class="blade-block not-prose">X</div>'));
    }

    public function testBladeBlocksNestedInComponentSlotAreProcessed()
    {
        $html = $this->render("````blade component(blade-block-fixture)\n---\nfoo: bar\n---\n\n```blade render\n{{ \"Nested\" }}\n```\n````");

        $this->assertStringContainsString('<div class="blade-block not-prose">Nested</div>', $html);
    }

    public function testComponentSlotUsesPageClassWhenCompiledWithinPage()
    {
        $page = MarkdownPage::make('blade-block-slot-page', [], "```blade component(blade-block-fixture)\n---\nfoo: bar\n---\n\n# Heading\n```");

        Hyde::shareViewData($page);

        $html = $page->compile();

        $this->assertStringContainsString('<h1>Heading</h1>', $html);
    }

    public function testFeatureWorksAcrossPageTypes()
    {
        $cases = [
            [MarkdownPage::class, '_site/blade-block-type-test.html'],
            [MarkdownPost::class, '_site/posts/blade-block-type-test.html'],
            [DocumentationPage::class, '_site/docs/blade-block-type-test.html'],
        ];

        foreach ($cases as [$pageClass, $outputPath]) {
            $page = $pageClass::make('blade-block-type-test', [], "```blade render\n{{ \"PageType\" }}\n```");

            Hyde::shareViewData($page);

            $html = $page->compile();

            $this->assertStringContainsString('<div class="blade-block not-prose">PageType</div>', $html);
        }
    }

    public function testBladeDownAndBladeBlocksCanBeEnabledTogether()
    {
        config(['markdown.enable_blade' => true]);

        $html = $this->render("[Blade]: {{ \"Hello BladeDown!\" }}\n\n```blade render\n{{ \"Hello BladeBlock!\" }}\n```");

        $this->assertStringContainsString('Hello BladeDown!', $html);
        $this->assertStringContainsString('<div class="blade-block not-prose">Hello BladeBlock!</div>', $html);
    }

    public function testStaticStateIsNotLeakedAfterFailedRender()
    {
        try {
            $this->render("```blade render\n{{ \"Valid\" }}\n```\n\n```blade foo\ninvalid\n```");
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException) {
            //
        }

        $html = $this->render('# Clean');

        $this->assertStringNotContainsString('blade-block', $html);
    }
}
