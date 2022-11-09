<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Concerns\BaseFoundationCollection
 */
class BaseFoundationCollectionTest extends TestCase
{
    //
}

class BaseFoundationCollectionTestClass extends BaseFoundationCollection
{
    protected bool $discovered = false;

    protected function runDiscovery(): self
    {
        $this->discovered = true;

        return $this;
    }

    public function isDiscovered(): bool
    {
        return $this->discovered;
    }

    public function getKernel(): HydeKernel
    {
        return $this->kernel;
    }
}
