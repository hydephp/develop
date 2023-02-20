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
    public function tearDown(): void
    {
        PharSupport::clearMocks();

        parent::tearDown();
    }

    public function testActive()
    {
        $this->assertFalse(PharSupport::active());
    }

    public function testMockActive()
    {
        PharSupport::mock('active', true);
        $this->assertTrue(PharSupport::active());
        PharSupport::mock('active', false);
        $this->assertFalse(PharSupport::active());
    }
}
