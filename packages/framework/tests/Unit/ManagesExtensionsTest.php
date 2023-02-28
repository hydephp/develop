<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Concerns\ManagesExtensions
 *
 * @see \Hyde\Framework\Testing\Feature\HydeKernelTest
 * @see \Hyde\Framework\Testing\Feature\HydeExtensionFeatureTest
 */
class ManagesExtensionsTest extends UnitTestCase
{
    protected HydeKernel $kernel;

    public function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();

        $this->kernel = HydeKernel::getInstance();
    }

    protected function markTestSuccessful(): void
    {
        $this->assertTrue(true);
    }
}
