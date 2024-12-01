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

    protected function createRealBladeCompilerEnvironment(): void
    {
        // Create and configure the engine resolver
        $resolver = new EngineResolver();
        $filesystem = new Filesystem();

        // Register the blade engine
        $resolver->register('blade', function () use ($filesystem) {
            return new CompilerEngine(
                new BladeCompiler($filesystem, sys_get_temp_dir())
            );
        });

        // Create the view factory with the configured resolver
        $view = new Factory(
            $resolver,
            $finder = new FileViewFinder(
                $filesystem,
                [realpath(__DIR__ . '/../../resources/views')]
            ),
            new Dispatcher()
        );

        $finder->addNamespace('hyde', realpath(__DIR__ . '/../../resources/views'));

        app()->instance('view', $view);
        app()->instance(FactoryContract::class, $view);
    }
}
