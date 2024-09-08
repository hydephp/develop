<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Foundation\HydeKernel;
use Hyde\Support\Facades\Render;
use Illuminate\Config\Repository;
use Hyde\Support\Models\RenderData;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected static bool $hasSetUpKernel = false;

    protected static bool $needsKernel = false;
    protected static bool $needsConfig = false;
    protected static bool $needsRender = false;

    protected static function needsKernel(): void
    {
        if (! self::$hasSetUpKernel) {
            self::setupKernel();
        }
    }

    public static function setUpBeforeClass(): void
    {
        if (static::$needsKernel) {
            self::needsKernel();
        }

        if (static::$needsConfig) {
            self::mockConfig();
        }

        if (static::$needsRender) {
            Render::swap(new RenderData());
        }
    }

    protected static function setupKernel(): void
    {
        HydeKernel::setInstance(new HydeKernel());
        self::$hasSetUpKernel = true;
    }

    protected static function resetKernel(): void
    {
        HydeKernel::setInstance(new HydeKernel());
    }

    protected static function mockConfig(array $items = []): void
    {
        app()->bind('config', fn (): Repository => new Repository($items));

        Config::swap(app('config'));
    }

    protected static function mockCurrentRouteKey(?string $routeKey = null): void
    {
        Render::swap(new RenderData());
        Render::shouldReceive('getRouteKey')->andReturn($routeKey);
    }
}
