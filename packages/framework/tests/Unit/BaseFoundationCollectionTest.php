<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\Concerns\BaseFoundationCollection
 */
class BaseFoundationCollectionTest extends UnitTestCase
{
    public function test_init()
    {
        $this->needsKernel();

        $booted = BaseFoundationCollectionTestClass::init(HydeKernel::getInstance())->boot();

        $this->assertInstanceOf(BaseFoundationCollection::class, $booted);
        $this->assertInstanceOf(BaseFoundationCollectionTestClass::class, $booted);

        $this->assertSame(HydeKernel::getInstance(), $booted->getKernel());
        $this->assertTrue($booted->isDiscovered());
    }

    public function test_get_instance()
    {
        $booted = BaseFoundationCollectionTestClass::init(HydeKernel::getInstance())->boot();

        $this->assertSame($booted, $booted->getInstance());
    }
}

class BaseFoundationCollectionTestClass extends BaseFoundationCollection
{
    protected bool $discovered = false;

    protected function runDiscovery(): self
    {
        $this->discovered = true;

        return $this;
    }

    protected function runExtensionCallbacks(): self
    {
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
