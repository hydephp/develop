<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Hyde::class)]
class ExampleTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
