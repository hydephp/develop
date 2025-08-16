<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Hyde::class)]
class ExampleUnitTest extends UnitTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
