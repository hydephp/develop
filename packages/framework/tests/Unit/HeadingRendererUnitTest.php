<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Events\Dispatcher;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Hyde\Markdown\Processing\HeadingRenderer;
use Hyde\Pages\DocumentationPage;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;

/**
 * @covers \Hyde\Markdown\Processing\HeadingRenderer
 *
 * @see \Hyde\Framework\Testing\Feature\MarkdownHeadingRendererTest
 */
class HeadingRendererUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        $this->createRealBladeCompilerEnvironment();
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

    protected function createRealBladeCompilerEnvironment(): void
    {
        $resolver = new EngineResolver();
        $filesystem = new Filesystem();

        $resolver->register('blade', function () use ($filesystem) {
            return new CompilerEngine(
                new BladeCompiler($filesystem, sys_get_temp_dir())
            );
        });

        $finder = new FileViewFinder($filesystem, [realpath(__DIR__ . '/../../resources/views')]);
        $finder->addNamespace('hyde', realpath(__DIR__ . '/../../resources/views'));

        $view = new Factory($resolver, $finder, new Dispatcher());

        app()->instance('view', $view);
        app()->instance(FactoryContract::class, $view);
    }

    protected function mockChildNodeRenderer(string $contents): ChildNodeRendererInterface
    {
        $childRenderer = Mockery::mock(ChildNodeRendererInterface::class);
        $childRenderer->shouldReceive('renderNodes')
            ->andReturn($contents);

        return $childRenderer;
    }
}
