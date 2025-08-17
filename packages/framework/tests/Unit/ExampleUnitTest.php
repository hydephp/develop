<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass('\\Hyde\\Hyde')]
class ExampleUnitTest extends UnitTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
