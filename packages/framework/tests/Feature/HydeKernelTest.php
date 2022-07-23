<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\HydeKernel
 */
class HydeKernelTest extends TestCase
{
    public function test_kernel_singleton_can_be_accessed_by_service_container()
    {
        $this->assertSame(app(HydeKernelContract::class), app(HydeKernelContract::class));
    }

    public function test_kernel_singleton_can_be_accessed_by_kernel_static_method()
    {
        $this->assertSame(app(HydeKernelContract::class), HydeKernel::getInstance());
    }

    public function test_kernel_singleton_can_be_accessed_by_hyde_facade_method()
    {
        $this->assertSame(app(HydeKernelContract::class), Hyde::getInstance());
    }

    public function test_kernel_singleton_can_be_accessed_by_helper_function()
    {
        $this->assertSame(app(HydeKernelContract::class), hyde());
    }
}
