<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Foundation\HydeKernel;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected static bool $hasSetUpKernel = false;

    protected static bool $needsKernel = false;
    protected static bool $needsConfig = false;

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
    }

    protected static function setupKernel(): void
    {
        // If a config is not bound we bind it
        if (! app()->bound('config')) {
            self::mockConfig();
        }

        HydeKernel::setInstance(new HydeKernel());
        self::$hasSetUpKernel = true;
    }

    protected static function mockConfig(array $items = []): void
    {
        app()->bind('config', fn (): Repository => new Repository($items));

        Config::swap(app('config'));
    }
}
