<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 */
class NavigationDataFactoryUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    //
}
