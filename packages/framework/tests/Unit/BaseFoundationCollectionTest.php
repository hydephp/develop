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

    public function test_exceptions_are_caught_and_rethrown_as_runtime_exceptions()
    {
        $this->expectException(RuntimeException::class);
        ThrowingBaseFoundationCollectionTestClass::init(HydeKernel::getInstance())->boot();
    }

    public function test_exceptions_are_caught_and_rethrown_with_helpful_information()
    {
        $this->expectException(RuntimeException::class);
        ThrowingBaseFoundationCollectionTestClass::init(HydeKernel::getInstance())->boot();
    }

    public function test_can_get_previous_exception()
    {
        try {
            ThrowingBaseFoundationCollectionTestClass::init(HydeKernel::getInstance())->boot();
        } catch (RuntimeException $exception) {
            $this->assertInstanceOf(Exception::class, $exception->getPrevious());
            $this->assertSame('This is a test exception', $exception->getPrevious()->getMessage());
        }
    }
}

class BaseFoundationCollectionTestClass extends BaseFoundationCollection
{
    protected bool $discovered = false;

    protected function runDiscovery(): void
    {
        $this->discovered = true;
    }

    protected function runExtensionCallbacks(): void
    {
        //
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
    protected function runDiscovery(): void
    {
        throw new Exception('This is a test exception');
    }

    protected function runExtensionCallbacks(): void
    {
        //
    }
}
