<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Processing\HeadingRenderer;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use Mockery;

/**
 * @covers \Hyde\Markdown\Processing\HeadingRenderer
 *
 * @see \Hyde\Framework\Testing\Feature\MarkdownHeadingRendererTest
 */
class HeadingRendererUnitTest extends UnitTestCase
{
    protected static bool $needsConfig = true;
    protected static ?array $cachedConfig = null;

    protected function setUp(): void
    {
        $this->createRealBladeCompilerEnvironment();

        self::mockConfig(['markdown' => self::$cachedConfig ??= require __DIR__.'/../../config/markdown.php']);
    }

    public function testCanConstruct()
    {
        $renderer = new HeadingRenderer(DocumentationPage::class);
        $this->assertInstanceOf(HeadingRenderer::class, $renderer);
    }

    public function testCanConstructWithPageClass()
    {
        $renderer = new HeadingRenderer(DocumentationPage::class);
        $this->assertInstanceOf(HeadingRenderer::class, $renderer);
    }

    public function testCanRenderHeading()
    {
        $childRenderer = $this->mockChildNodeRenderer();
        $renderer = new HeadingRenderer(DocumentationPage::class);
        $rendered = $renderer->render(new Heading(2), $childRenderer);

        $this->assertStringContainsString('Test Heading', $rendered);
        $this->assertStringContainsString('<h2', $rendered);
        $this->assertStringContainsString('</h2>', $rendered);

        $this->assertSame('<h2 id="test-heading" class="group w-fit">Test Heading<a href="#test-heading" class="heading-permalink opacity-0 ml-1 transition-opacity duration-300 ease-linear px-1 group-hover:opacity-100 focus:opacity-100 group-hover:grayscale-0 focus:grayscale-0" title="Permalink">#</a></h2>', $rendered);
    }

    public function testAddsPermalinkToValidHeadings()
    {
        $childRenderer = $this->mockChildNodeRenderer();
        $renderer = new HeadingRenderer(DocumentationPage::class);
        $heading = new Heading(2);
        $rendered = $renderer->render($heading, $childRenderer);

        $this->assertStringContainsString('heading-permalink', $rendered);
        $this->assertStringContainsString('#test-heading', $rendered);
    }

    public function testThrowsExceptionForInvalidNode()
    {
        $invalidNode = Mockery::mock(Node::class);

        $this->expectException(InvalidArgumentException::class);
        $renderer = new HeadingRenderer(DocumentationPage::class);

        $childRenderer = Mockery::mock(ChildNodeRendererInterface::class);
        $renderer->render($invalidNode, $childRenderer);
    }

    public function testDoesNotAddPermalinksIfDisabledInConfiguration()
    {
        self::mockConfig(['markdown.permalinks.enabled' => false]);

        $childRenderer = $this->mockChildNodeRenderer();
        $renderer = new HeadingRenderer(DocumentationPage::class);
        $rendered = $renderer->render(new Heading(2), $childRenderer);

        $this->assertStringNotContainsString('heading-permalink', $rendered);
    }

    public function testAddsPermalinksBasedOnConfiguredHeadingLevels(): void
    {
        $childRenderer = $this->mockChildNodeRenderer('Test Content');
        $renderer = new HeadingRenderer(DocumentationPage::class);

        self::mockConfig(['markdown.permalinks.min_level' => 2, 'markdown.permalinks.max_level' => 6]);

        $this->validateHeadingPermalinkStates($renderer, $childRenderer);

        self::mockConfig(['markdown.permalinks.min_level' => 3, 'markdown.permalinks.max_level' => 4]);

        $this->validateHeadingPermalinkStates($renderer, $childRenderer);

        self::mockConfig(['markdown.permalinks.min_level' => 0, 'markdown.permalinks.max_level' => 0]);

        $this->validateHeadingPermalinkStates($renderer, $childRenderer);
    }

    public function testForwardsHeadingAttributes()
    {
        $childRenderer = $this->mockChildNodeRenderer('Test Content');

        $renderer = new HeadingRenderer(DocumentationPage::class);
        $heading = new Heading(2);
        $heading->data->set('attributes', ['class' => 'custom-class']);
        $rendered = $renderer->render($heading, $childRenderer);

        $this->assertStringContainsString('class="custom-class group w-fit', $rendered);
    }

    public function testForwardsArbitraryHeadingAttributes()
    {
        $childRenderer = $this->mockChildNodeRenderer('Test Content');

        $renderer = new HeadingRenderer(DocumentationPage::class);
        $heading = new Heading(2);
        $heading->data->set('attributes', ['foo' => 'bar']);
        $rendered = $renderer->render($heading, $childRenderer);

        $this->assertStringContainsString('foo="bar"', $rendered);
    }

    public function testMergesAllHeadingAttributes()
    {
        $childRenderer = $this->mockChildNodeRenderer('Test Content');

        $renderer = new HeadingRenderer(DocumentationPage::class);
        $heading = new Heading(2);
        $heading->data->set('attributes', ['class' => 'custom-class', 'foo' => 'bar']);
        $rendered = $renderer->render($heading, $childRenderer);

        $this->assertStringContainsString('class="custom-class group w-fit"', $rendered);
        $this->assertStringContainsString('foo="bar"', $rendered);
    }

    public function testCanAddPermalinkReturnsFalseForExistingPermalink(): void
    {
        $renderer = new HeadingRenderer(DocumentationPage::class);
        $content = 'Test Content<a class="heading-permalink"></a>';

        $result = $renderer->canAddPermalink($content, 2);

        $this->assertFalse($result);
    }

    public function testCanAddPermalinkReturnsFalseForNotEnabledPageClass(): void
    {
        $renderer = new HeadingRenderer(MarkdownPage::class);

        $this->assertFalse($renderer->canAddPermalink('Test Content', 2));
    }

    public function testCanAddPermalinkWithCustomPageClasses(): void
    {
        self::mockConfig([
            'markdown.permalinks.pages' => [DocumentationPage::class, MarkdownPage::class],
        ]);

        $renderer = new HeadingRenderer(MarkdownPage::class);

        $this->assertTrue($renderer->canAddPermalink('Test Content', 2));
    }

    public function testPostProcessMethodNormalizesInputToMatchCommonMark()
    {
        // Actual HTML output returned from Blade
        $html = <<<'HTML'
        <h2 >
            Test Heading
                    <a id="test-heading" href="#test-heading" class="heading-permalink opacity-0 ml-1 transition-opacity duration-300 ease-linear px-1 group-hover:opacity-100 focus:opacity-100 group-hover:grayscale-0 focus:grayscale-0" title="Permalink">#</a>
            </h2> 
        HTML;

        // What CommonMark would generate from the same input Markdown
        $expected = '<h2>Test Heading<a id="test-heading" href="#test-heading" class="heading-permalink opacity-0 ml-1 transition-opacity duration-300 ease-linear px-1 group-hover:opacity-100 focus:opacity-100 group-hover:grayscale-0 focus:grayscale-0" title="Permalink">#</a></h2>';

        $this->assertSame($expected, (new HeadingRenderer())->postProcess($html));
    }

    public function testPostProcessRemovesSpacesCausedByNoExtraBladeAttributes()
    {
        $html = "<h1 >Title</h1>\n<h2 >Subtitle</h2>";

        $this->assertSame('<h1>Title</h1><h2>Subtitle</h2>', (new HeadingRenderer())->postProcess($html));
    }

    public function testPostProcessRemovesSpacesCausedByNoExtraBladeAttributesButLeavesExtraAttributesAlone()
    {
        $html = "<h1 class=\"foo-bar baz\">Title</h1>\n<h2 >Subtitle</h2>";

        $this->assertSame('<h1 class="foo-bar baz">Title</h1><h2>Subtitle</h2>', (new HeadingRenderer())->postProcess($html));
    }

    public function testPostProcessTrimsWhitespaceAndIndentationFromLines()
    {
        $html = "  <h1>Title</h1>  \n  <h2>Subtitle</h2>  ";

        $this->assertSame('<h1>Title</h1><h2>Subtitle</h2>', (new HeadingRenderer())->postProcess($html));
    }

    public function testPostProcessHandlesEmptyString()
    {
        $html = '';

        $this->assertSame('', (new HeadingRenderer())->postProcess($html));
    }

    public function testPostProcessHandlesNoHeadingTags()
    {
        $html = '<p>Paragraph</p>';

        $this->assertSame('<p>Paragraph</p>', (new HeadingRenderer())->postProcess($html));
    }

    protected function createRealBladeCompilerEnvironment(): void
    {
        $resolver = new EngineResolver();
        $filesystem = new Filesystem();

        $resolver->register('blade', function () use ($filesystem) {
            return new CompilerEngine(
                new BladeCompiler($filesystem, sys_get_temp_dir())
            );
        });

        $finder = new FileViewFinder($filesystem, [realpath(__DIR__.'/../../resources/views')]);
        $finder->addNamespace('hyde', realpath(__DIR__.'/../../resources/views'));

        $view = new Factory($resolver, $finder, new Dispatcher());

        app()->instance('view', $view);
        app()->instance(FactoryContract::class, $view);
    }

    protected function mockChildNodeRenderer(string $contents = 'Test Heading'): ChildNodeRendererInterface
    {
        $childRenderer = Mockery::mock(ChildNodeRendererInterface::class);
        $childRenderer->shouldReceive('renderNodes')->andReturn($contents);

        return $childRenderer;
    }

    protected function validateHeadingPermalinkStates(HeadingRenderer $renderer, ChildNodeRendererInterface $childRenderer): void
    {
        foreach (range(1, 6) as $level) {
            $rendered = $renderer->render(new Heading($level), $childRenderer);

            $shouldHavePermalink = $level >= config('markdown.permalinks.min_level') && $level <= config('markdown.permalinks.max_level');

            if ($shouldHavePermalink) {
                $this->assertStringContainsString('heading-permalink', $rendered);
            } else {
                $this->assertStringNotContainsString('heading-permalink', $rendered);
            }
        }
    }
}
