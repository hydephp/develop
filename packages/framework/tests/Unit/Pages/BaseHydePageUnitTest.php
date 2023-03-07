<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Testing\CreatesTemporaryFiles;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Facades\View;
use Illuminate\View\Factory;
use Mockery;

/**
 * Providers helpers and a contract for unit testing for the specified page class.
 *
 * These unit tests ensure all inherited methods are callable, and that they return the expected value.
 *
 * @coversNothing
 */
abstract class BaseHydePageUnitTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected function setUp(): void
    {
        self::setupKernel();
        self::mockConfig();

        View::swap($mock = Mockery::mock(Factory::class, [
            'make' => Mockery::mock(Factory::class, [
                'render' => 'foo',
                'with' => Mockery::mock(Factory::class, [
                    'render' => 'foo',
                ]),
            ]),
            'share' => null,
        ]));
        app()->bind(\Illuminate\Contracts\View\Factory::class, fn () =>$mock);
        app()->bind('view', fn () =>$mock);

        Render::swap(new RenderData());
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
        View::swap(null);
        Render::swap(null);
        app()->forgetInstance(\Illuminate\Contracts\View\Factory::class);
        app()->forgetInstance('view');
    }

    abstract public function testPath();

    abstract public function testBaseRouteKey();

    abstract public function testGetBladeView();

    abstract public function testSourcePath();

    abstract public function testFiles();

    abstract public function testNavigationMenuLabel();

    abstract public function testGetOutputPath();

    abstract public function testGet();

    abstract public function testData();

    abstract public function testOutputDirectory();

    abstract public function testParse();

    abstract public function testNavigationMenuGroup();

    abstract public function testNavigationMenuPriority();

    abstract public function testGetRouteKey();

    abstract public function testTitle();

    abstract public function testAll();

    abstract public function testMetadata();

    abstract public function test__construct();

    abstract public function testMake();

    abstract public function testGetRoute();

    abstract public function testShowInNavigation();

    abstract public function testGetSourcePath();

    abstract public function testGetLink();

    abstract public function testGetIdentifier();

    abstract public function testHas();

    abstract public function testToCoreDataObject();

    abstract public function testFileExtension();

    abstract public function testSourceDirectory();

    abstract public function testCompile();

    abstract public function testMatter();

    abstract public function testOutputPath();
}
