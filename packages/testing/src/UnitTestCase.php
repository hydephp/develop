<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Foundation\HydeKernel;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected static bool $hasSetUpKernel = false;

    protected function needsKernel(): void
    {
        if (! self::$hasSetUpKernel) {
            $this->setupKernel();
        }
    }

    protected function setupKernel(): void
    {
        HydeKernel::setInstance(new HydeKernel());
        self::$hasSetUpKernel = true;
    }
}
