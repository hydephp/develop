<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\PharSupport;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\PharSupport
 */
class PharSupportTest extends TestCase
{
    public function testActive()
    {
        $this->assertFalse(PharSupport::active());
    }

    public function testMockActive()
    {
        PharSupport::mockActive(true);
        $this->assertTrue(PharSupport::active());
        PharSupport::mockActive(false);
        $this->assertFalse(PharSupport::active());
    }
}
