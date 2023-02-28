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
    public function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();
    }
}
