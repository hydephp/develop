<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Events\Dispatcher;
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
        // Create a minimal view environment without the full service provider
        $view = new Factory(
            new EngineResolver(),
            $finder = new FileViewFinder(
                new Filesystem(),
                [realpath(__DIR__ . '/../../resources/views')]
            ),
            new Dispatcher()
        );

        $finder->addNamespace('hyde', realpath(__DIR__ . '/../../resources/views'));

        app()->instance('view', $view);
        app()->instance(FactoryContract::class, $view);
    }
}
