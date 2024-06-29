<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Factories\Concerns\PageDataFactory;

/**
 * @covers \Hyde\Framework\Factories\Concerns\PageDataFactory
 */
class PageDataFactoryTest extends UnitTestCase
{
    public function testItHasASchemaConstant()
    {
        $this->assertIsArray(PageDataFactory::SCHEMA);
    }

    public function testItImplementsArrayableInterface()
    {
        $this->assertInstanceOf(
            \Illuminate\Contracts\Support\Arrayable::class,
            $this->getMockForAbstractClass(PageDataFactory::class)
        );
    }

    public function testItHasToArrayMethod()
    {
        $mock = $this->getMockForAbstractClass(PageDataFactory::class);
        $mock->method('toArray')->willReturn([]);

        $this->assertIsArray($mock->toArray());
    }
}
