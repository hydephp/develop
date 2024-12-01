<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Processing\HeadingRenderer;
use Hyde\Pages\DocumentationPage;
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
use PHPUnit\Framework\Attributes\TestWith;

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

        $this->assertSame('<h2>Test Heading<a id="test-heading" href="#test-heading" class="heading-permalink" title="Permalink"></a></h2>', $rendered);
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

    #[TestWith([1, false])]
    #[TestWith([2, true])]
    #[TestWith([3, true])]
    #[TestWith([4, true])]
    #[TestWith([5, true])]
    #[TestWith([6, true])]
    public function testCanAddPermalinkBasedOnConfiguration(int $level, bool $shouldHavePermalink): void
    {
        $childRenderer = $this->mockChildNodeRenderer('Test Content');
        $renderer = new HeadingRenderer(DocumentationPage::class);
        $rendered = $renderer->render(new Heading($level), $childRenderer);

        if ($shouldHavePermalink) {
            $this->assertStringContainsString('heading-permalink', $rendered);
        } else {
            $this->assertStringNotContainsString('heading-permalink', $rendered);
        }
    }

    public function testForwardsHeadingAttributes()
    {
        $childRenderer = $this->mockChildNodeRenderer('Test Content');

        $renderer = new HeadingRenderer(DocumentationPage::class);
        $heading = new Heading(2);
        $heading->data->set('attributes', ['class' => 'custom-class']);
        $rendered = $renderer->render($heading, $childRenderer);

        $this->assertStringContainsString('class="custom-class"', $rendered);
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

        $this->assertStringContainsString('class="custom-class"', $rendered);
        $this->assertStringContainsString('foo="bar"', $rendered);
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
}
