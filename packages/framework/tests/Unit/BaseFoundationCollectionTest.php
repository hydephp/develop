<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Testing\UnitTestCase;
use RuntimeException;
use Exception;

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

    public function test_exceptions_are_caught_and_rethrown_with_helpful_information()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('An error occurred during the discovery process');

        ThrowingBaseFoundationCollectionTestClass::init(HydeKernel::getInstance())->boot();
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

class ThrowingBaseFoundationCollectionTestClass extends BaseFoundationCollection
{
    protected function runDiscovery(): self
    {
        throw new Exception('This is a test exception');
    }

    protected function runExtensionCallbacks(): self
    {
        return $this;
    }
}
