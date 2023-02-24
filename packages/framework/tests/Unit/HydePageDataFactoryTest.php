<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Factories\HydePageDataFactory
 */
class HydePageDataFactoryTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
    }

    protected function factory(array $data = []): HydePageDataFactory
    {
        return new HydePageDataFactory((new InMemoryPage('foo', $data))->toCoreDataObject());
    }
}
