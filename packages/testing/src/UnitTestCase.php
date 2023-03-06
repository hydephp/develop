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

    protected bool $needsKernel = false;
    protected bool $needsConfig = false;

    protected static function needsKernel(): void
    {
        if (! self::$hasSetUpKernel) {
            self::setupKernel();
        }
    }

    protected function setUp(): void
    {
        if ($this->needsKernel) {
            self::needsKernel();
        }

        if ($this->needsConfig) {
            self::mockConfig();
        }
    }

    protected static function setupKernel(): void
    {
        HydeKernel::setInstance(new HydeKernel());
        self::$hasSetUpKernel = true;
    }

    protected static function mockConfig(array $items = []): void
    {
        app()->bind('config', fn (): Repository => new Repository($items));

        Config::swap(app('config'));
    }
}
