<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Foundation\HydeKernel;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected static bool $hasSetUpKernel = false;

    protected static function needsKernel(): void
    {
        if (! self::$hasSetUpKernel) {
            self::setupKernel();
        }
    }

    protected static function setupKernel(): void
    {
        HydeKernel::setInstance(new HydeKernel());
        self::$hasSetUpKernel = true;
    }
}
