<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Pages\Concerns\HydePage
 */
class HydePageSerializableUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testToArray()
    {
        //
    }
}

class InstantiableHydePage extends HydePage
{
    public function compile(): string
    {
        return '';
    }
}
