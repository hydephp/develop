<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass('\\Hyde\\Hyde')]
class ExampleTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
