<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Processing\ColoredBlockquotes;
use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

/**
 * @covers \Hyde\Markdown\Processing\ColoredBlockquotes
 */
class ColoredBlockquoteShortcodesTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        $this->mockRender();
        $this->createRealBladeCompilerEnvironment();
    }

    public function testSignature()
    {
        $this->assertSame('>', ColoredBlockquotes::signature());
    }

    public function testSignatures()
    {
        $this->assertSame(
            ['>danger', '>info', '>success', '>warning'],
            ColoredBlockquotes::getSignatures()
        );
    }

    public function testResolveMethod()
    {
        $this->assertSame(<<<'HTML'
            <blockquote class="info">
                <p>foo</p>
            </blockquote>
            HTML, ColoredBlockquotes::resolve('>info foo')
        );
    }

    public function testCanUseMarkdownWithinBlockquote()
    {
        $this->assertSame(
            <<<'HTML'
            <blockquote class="info">
                <p>foo <strong>bar</strong></p>
            </blockquote>
            HTML, ColoredBlockquotes::resolve('>info foo **bar**')
        );
    }

    public function testWithUnrelatedClass()
    {
        $this->assertSame(
            '>foo foo',
            ColoredBlockquotes::resolve('>foo foo')
        );
    }

    // Todo: Extract trait for this and MarkdownHeadingRendererUnitTest
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
}
