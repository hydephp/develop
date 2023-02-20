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
        $this->assertFalse(PharSupport::running());
    }

    public function testMockActive()
    {
        PharSupport::mock('running', true);
        $this->assertTrue(PharSupport::running());
        PharSupport::mock('running', false);
        $this->assertFalse(PharSupport::running());
    }
}
