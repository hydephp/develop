<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Mockery;
use Hyde\Foundation\HydeKernel;
use Hyde\Support\Facades\Render;
use Illuminate\Config\Repository;
use Hyde\Support\Models\RenderData;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected static bool $needsKernel = false;
    protected static bool $needsConfig = false;
    protected static bool $needsRender = false;

    public static function setUpBeforeClass(): void
    {
        if (static::$needsKernel) {
            self::resetKernel();
        }

        if (static::$needsConfig) {
            self::mockConfig();
        }

        if (static::$needsRender) {
            self::mockRender();
        }
    }

    protected static function setupKernel(): void
    {
        HydeKernel::setInstance(new HydeKernel());
    }

    protected static function resetKernel(): void
    {
        HydeKernel::setInstance(new HydeKernel());
    }

    protected static function mockRender(): Render
    {
        return tap(new Render(), function () {
            Render::swap(new RenderData());
        });
    }

    protected static function mockCurrentRouteKey(?string $routeKey = null): void
    {
        self::mockRender()->shouldReceive('getRouteKey')->andReturn($routeKey);
    }

    protected static function mockConfig(array $items = []): void
    {
        Config::swap(tap(new Repository($items), function ($config) {
            app()->instance('config', $config);
        }));
    }

    /**
     * @return \Illuminate\Filesystem\Filesystem&\Mockery\MockInterface
     */
    protected function mockFilesystem(array $methods = []): Filesystem
    {
        return tap(Mockery::mock(Filesystem::class, $methods)->makePartial(), function ($filesystem) {
            app()->instance(Filesystem::class, $filesystem);
        });
    }

    protected function verifyMockeryExpectations(): void
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());

        Mockery::close();
    }
}
